<?php
/**
 * Klarna
 *
 * This class creates payment method: Klarna - Pay later .
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.1.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Klarna extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.1.0
    */
   public function __construct(){

      parent::__construct();

      // $this->supports = array_merge($this->supports, [
      //    'subscriptions',
      //    'subscription_cancellation',
      //    'subscription_suspension',
      //    'subscription_reactivation',
      //    'subscription_amount_changes',
      //    'subscription_date_changes',
      //    // 'subscription_payment_method_change',
      //    // 'subscription_payment_method_change_customer',
      //    // 'subscription_payment_method_change_admin',
      //    'multiple_subscriptions'
      // ]);

      $this->send_payment_details();

   }



   /**
    * List of countries where is available.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [
         'AT' => [
            'currencies' => ['EUR'],
         ],
         'BE' => [
            'currencies' => ['EUR'],
         ],
         'DK' => [
            'currencies' => ['DKK'],
         ],
         'FI' => [
            'currencies' => ['EUR'],
         ],
         'DE' => [
            'currencies' => ['EUR'],
         ],
         'NO' => [
            'currencies' => ['NOK'],
         ],
         'SE' => [
            'currencies' => ['SEK'],
         ],
         'CH' => [
            'currencies' => ['CHF'],
         ],
         'NL' => [
            'currencies' => ['EUR'],
         ],
         'GB' => [
            'currencies' => ['GBP'],
         ],
         'US' => [
            'currencies' => ['USD'],
         ],
      ];
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Klarna - Pay later', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_default_description(){
      return sprintf(__('Pay after the goods have been delivered. %s', 'woosa-adyen'), '<br/>'.$this->show_supported_country());
   }



   /**
    * Gets default description set in settings.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_settings_description(){}



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.1.0
    * @return string
    */
   public function payment_method_type(){
      return 'klarna';
   }



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.1.0
    * @return string
    */
   public function recurring_payment_method(){
      return $this->payment_method_type();
   }



   /**
    * Validates extra added fields.
    *
    * @since 1.1.0
    * @return bool
    */
   public function validate_fields() {

      $is_valid = Abstract_Gateway::validate_fields();

      return $is_valid;
   }



   /**
    * Builds the required payment payload
    *
    * @since 1.1.0
    * @param \WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $payload = parent::build_payment_payload($order, $reference);

      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.1.0
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      parent::process_payment($order_id);

      $order     = wc_get_order($order_id);
      $reference = $order_id;
      $payload   = $this->build_payment_payload( $order, $reference );
      $result    = ['result' => 'failure'];

      $recurr_reference = [];
      $subscriptions    = $this->get_subscriptions_for_order( $order_id );
      $subscription_ids = [];

      //recurring payments
      if(count($subscriptions) > 0){

         foreach($subscriptions as $sub_id => $item){
            $subscription_ids[$sub_id] = $sub_id;
            $recurr_reference[] = $sub_id;
         }

         $reference = \implode('-S', $recurr_reference);
         $reference = $order_id.'-S'.$reference;
         $payload = $this->build_payment_payload( $order, $reference );

         //for tokenizing
         $payload['storePaymentMethod'] = true;

         //create a list with unpaid subscriptions
         $order->update_meta_data('_'.PREFIX.'_unpaid_subscriptions', $subscription_ids);

      }

      $request = API::send_payment($payload);

      if(is_string($request)){

         wc_add_notice($request, 'error');

      }else{

         $result_code = Utility::rgar($request, 'resultCode');
         $action      = Utility::rgar($request, 'action');

         $order->update_meta_data('_'.PREFIX.'_payment_resultCode', $result_code);
         $order->update_meta_data('_'.PREFIX.'_payment_action', $action);

         $redirect_url = 'RedirectShopper' == $result_code ? $action['url'] : $this->get_return_url( $order );

         if( ! empty($redirect_url) ){
            $result = [
               'result'   => 'success',
               'redirect' => $redirect_url
            ];
         }

      }

      $order->save();

      return $result;

   }



   /**
    * Sends received payment details to be processed
    *
    * @since 1.1.0
    * @return void
    */
   public function send_payment_details(){

      if(is_checkout() && isset($_GET['key']) && isset($_GET['redirectResult'])){

         $order_id = wc_get_order_id_by_order_key($_GET['key']);
         $order    = wc_get_order($order_id);

         if($order instanceof \WC_Order){

            $payment_method_type = str_replace('woosa_adyen_', '', $order->get_payment_method()); //replace the prefix

            //only if the order payment method type matches
            if($payment_method_type === $this->payment_method_type()){

               $action = $order->get_meta('_' . PREFIX . '_payment_action', true);
               $payload = [
                  'paymentData' => Utility::rgar($action, 'paymentData'),
                  'details' => [
                     'redirectResult' => $_GET['redirectResult'],
                  ]
               ];

               $request = API::send_payment_details($payload);

               $this->update_order_status_based_on_payment_result($order, $request);

               wp_redirect( $this->get_return_url( $order ) );
            }

         }

      }
   }


}