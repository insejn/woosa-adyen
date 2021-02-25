<?php
/**
 * Adyen API Requests
 *
 * This contains methods to comunicate with Adyen via API.
 *
 * @package Woosa-Adyen/Requests
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;

use Woosa\Adyen\Adyen\Service;
use Woosa\Adyen\Adyen\Environment;
use Woosa\Adyen\Adyen\Client;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class API{


   /**
    * Returns account information.
    *
    * @since 1.0.0
    * @return void
    */
   public static function account(){

      $test_mode = get_option(PREFIX.'_testmode', 'no');
      $api_key   = $test_mode == 'yes' ? get_option(PREFIX.'_test_api_key') : get_option(PREFIX.'_api_key');
      $merchant  = $test_mode == 'yes' ? get_option(PREFIX.'_test_merchant_account') : get_option(PREFIX.'_merchant_account');
      $prefix = $test_mode == 'no' ? get_option(PREFIX.'_url_prefix', '') : '';

      return (object) array(
         'key' => $api_key,
         'merchant' => $merchant,
         'test_mode' => $test_mode,
         'prefix' => $prefix,
      );
   }



   /**
    * Formats the client errors with easy-to-understand error messages for admin (customer).
    *
    * @since 1.0.0
    * @param string $key
    * @param string $default
    * @return string
    */
   public static function get_error_msg($key = '', $default = ''){

      $list = array(
         'Please provide a valid Checkout API Key' => __('Please provide a valid API Key', 'woosa-adyen'),
      );

      return Utility::rgar($list, $key, $default);
   }



   /**
    * Returns information about our plugin and the CMS
    *
    * @since 1.0.0
    * @return array
    */
   public static function app_info(){

      global $woocommerce;

      return [
         'applicationInfo' => [
            'adyenPaymentSource' => [
               'name' => 'adyen-woocommerce',
               'version' => PLUGIN_VERSION
            ],
            'externalPlatform' => [
               'name' => 'WooCommerce',
               'version' => $woocommerce->version,
               'integrator' => 'Woosa'
            ]
         ]
      ];
   }



   /**
    * Checks whether or not the Adyen API info
    *
    * @since 1.0.1
    * @return boolean
    */
   public static function is_configured(){

      $authorized = get_option(PREFIX.'_is_authorized');

      if( empty($authorized) || empty(self::account()->key) || empty(self::account()->merchant) || (self::account()->test_mode === 'no' && empty(self::account()->prefix)) ){
         return false;
      }

      return true;
   }



   /**
    * API client.
    *
    * @since 1.0.0
    * @return object
    */
   public static function client(){

      try{

         $env = self::account()->test_mode === 'yes' ? Environment::TEST : Environment::LIVE;

         $client = new Client();
         $client->setXApiKey( self::account()->key );
         $client->setEnvironment( $env, self::account()->prefix );

         Errors::delete( 'client' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'client' );
      }

      return $client;
   }



   /**
    * Gets the entire response of payment methods.
    *
    * @since 1.1.1 - add $cached parameter
    * @since 1.0.7 - added payment_methods cacheing with transient
    * @since 1.0.3
    * @param string $country
    * @param integer $amount
    * @param bool $cached - whether or not to use cached data
    * @return array
    */
   public static function get_response_payment_methods($country = null, int $amount = 0, $cached = true){

      $result = [];

      if( ! self::is_configured() ) return $result;

      if( ! is_null($country) && ! Core::is_valid_country_code($country)) return $result;

      if($cached){
         $result = get_transient( PREFIX . '_payment_methods_' . $country );
      }

      if ( empty( $result ) ){

         try{

            $payload = array(
               'merchantAccount' => self::account()->merchant,
               'shopperReference' => self::shopper_reference(),
               'channel' => 'Web',
            );

            if( ! is_null($country) ){

               $payload = array_merge($payload, [
                  'countryCode' => $country,
                  'amount' => array(
                     'currency' => get_woocommerce_currency(),
                     'value' => self::format_amount($amount)
                  )
               ]);
            }

            $service = new Service\Checkout(self::client());
            $result = $service->paymentMethods($payload);

            set_transient( PREFIX . '_payment_methods_' . $country , $result, \DAY_IN_SECONDS );

         }catch (\Exception $e){

            Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);
         }

      }

      return $result;
   }



   /**
    * Gets available payment methods.
    *
    * @since 1.0.0
    * @param string $country
    * @param integer $amount
    * @return array
    */
   public static function get_payment_methods($country = null, int $amount = 0){

      $result = [];
      $response = self::get_response_payment_methods($country, $amount);

      if(isset($response['paymentMethods'])){
         $result = $response['paymentMethods'];
      }

      return $result;
   }



   /**
    * Gets available stored payment methods.
    *
    * @since 1.1.1 - cache results per shopper reference and only for logged in users
    * @since 1.0.3
    * @param string $country
    * @param integer $amount
    * @return array
    */
   public static function get_stored_payment_methods($country = null, int $amount = 0){

      $shopper_reference = self::shopper_reference();
      $result = get_transient( PREFIX . '_stored_payment_methods_'.$shopper_reference );

      if(empty($result) && is_user_logged_in()){
         $response = self::get_response_payment_methods($country, $amount, false);

         if(isset($response['storedPaymentMethods'])){
            $result = $response['storedPaymentMethods'];

            set_transient( PREFIX . '_stored_payment_methods_'.$shopper_reference , $result, \DAY_IN_SECONDS );
         }
      }

      return array_filter((array) $result);
   }



   /**
    * Gets stored cards which has `Ecommerce` supported
    *
    * @since 1.0.3
    * @param string $country
    * @param integer $amount
    * @return array
    */
   public static function get_ec_stored_cards($country = null, int $amount = 0){

      $cards = [];
      $list = self::get_stored_payment_methods($country, $amount);

      foreach($list as $item){
         if(in_array('Ecommerce', $item['supportedShopperInteractions'])){
            $cards[] = $item;
         }
      }

      return $cards;
   }



   /**
    * Retrieves available card types
    *
    * @since 1.0.0
    * @param string $country
    * @return array
    */
   public static function get_card_types($country = null){

      $result = [];

      if( ! is_null($country) && ! Core::is_valid_country_code($country)) return $result;

      $response = self::get_response_payment_methods($country);

      if(isset($response['groups'])){

         foreach($response['groups'] as $group){
            if($group['name'] == 'Credit Card'){
               $result = $group['types'];
            }
         }
      }

      return $result;
   }



   /**
    * Checks whether or not the given payment method is activated
    *
    * @since 1.0.4 - add caching for 1 hour
    * @since 1.0.0
    * @param string $method
    * @return boolean
    */
   public static function is_payment_method_active($method){

      $is_active = get_transient( PREFIX . '_is_active_'.$method );

      if( empty($is_active) ){

         foreach(self::get_payment_methods() as $item){
            if(Utility::rgar($item, 'type') === $method){
               set_transient( PREFIX . '_is_active_'.$method, true, \HOUR_IN_SECONDS );
               return true;
            }
         }
      }

      return $is_active;
   }



   /**
    * Sends a given payment.
    *
    * @since 1.0.0
    * @param array $payload
    * @return array
    */
   public static function send_payment($payload){

      $result = [];

      if( ! self::is_configured() ) return $result;

      try{

         //add our app info in the call
         $payload = array_merge($payload, self::app_info());

         $service = new Service\Checkout(self::client());
         $response = $service->payments($payload);
         $result = $response;

         Errors::delete( 'send_payment' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'send_payment' );

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());
      }


      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== SEND PAYMENT ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;
   }



   /**
    * Sends a payment details.
    *
    * @link https://docs.adyen.com/api-explorer/#/PaymentSetupAndVerificationService/payments/details
    *
    * @since 1.0.0
    * @param array $payload
    * @return array
    */
   public static function send_payment_details($payload){

      $result = [];

      if( ! self::is_configured() ) return $result;

      try{

         //add our app info in the call
         $payload = array_merge($payload, self::app_info());

         $service = new Service\Checkout(self::client());
         $response = $service->paymentsDetails($payload);
         $result = $response;

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());
      }


      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== SEND PAYMENT DETAILS ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;
   }



   /**
    * Checks a payment result.
    *
    * @since 1.0.0
    * @param string $payload - Encrypted and signed payment result data
    * @return array
    */
   public static function get_payment_result($payload){

      $result = [];

      if( ! self::is_configured() ) return $result;

      try{

         $service = new Service\Checkout(self::client());
         $response = $service->paymentsResult(['payload' => $payload]);
         $result = $response;

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }

      return $result;

   }



   /**
    * Refunds a payment.
    *
    * @link https://docs.adyen.com/api-explorer/#/Payment/v46/refund
    *
    * @since 1.0.0
    * @param string $reference - payment reference
    * @param int $amount - payment amount
    * @return array
    */
   public static function refund_payment($reference, $amount){

      $result = [];
      $payload = [
         'modificationAmount' => [
            'currency' => get_woocommerce_currency(),
            'value' => self::format_amount($amount),
         ],
         // 'reference' => 'YourModificationReference',
         'originalReference' => $reference,
         'merchantAccount' => API::account()->merchant
      ];

      if( ! self::is_configured() ) return $result;

      try{

         $service = new Service\Modification(self::client());
         $response = $service->refund($payload);
         $result = $response;

         Errors::delete( 'refund_payment' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'refund_payment' );

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }


      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== REFUND PAYMENT ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;

   }



   /**
    * Cancels the authorisation on an uncaptured payment.
    *
    * @link https://docs.adyen.com/development-resources/payment-modifications/cancel
    *
    * @since 1.0.0
    *
    * @param string $reference
    * @return array
    */
   public static function cancel_payment($reference){

      $result = [];
      $payload = [
         'originalReference' => $reference,
         // 'reference' => 'YourModificationReference',
         'merchantAccount' => API::account()->merchant
      ];

      if( ! self::is_configured() ) return $result;

      try{

         $service = new Service\Modification(self::client());
         $response = $service->cancel($payload);
         $result = $response;

         Errors::delete( 'cancel_payment' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'cancel_payment' );

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }


      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== CANCEL PAYMENT ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;

   }



   /**
    * Captures an authorised payment.
    *
    * @link https://docs.adyen.com/checkout/capture
    *
    * @since 1.0.0
    *
    * @param string $reference
    * @param int $amount - payment amount
    * @return array
    */
   public static function capture_payment($reference, $amount){

      $result = [];
      $payload = [
         'modificationAmount' => [
            'currency' => get_woocommerce_currency(),
            'value' => self::format_amount($amount),
         ],
         'originalReference' => $reference,
         'merchantAccount' => API::account()->merchant
      ];

      if( ! self::is_configured() ) return $result;

      try{

         $service = new Service\Modification(self::client());
         $response = $service->capture($payload);
         $result = $response;

         Errors::delete( 'capture_payment' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'capture_payment' );

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }


      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== CAPTURE PAYMENT ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;

   }



   /**
    * Removes data protection for the given payment reference.
    *
    * @since 1.1.0
    * @param string $pspReference
    * @return object
    */
   public static function remove_data_protection($pspReference){

      $env = 'yes' === self::account()->test_mode ? 'test' : 'live';
      $url = "https://ca-{$env}.adyen.com/ca/services/DataProtectionService/v1/requestSubjectErasure";
      $payload = [
         'headers' => [
            'x-API-key' => self::account()->key,
            'content-type' => 'application/json',
         ],
         'body' => json_encode([
            'merchantAccount' => self::account()->merchant,
            'pspReference' => $pspReference,
            'forceErasure' => true,
         ])
      ];

      $request = wp_remote_post($url, $payload);


      if(is_wp_error($request)){

         $response = (object)[
            'code' => $request->get_error_code(),
            'body' => (object) [
               'message' => $request->get_error_message()
            ],
         ];

      }else{

         $body = json_decode(wp_remote_retrieve_body($request));
         $code = wp_remote_retrieve_response_code($request);

         $response = (object)[
            'code' => $code,
            'body' => $body,
         ];
      }

      $log = [
         '_DESCRIPTION' => '==== REMOVE DATA PROTECTION GDPR ====',
         '_REQUEST_PAYLOAD' => $payload,
         '_REQUEST_RESPONSE' => $response,
      ];

      if( ! in_array($request->code, [200, 201]) ){
         Utility::wc_error_log($log, __FILE__, __LINE__);
      }

      if(DEBUG){
         Utility::wc_debug_log($log, __FILE__, __LINE__);
      }

      return $response;
   }



   /**
    * Disables a shopper's saved payment methods.
    *
    * @link https://docs.adyen.com/checkout/tokenization/managing-tokens#disable-stored-details
    *
    * @since 1.0.10 - added debug log
    * @since 1.0.0
    *
    * @param string $shopper_reference
    * @return array
    */
   public static function disable_recurring($shopper_reference, $recurr_reference = null){

      $result = [];
      $payload = [
         'shopperReference' => $shopper_reference,
         'merchantAccount' => API::account()->merchant
      ];

      if( ! self::is_configured() ) return $result;

      if( ! is_null($recurr_reference) ){
         $payload['recurringDetailReference'] = $recurr_reference;
      }

      try{

         $service = new Service\Recurring(self::client());
         $response = $service->disable($payload);
         $result = $response;

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }

      if(DEBUG){
         Utility::wc_debug_log([
            '_DESCRIPTION' => '==== REMOVE SAVED CREDIT CARD ====',
            '_REQUEST_PAYLOAD' => $payload,
            '_REQUEST_RESPONSE' => $result,
         ], __FILE__, __LINE__);
      }

      return $result;

   }



   /**
    * Lists recurring details for a given shopper
    *
    * @since 1.0.0
    * @param string $shopper_reference
    * @return array
    */
   public static function list_recurring_details($shopper_reference){

      $result = [];
      $payload = [
         'shopperReference' => $shopper_reference,
         'merchantAccount' => API::account()->merchant
      ];

      if( ! self::is_configured() ) return $result;

      try{

         $service = new Service\Recurring(self::client());
         $response = $service->listRecurringDetails($payload);
         $result = $response;

         Errors::delete( 'list_recurring_details' );

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( self::get_error_msg($e->getMessage(), $e->getMessage()), 'list_recurring_details' );

         $result = self::get_error_msg($e->getMessage(), $e->getMessage());

      }

      return $result;
   }



   /**
    * Gets the recurring reference for a given shopper
    *
    * @since 1.0.0
    * @param string $shopper - user reference
    * @param string $reference - first payment reference
    * @return string
    */
   public static function get_recurring_reference($shopper, $reference){

      $recurr_reference = null;
      $details = self::list_recurring_details($shopper);

      if(is_array($details) && isset($details['details'])){

         foreach($details['details'] as $items){

            foreach ($items as $value) {

               if( Utility::rgar($value, 'firstPspReference') === $reference){
                  return $value['recurringDetailReference'];
               }
            }

         }
      }

      return $recurr_reference;

   }



   /**
    * Checks if the the API is valid
    *
    * @since 1.0.4 - check if API merchant/key is valid
    * @since 1.0.0
    * @return void
    */
   public static function check_connection(){

      try{

         $payload = array(
            'merchantAccount' => self::account()->merchant,
            'shopperReference' => self::shopper_reference(),
            'channel' => 'Web',
         );

         $service = new Service\Checkout(self::client());
         $result = $service->paymentMethods($payload);

         Errors::delete( 'connection' );

         update_option(PREFIX.'_is_authorized', 'yes');

         self::generate_origin_keys();

      }catch (\Exception $e){

         Utility::wc_error_log($e->getMessage(), __FILE__, __LINE__);

         Errors::save( API::get_error_msg($e->getMessage(), $e->getMessage()), 'connection' );

         delete_option(PREFIX.'_is_authorized');

         return false;
      }

      return true;
   }



   /**
    * Generates a shopper reference based on the WP user id.
    *
    * @since 1.0.6 - generate a unique guest id.
    * @since 1.0.0
    * @return string
    */
   public static function shopper_reference(){

      $guest_id = md5(uniqid(time(), true));
      $user_id = is_user_logged_in() ? get_current_user_id() : $guest_id;

      return "user_{$user_id}";
   }



   /**
    * Gets amount decimals for the shop currency code.
    *
    * @since 1.0.0
    * @return integer
    */
   public static function currency_decimal(){

      $three = array('BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND');
      $zero = array('CVE', 'DJF', 'GNF', 'IDR', 'JPY', 'KMF', 'KRW', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF');

      if(in_array(get_woocommerce_currency(), $three)) return 3;

      if(in_array(get_woocommerce_currency(), $zero)) return 0;

      return 2;
   }



   /**
    * Gets the shop domain used for generating origin keys.
    *
    * @since 1.0.7
    * @return string
    */
   public static function get_origin_domain(){

      $protocol = Utility::rgar($_SERVER, 'HTTPS') === 'on' ? 'https://' : 'http://';
      $port = in_array( Utility::rgar($_SERVER, 'SERVER_PORT'), [ '80', '443' ] ) ? '':':'.Utility::rgar($_SERVER, 'SERVER_PORT');
      $domain = $protocol.Utility::rgar($_SERVER, 'HTTP_HOST').$port;

      return $domain;
   }



   /**
    * Generates origin key for the current webshop
    *
    * @since 1.0.7 - replace home_url with the origin domain
    * @since 1.0.0
    * @return void
    */
   public static function generate_origin_keys(){

      if( ! self::is_configured() ) return;

      $service = new Service\CheckoutUtility(self::client());
      $payload = ["originDomains" => [self::get_origin_domain()]];

      $request = $service->originKeys($payload);

      if(isset($request['originKeys'])){
         update_option(PREFIX . '_origin_keys', $request['originKeys']);
      }
   }



   /**
    * Get the origin key
    *
    * @since 1.0.8 - fix: return the value
    * @since 1.0.7 - replace home_url with the origin domain
    * @since 1.0.0
    * @return string|null
    */
   public static function get_origin_key(){

      $keys = get_option(PREFIX . '_origin_keys', []);

      return Utility::rgar($keys, self::get_origin_domain(), null);
   }



   /**
    * Gets the number of installments based on the country.
    *
    * @since 1.0.3
    * @param string $country - ISO CODE
    * @param string $max_installments - the max number of allowed installments
    * @return false|array|string
    */
   public static function get_installments_by_country($country, $max_installments){

      $list = [
         'BR' => $max_installments,
         'MX' => [3, 6, 9, 12, 18],
         'TR' => [2, 3, 6, 9],
      ];

      if(isset($list[$country])){
         return $list[$country];
      }

      return false;
   }



   /**
    * Checks whether or not the installment value is valid.
    *
    * @since 1.0.3
    * @param string|int $number
    * @param string $country
    * @param string|int $max_installments
    * @return boolean
    */
   public static function is_valid_installment($number, $country, $max_installments){

      $is_valid = true;
      $value = self::get_installments_by_country($country, $max_installments);


      if( is_array($value) ){

         if( ! in_array($number, $value) ){
            $is_valid = false;
         }

      }elseif( (int) $value > 0 && $number > $max_installments ){
         $is_valid = false;
      }

      return $is_valid;

   }



   /**
    * Formats a given amount according to required currency decimals.
    *
    * @since 1.0.0
    * @param string $amount
    * @return integer
    */
   public static function format_amount($amount){
      return (int) number_format( $amount, self::currency_decimal(), '', '' );
   }



   /**
    * List of payment methods which are immediately captured.
    *
    * @since 1.1.0
    * @return array
    */
   public static function immediate_payment_methods(){

      return [
         'ideal',
         'giropay',
         'directEbanking',
         'bcmc',
         'alipay'
      ];
   }



   /**
    * List of payment methods which are manually captured.
    *
    * @since 1.1.0
    * @return array
    */
   public function manual_payment_methods(){

      return [
         'klarna',
         'klarna_paynow',
         'klarna_account',
      ];
   }



   /**
    * Checks whether the given payment method is manually captured.
    *
    * @since 1.1.0
    * @param string $method
    * @return boolean
    */
   public static function is_manual_payment($method){

      if( in_array($method, self::manual_payment_methods()) ){
         return true;
      }

      return false;
   }



   /**
    * Checks whether the given payment method is immediatley captured.
    *
    * @since 1.1.0
    * @param string $method
    * @return boolean
    */
   public static function is_immediate_payment($method){

      if( in_array($method, self::immediate_payment_methods()) ){
         return true;
      }

      return false;
   }

}
