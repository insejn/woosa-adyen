<?php
/**
 * Core class
 *
 * This sets all together.
 *
 * @package Woosa-Adyen
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Core extends Abstract_Core{


   /**
    * Initiates hooks.
    *
    * @since 1.0.0
    * @return void
    */
   protected function init_hooks(){

      add_filter('woocommerce_payment_gateways', [__CLASS__, 'payment_gateway']);
      add_action('rest_api_init', [Routes::class, 'register']);

   }



   /**
    * Initiates modules.
    *
    * @since 1.0.0
    * @return void
    */
   protected function init_modules(){

      //--- extend WC settings
      Settings::instance()->init_hooks();

      //--- extend WC orders
      Order::instance();

      //--- extend WC my account
      My_Account::instance();

      //--- extend WC checkout
      Checkout::instance();

      //--- display caught errors
      Errors::display();
   }



   /**
    * Adds new gateway to WooCommerce payments.
    *
    * @since 1.0.0
    * @param array $gateways
    * @return void
    */
    public static function payment_gateway($gateways) {

      $gateways[] = Ideal::class;
      $gateways[] = Sepa_Direct_Debit::class;
      $gateways[] = Credit_Card::class;
      $gateways[] = Giropay::class;
      $gateways[] = Sofort::class;
      $gateways[] = Bancontact::class;
      $gateways[] = Boleto::class;
      $gateways[] = Alipay::class;
      $gateways[] = Wechatpay::class;
      $gateways[] = Googlepay::class;
      // $gateways[] = Applepay::class;
      $gateways[] = Klarna::class;
      $gateways[] = Klarna_PayNow::class;
      $gateways[] = Klarna_Account::class;
      $gateways[] = Paypal::class;

      return $gateways;
   }



   /**
    * Checks whether or not a given country code is valid (exists in the Woo countries list).
    *
    * @since 1.0.0
    * @param string $code
    * @return boolean
    */
   public static function is_valid_country_code($code){

      $countries = (new \WC_Countries)->get_countries();

      if(array_key_exists(strtoupper($code), $countries)) return true;

      return false;
   }



   /**
    * Runs when plugin is updated.
    *
    * @return void
    */
   protected function on_upgrade(){

      /**
       * Make sure the old `adn_is_authorized` and old `adn_testmode` flag will become `adn_is_authorized_{$env}` and `adn_test_mode`
       * to follow the abstract API class logic
       * @since 1.1.3
       */
      $old_auth = get_option(PREFIX . '_is_authorized');
      $old_testmode = get_option(PREFIX . '_testmode');

      if('yes' === $old_auth){

         $env = 'yes' === $old_testmode ? 'test' : 'live';

         API::instance($env)->set_as_authorized();
      }

      update_option(PREFIX . '_test_mode', $old_testmode, false);
   }

}