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


class Klarna_PayNow extends Klarna{



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
         'DE' => [
            'currencies' => ['EUR'],
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
      ];
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Klarna - Pay now', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_default_description(){
      return sprintf(__('Pay the whole amount instantly, either by online banking or direct debit. %s', 'woosa-adyen'), '<br/>'.$this->show_supported_country());
   }



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.1.0
    * @return string
    */
   public function payment_method_type(){
      return 'klarna_paynow';
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


}