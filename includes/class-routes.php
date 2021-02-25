<?php
/**
 * This class extends Wordpress API
 *
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Routes{


   /**
    * Register routes
    *
    * @since 1.0.10 - added `permission_callback` set on true to fix the notice in WP 5.5
    * @since 1.0.0
    * @return void
    */
   public static function register() {

      register_rest_route( 'woosa-adyen', 'payment-status', array(
            'methods' => 'POST',
            'callback' => [__CLASS__, 'payment_notification'],
            'permission_callback' => '__return_true'
         )
      );

      register_rest_route( 'woosa-adyen', 'boleto-payment-status', array(
            'methods' => 'POST',
            'callback' => [__CLASS__, 'boleto_payment_notification'],
            'permission_callback' => '__return_true'
         )
      );
   }



   /**
    * Gets request headers
    *
    * @since 1.0.3 - save notification payload on debugging
    * @since 1.0.0
    * @return array
    */
   public static function getallheaders(){

      $headers = array();
      $payload = $_POST;

      if(is_array($_SERVER)) {
         foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
         }
      }

      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== WEBHOOK NOTIFICATION ====',
            '_HEADERS' => $headers,
            '_PAYLOAD' => $payload
         ], __FILE__, __LINE__);
      }

      return $headers;
   }



   /**
    * Checks if there is a basic authentication
    *
    * @since 1.0.0
    * @param array $headers
    * @return boolean
    */
   public static function is_not_authenticated($headers){

      $authorization = base64_decode(str_replace('Basic ', '', $headers['Authorization']));
      $credentials = explode(':', $authorization);
      $username = get_option(PREFIX.'_api_username');
      $password = get_option(PREFIX.'_api_password');


      if( ! empty($username) || ! empty($password) ){

         if( $username != Utility::rgar($credentials, '0') || $password != Utility::rgar($credentials, '1') ){
            Utility::wc_error_log('Notifications could not be authenticated, please check username/password!', __FILE__, __LINE__);
            return true;
         }
      }

      return false;
   }



   /**
    * Returns an object with the payload data.
    *
    * @since 1.1.0 - remove the order reference prefix
    *              - add `-S` as a new reference separator for subscriptions
    * @since 1.0.3
    * @param array $payload
    * @return object
    */
   public static function get_payload_data($payload){

      $psp_reference    = Utility::rgar($payload, 'pspReference');
      $recurr_reference = Utility::rgar($payload, 'additionalData_recurring_recurringDetailReference');
      $event_code       = Utility::rgar($payload, 'eventCode');
      $success          = Utility::rgar($payload, 'success');
      $value            = Utility::rgar($payload, 'value');
      $amount_value     = number_format($value/100, 2, '.', ' ');
      $payment_method   = Utility::rgar($payload, 'paymentMethod');
      $order_id         = Utility::rgar($payload, 'merchantReference');
      $subscription_ids = [];

      //keep support for old `_#subscription#_`
      $seps = ['_#subscription#_', '-S'];

      foreach($seps as $sep){

         if(strpos($order_id, $sep) !== false){

            $rfs = explode($sep, $order_id);
            $order_id = $rfs[0];

            //remove order id
            unset($rfs[0]);

            $subscription_ids = $rfs;
         }

      }


      return (object) [
         'psp_reference'    => $psp_reference,
         'recurr_reference' => $recurr_reference,
         'event_code'       => $event_code,
         'success'          => $success,
         'value'            => $value,
         'amount_value'     => $amount_value,
         'payment_method'   => $payment_method,
         'order_id'         => Order::remove_reference_prefix($order_id),
         'subscription_ids' => $subscription_ids,
      ];
   }



   /**
    * This runs only when a Boleto notification is received.
    *
    * @since 1.0.3
    * @return string
    */
   public static function boleto_payment_notification(){

      $headers = self::getallheaders();
      $data    = self::get_payload_data($_POST);
      $order   = wc_get_order($data->order_id);


      //stop if something doesn't match
      if(self::is_not_authenticated($headers) || empty($data->psp_reference) || ! $order instanceof \WC_Order){
         return '[accepted]';
      }


      switch($data->event_code){

         case 'AUTHORISATION':

            if($data->success === 'true'){

               self::payment_action_completed($order, $data->psp_reference, [], $data->payment_method);

            }

            break;

         case 'OFFER_CLOSED':

            if($data->success === 'true'){

               $order->update_status('cancelled');
               $order->add_order_note(sprintf(
                  __('The payment amount of %s has been cancelled.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));

            }

            break;


         default:

            if($data->success === 'true'){

               $order->update_status('on-hold');
               $order->add_order_note(__('Waiting for customer to pay.', 'woosa-adyen'));

            }
      }

      //something went wrong with the payment
      if($data->success !== 'true'){
         self::payment_action_failed($order, $data->psp_reference, __('The payment could not be processed.', 'woosa-adyen'));
      }


      return '[accepted]';
   }



   /**
    * Processes the payment notifications
    *
    * @since 1.0.10 - update cached payment methods
    * @since 1.0.0
    * @return void
    */
   public static function payment_notification() {

      $headers = self::getallheaders();
      $data    = self::get_payload_data($_POST);
      $order   = wc_get_order($data->order_id);


      //stop if something doesn't match
      if(self::is_not_authenticated($headers) || empty($data->psp_reference) || ! $order instanceof \WC_Order){
         return '[accepted]';
      }

      $shopper_reference = get_post_meta($order->get_id(), '_'.PREFIX.'_shopper_reference', true);
      $capture_payment = get_option(PREFIX.'_capture_payment', 'immediate');


      switch($data->event_code){

         case 'AUTHORISATION':

            if($data->success === 'true'){

               update_post_meta($order->get_id(), '_'.PREFIX.'_payment_pspReference', $data->psp_reference);

               //save required data for subscriptions
               foreach($data->subscription_ids as $sub_id){

                  //only if it's not empty because we do not want to overwrite stored card id (in case we saved it before)
                  if( ! empty($data->recurr_reference) ){
                     update_post_meta($sub_id, '_'.PREFIX.'_recurringDetailReference', $data->recurr_reference);
                  }
                  update_post_meta($sub_id, '_'.PREFIX.'_payment_pspReference', $data->psp_reference);
                  update_post_meta($sub_id, '_'.PREFIX.'_shopper_reference', $shopper_reference);
               }

               if(
                  ('immediate' === $capture_payment && ! API::is_manual_payment($data->payment_method) ) ||
                  API::is_immediate_payment($data->payment_method)
               ){

                  self::payment_action_completed($order, $data->psp_reference, $data->subscription_ids, $data->payment_method);
                  update_post_meta($order->get_id(), '_'.PREFIX.'_payment_captured', 'yes');

               }else{

                  $order->update_status('on-hold');
                  $order->add_order_note(__('Waiting for payment capture.', 'woosa-adyen'));
               }

            }else{

               self::payment_action_failed($order, $data->psp_reference, __('The payment could not be processed.', 'woosa-adyen'));
            }

            break;


         case 'CANCELLATION': case 'OFFER_CLOSED':

            if($data->success === 'true'){

               $order->update_status('cancelled');
               $order->add_order_note(sprintf(
                  __('The payment amount of %s has been cancelled.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));

            }else{

               self::payment_action_failed($order, $data->psp_reference, sprintf(
                  __('The payment amount of %s could not be cancelled.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));
            }

            break;


         case 'CAPTURE':

            if($data->success === 'true'){

               self::payment_action_completed($order, $data->psp_reference, $data->subscription_ids, $data->payment_method);

               update_post_meta($order->get_id(), '_'.PREFIX.'_payment_captured', 'yes');

            }else{

               self::payment_action_failed($order, $data->psp_reference, __('The payment capture has failed.', 'woosa-adyen'));
            }

            break;


         case 'CAPTURE_FAILED':

            self::payment_action_failed($order, $data->psp_reference, __('The payment capture has failed.', 'woosa-adyen'));

            break;


         case 'REFUND':

            if($data->success === 'true'){

               //change to refunded if the total order has been refunded
               if($data->amount_value === $order->get_total()){
                  $order->update_status('refunded');
               }

               $order->add_order_note(sprintf(
                  __('The payment amount of %s has been refunded.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));

            }else{

               self::payment_action_failed($order, $data->psp_reference, sprintf(
                  __('Refunding the payment amount of %s has failed.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));
            }

            break;


         case 'REFUND_FAILED':

            self::payment_action_failed($order, $data->psp_reference, sprintf(
               __('Refunding the payment amount of %s has failed.', 'woosa-adyen'),
               wc_price($data->amount_value)
            ));

            break;


         case 'CANCEL_OR_REFUND':

            if($data->success === 'true'){

               $order->add_order_note(sprintf(
                  __('The payment amount of %s has been refunded.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));

            }else{

               self::payment_action_failed($order, $data->psp_reference, sprintf(
                  __('Refunding the payment amount of %s could not be refunded.', 'woosa-adyen'),
                  wc_price($data->amount_value)
               ));
            }

            break;


         case 'RECURRING_CONTRACT':

            if($data->success === 'true'){


               //in case we still don't have `recurringDetailReference` let's use `pspReference` from this event payload
               foreach($data->subscription_ids as $sub_id){

                  $recurr_reference = get_post_meta($sub_id, '_'.PREFIX.'_recurringDetailReference', true);

                  if( empty($recurr_reference) ){
                     update_post_meta($sub_id, '_'.PREFIX.'_recurringDetailReference', $data->psp_reference);
                  }
               }

            }

            break;


         case 'REPORT_AVAILABLE':

            Utility::wc_debug_log($data);

            break;
      }


      //update the cached payment methods for credit card payment method, in this way we ensure the new stored cards will be also included
      if( in_array( $order->get_payment_method(), ['woosa_adyen_bancontact', 'woosa_adyen_credit_card'] ) ){
         Settings::update_cached_payment_methods();
      }


      return '[accepted]';
   }



   /**
    * Sets order as payment failed
    *
    * @since 1.0.0
    * @param WC_Order $order
    * @param string $reference - payment reference
    * @param string $message
    * @return void
    */
   protected static function payment_action_failed($order, $reference, $message){

      if( ! $order->has_status('failed')){
         $order->update_status('failed');
         $order->add_order_note($message);
      }

      $order->set_transaction_id($reference);
      $order->save();
   }



   /**
    * Sets the order as payment completed
    *
    * @since 1.0.0
    * @param WC_Order $order
    * @param string $reference - payment reference
    * @param array $subscription_ids - list of subscription ids
    * @return void
    */
   protected static function payment_action_completed($order, $reference, $subscription_ids, $payment_method){

      $unpaid_subscriptions = array_filter( (array) get_post_meta($order->get_id(), '_'.PREFIX.'_unpaid_subscriptions', true) );

      //process subscriptions
      foreach($subscription_ids as $sub_id){

         if( isset($unpaid_subscriptions[$sub_id]) ){

            if(class_exists('\WC_Subscription')){

               $subscription = new \WC_Subscription($sub_id);
               $subscription->update_status('active');
               $subscription->save();

               unset($unpaid_subscriptions[$sub_id]);

               update_post_meta($order->get_id(), '_'.PREFIX.'_unpaid_subscriptions', $unpaid_subscriptions);
            }

         }
      }


      //mark order as completed as long as there are no unpaid subscriptions
      if( count($unpaid_subscriptions) == 0 ){

         //set order payment method via SEPA for recurring payments
         if( count($subscription_ids) > 0 && $payment_method === 'sepadirectdebit' ){

            $sepa_method_id = 'woosa_adyen_sepa_direct_debit';
            $sepa_settings = get_option("woocommerce_{$sepa_method_id}_settings");

            $order->set_payment_method($sepa_method_id);
            $order->set_payment_method_title( Utility::rgar($sepa_settings, 'title') );
         }

         $order->payment_complete( $reference );
         $order->add_order_note(sprintf(__('Order completed using %s .', 'woosa-adyen'), $order->get_payment_method_title()));
         $order->save();
      }
   }

}