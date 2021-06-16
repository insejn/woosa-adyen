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


class Klarna extends Ideal{


   /**
    * Constructor of this class.
    *
    * @since 1.1.0
    */
   public function __construct(){

      parent::__construct();

      $this->has_fields = false;

      $this->supports = [
         'products',
         'refunds',
      ];

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

      $payload = Abstract_Gateway::build_payment_payload($order, $reference);

      return $payload;
   }


}