<?php
/**
 * iDeal payment method
 *
 * This class creates iDeal payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Ideal extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    */
    public function __construct(){

      parent::__construct();

      $this->has_fields = true;

      if( $this->support_recurring() ){
         $this->supports = array_merge($this->supports, [
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            // 'subscription_payment_method_change',
            // 'subscription_payment_method_change_customer',
            // 'subscription_payment_method_change_admin',
            'multiple_subscriptions'
         ]);
      }
   }



   /**
    * List of countries where is available.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [
         'NL' => [
            'currencies' => ['EUR'],
         ],
      ];
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - iDEAL', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0 - display supported countries
    * @since 1.0.0
    * @return string
    */
   public function get_default_description(){
      return sprintf(__('In order to support recurring payments with iDeal you have to enable SEPA Direct Debit first. %s', 'woosa-adyen'), '<br/>'.$this->show_supported_country());
   }



   /**
    * Gets default description set in settings.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_settings_description(){}



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.0.0
    * @return string
    */
   public function payment_method_type(){
      return 'ideal';
   }



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.0
    * @return string
    */
   public function recurring_payment_method(){
      return 'sepadirectdebit';
   }



   /**
    * Adds extra fields.
    *
    * @since 1.0.0
    * @return void
    */
   public function payment_fields() {

      parent::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
    * Generates extra fields HTML.
    *
    * @since 1.1.0 - added CSS class
    * @since 1.0.0
    * @return string
    */
   public function generate_extra_fields_html(){

      $output = '';
      $method = $this->get_payment_method_details();

      if(isset($method['details'])){

         $output = '<select class="'.PREFIX.'-payment-dropdown" name="'.$this->id.'_issuer">';
            $output .= '<option value="">'.__('Select your bank', 'woosa-adyen').'</option>';
            foreach(Utility::rgars($method, 'details/0/items', []) as $item){
               $output .= '<option value="'.$item['id'].'">'.$item['name'].'</option>';
            }
         $output .= '</select>';
      }

      return $output;
   }



   /**
    * Validates extra added fields.
    *
    * @since 1.0.0
    * @return bool
    */
   public function validate_fields() {

      $is_valid = parent::validate_fields();
      $issuer = Utility::rgar($_POST, $this->id.'_issuer');

      if(empty($issuer)){
         $is_valid = false;
         wc_add_notice(__('Please select your bank account.', 'woosa-adyen'), 'error');
      }

      return $is_valid;
   }



   /**
    * Builds the required payment payload
    *
    * @since 1.1.0 - use parent function to get common data
    * @since 1.0.0
    * @param \WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $issuer = Utility::rgar($_POST, $this->id.'_issuer');

      $payload = array_merge(parent::build_payment_payload($order, $reference), [
         'paymentMethod' => [
            'type' => $this->payment_method_type(),
            'issuer' => $issuer
         ]
      ]);

      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.1.0 - replace `_#subscription#_` with `-S`
    * @since 1.0.7 - use \WC_Order instance to manipulate metadata
    * @since 1.0.0
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      parent::process_payment($order_id);

      $order     = wc_get_order($order_id);
      $reference = $order_id;
      $payload   = $this->build_payment_payload( $order, $reference );
      $result = ['result' => 'failure'];

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


}