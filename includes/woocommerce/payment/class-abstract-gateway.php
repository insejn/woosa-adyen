<?php
/**
 * WooCommerce Payments
 *
 * This abstract class is used to extends WooCommerce Payments by different Adyen payment methods.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;

use Woosa\Adyen\VIISON\AddressSplitter\AddressSplitter;



//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

if(!class_exists('\WC_Payment_Gateway')){
   include_once WP_CONTENT_DIR . '/plugins/woocommerce/includes/abstracts/abstract-wc-settings-api.php';
   include_once WP_CONTENT_DIR . '/plugins/woocommerce/includes/abstracts/abstract-wc-payment-gateway.php';
}

abstract class Abstract_Gateway extends \WC_Payment_Gateway{


   /**
    * Whether or not the payment information was displayed
    *
    * @since 1.0.0
	 * @var bool
	 */
	public static $payment_info_displayed = false;


   /**
    * Whether or not the payment method is activ in Adyen account
    *
    * @since 1.0.0
	 * @var bool
	 */
	public $is_activated = null;



   /**
    * List of available currencies.
    *
    * @var array
    */
   public $currencies = [];



   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    */
    public function __construct(){

      $this->id                 = strtolower(str_replace('\\', '_', get_class($this)));
      $this->enabled            = 'no';
      $this->method_title       = $this->get_default_title();
      $this->method_description = $this->get_default_description();
      $this->testmode           = 'yes' === API::account()->test_mode;
      $this->icon               = $this->get_icon_url();
      $this->title              = $this->get_option('title', $this->get_default_title());
      $this->description        = $this->get_option('description');
      $this->is_activated       = API::is_payment_method_active( $this->payment_method_type() );
      $this->supports = [
         'products',
         'refunds',
      ];

      // Load the settings.
      $this->init_form_fields();
      $this->init_settings();

      add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
      add_action('woocommerce_scheduled_subscription_payment_'.$this->id, [$this, 'renewal_subscription'], 10, 2);
      add_action('woocommerce_thankyou', [$this, 'order_received_page'], 1);

      $this->send_payment_details();

   }



   /**
    * List of countries where the payment method is available only.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [];
   }



   /**
    * Displays supported countries and currencies.
    *
    * @since 1.1.0
    * @return string
    */
   public function show_supported_country(){

      $countries = [];

      foreach($this->available_countries() as $country_code => $data){

         $country_code = '_ANY_' === $country_code ? __('ANY', 'woosa-adyen') : $country_code;

         if( empty(Utility::rgar($data, 'currencies')) ){
            $countries[] = $country_code;
         }else{
            $currencies = Utility::rgar($data, 'currencies');
            $countries[] = $country_code . ' ('.implode(', ', $currencies).')';
         }
      }

      $result = empty($countries) ? sprintf(__('%sSupported country:%s ANY', 'woosa-adyen'), '<b>', '</b>') : sprintf(__('%sSupported country:%s %s', 'woosa-adyen'), '<b>', '</b>', implode(', ', $countries));

      return $result;
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.0
    * @return string
    */
   abstract public function get_default_title();



   /**
    * Gets default payment method description.
    *
    * @since 1.0.0
    * @return string
    */
   abstract public function get_default_description();



   /**
    * Gets default description set in settings.
    *
    * @since 1.0.0
    * @return string
    */
   abstract public function get_settings_description();



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.0.0
    * @return string
    */
   abstract public function payment_method_type();



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.0
    * @return string
    */
   abstract public function recurring_payment_method();



   /**
    * Gets payment method icon by method type.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_icon_url(){
      return apply_filters( PREFIX.'_'.$this->id . '_icon_url', PLUGIN_URL . '/assets/images/'.$this->payment_method_type().'.svg' );
   }



   /**
	 * Checks if the gateway is available for use.
	 *
    * @since 1.1.1 - check if there is a valid origin key
    * @since 1.1.0 - check if it's available based on country and currencies
    * @since 1.0.3 - add currency verification
    * @since 1.0.0
	 * @return bool
	 */
	public function is_available() {

      if( ! API::is_configured() ) return false;

      if(empty(API::get_origin_key())){
         return false;
      }

      //only in WooCommerce checkout
      if( WC()->cart && $this->get_order_total() > 0 ) {

         if( ! empty($this->available_countries()) ){

            $customer_country = WC()->customer->get_billing_country();
            $any_country = Utility::rgar($this->available_countries(), '_ANY_');
            $country = Utility::rgar($this->available_countries(), $customer_country);

            if( empty($country) && empty($any_country) ){

               return false;

            }else{

               $currencies = empty($any_country) ? $country['currencies'] : $any_country['currencies'];

               if( ! empty($currencies) && ! in_array(get_woocommerce_currency(), $currencies)){
                  return false;
               }
            }
         }
      }

      return parent::is_available();
   }



	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * When this gateway is toggled on via AJAX, if this returns true a
	 * redirect will occur to the settings page instead.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function needs_setup() {

      if( ! $this->is_activated ){
         return true;
      }

		return false;
	}



   /**
	 * Gets the transaction URL.
	 *
    * @since 1.0.0
	 * @param  WC_Order $order Order object.
	 * @return string
	 */
	public function get_transaction_url( $order ) {

      $this->view_transaction_url = $this->get_service_base_url().'/ca/ca/accounts/showTx.shtml?txType=Payment&pspReference=%s&accountKey=MerchantAccount.'.API::account()->merchant;

		return parent::get_transaction_url( $order );
   }



   /**
    * Gets the base URL of Adyen platform
    *
    * @since 1.0.0
    * @return string
    */
   public function get_service_base_url(){

      if ( $this->testmode ) {
			return 'https://ca-test.adyen.com';
      }

      return 'https://ca-live.adyen.com';
   }



   /**
    * Gets details of a given method type.
    *
    * @since 1.0.0
    * @return array
    */
   public function get_payment_method_details(){

      $method = [];

      foreach(API::get_payment_methods() as $method){
         if(Utility::rgar($method, 'type') == $this->payment_method_type()){
            return $method;
         }
      }

      return $method;

   }



   /**
    * Checks if a given payment method is enabled in WooCommerce
    *
    * @since 1.0.0
    * @param string $method_id
    * @return boolean
    */
   public function is_payment_method_enabled($method_id){

      $method_settings = get_option("woocommerce_{$method_id}_settings", []);

      if( Utility::rgar($method_settings, 'enabled') === 'yes' ) return true;

      return false;
   }



   /**
    * Checks whether or not SEPA Direct Debit is enabled then this could support recurring payments
    *
    * @since 1.0.0
    * @return bool
    */
   public function support_recurring(){

      if( $this->is_payment_method_enabled('woosa_adyen_sepa_direct_debit') ) return true;

      return false;
   }



   /**
    * Validates extra added fields.
    *
    * @since 1.0.0
    * @return bool
    */
   public function validate_fields() {

      $is_valid = parent::validate_fields();

      return $is_valid;
   }



   /**
    * Gets subscriptions for an order
    *
    * @since 1.0.3
    * @param int|string $order_id
    * @return array
    */
   public function get_subscriptions_for_order($order_id){

      if(function_exists('\wcs_get_subscriptions_for_order')){
         return wcs_get_subscriptions_for_order( $order_id );
      }

      return [];
   }



   /**
    * Gets subscriptions for an renewal order
    *
    * @since 1.0.3
    * @param WC_Order $order
    * @return array
    */
   public function get_subscriptions_for_renewal_order($order){

      if(function_exists('\wcs_get_subscriptions_for_renewal_order')){
         return wcs_get_subscriptions_for_renewal_order( $order );
      }

      return [];
   }



   /**
    * Sends the payment when a WC Subscription is renewed.
    *
    * @since 1.1.0 - use function `build_payment_payload` to have the common data included
    *              - replace `_#subscription#_` with `-S`
    * @since 1.0.10- added `recurringProcessingModel` set on `Subscription`
    * @since 1.0.7 - use \WC_Subscription instance to manipulate the metadata
    *              - use the shopper reference from the metadata
    * @since 1.0.0
    * @param float $amount
    * @param WC_Order $order
    * @return void
    */
   public function renewal_subscription($amount, $order){

      $subscriptions = $this->get_subscriptions_for_renewal_order( $order );

      foreach($subscriptions as $sub_id => $subscription){

         $recurr_reference  = $subscription->get_meta('_' . PREFIX . '_recurringDetailReference');
         $shopper_reference = $subscription->get_meta('_' . PREFIX . '_shopper_reference');
         $psp_reference     = $subscription->get_meta('_' . PREFIX . '_payment_pspReference');

         //in case we still don't have `recurringDetailReference` let's look into the recurring list of the shopper
         if( empty($recurr_reference) ){
            $recurr_reference = API::get_recurring_reference($shopper_reference, $psp_reference);
            $subscription->update_meta_data('_' . PREFIX . '_recurringDetailReference', $recurr_reference);
            $subscription->save();
         }

         if(empty($recurr_reference)){

            Utility::wc_error_log([
               'TITLE' => '====== RENEWAL SUBSCRIPTION ERROR ======',
               'MESSAGE' => 'The recurring reference is not found',
               'DATA' => [
                  'subscription_id' => $sub_id,
                  'order_id' => $order->get_id(),
               ]
            ]);

            $order->update_status('failed');
            $order->add_order_note(__('We could not found a valid recurring reference.', 'woosa-adyen'));
            $order->save();

         }else{

            $reference = "{$order->get_id()}-S{$sub_id}";
            $payload = array_merge($this->build_payment_payload($order, $reference), [
               'amount' => [
                  'currency' => get_woocommerce_currency(),
                  'value' => API::format_amount($amount)
               ],
               'paymentMethod' => [
                  'type' => $this->recurring_payment_method(),
                  'recurringDetailReference' => $recurr_reference
               ],
               'shopperInteraction' => 'ContAuth',
               'recurringProcessingModel' => 'Subscription',
            ]);

            $request = API::send_payment($payload);
         }

      }

   }



   /**
    * Processes the payment.
    *
    * @since 1.0.9 - use order instance to save the shopper reference
    * @since 1.0.0
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      $order = wc_get_order($order_id);
      $order->update_meta_data('_'.PREFIX.'_shopper_reference', API::shopper_reference());
      $order->save();
   }



	/**
	 * Processes a refund.
	 *
    * @since 1.1.0 - show error if payment reference is empty
    * @since 1.0.0
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

      $order            = wc_get_order($order_id);
      $reference        = $order->get_meta('_'.PREFIX.'_payment_pspReference');
      $payment_captured = $order->get_meta('_'.PREFIX.'_payment_captured');
      $payment_method   = $order->get_payment_method();


      if($payment_method === 'woosa_adyen_boleto'){
         return new \WP_Error( 'broke', sprintf(
            __( 'Sorry you cannot refund payments captured via %s', 'woosa-adyen'),
            $order->get_payment_method_title()
         ));
      }


      if( empty($reference) ){
         return new \WP_Error( 'broke', __( 'Sorry you cannot refund this because the payment reference is invalid. Please try to refund it manually from Adyen account.', 'woosa-adyen'));
      }


      if($payment_captured === 'yes'){

         if($payment_method === 'woosa_adyen_sepa_direct_debit'){
            return new \WP_Error( 'broke', sprintf(
               __( 'Sorry we cannot refund payments via %s', 'woosa-adyen'),
               $order->get_payment_method_title()
            ));
         }

         $request = API::refund_payment( $reference, $amount );

      }else{

         if($amount == $order->get_total()){

            $request = API::cancel_payment($reference);

         }else{

            return new \WP_Error( 'broke',
               __( 'Sorry, you cannot refund a partial amount because the transaction has not been captured yet but only cancel the entire payment', 'woosa-adyen')
            );
         }
      }

      if(isset($request['pspReference']) && $request['response']){

         //save refund reference
         update_post_meta( $order_id, '_'.PREFIX.'_refund_pspReference', $request['pspReference']);

         return true;
      }

		return false;
	}



   /**
    * Adds an array of fields to be displayed on the gateway's settings screen.
    *
    * @since 1.0.7 - use `get_settings_description` instead of `get_default_description`
    * @since 1.0.0
    * @return void
    */
   public function init_form_fields() {

      if( ! $this->is_activated ){

         $this->form_fields = array(
            'show_notice' => array(
               'type' => 'show_notice',
            ),
         );

      }else{

         $this->form_fields = array(
            'enabled'        => array(
               'title'       => __('Enable/Disable', 'woosa-adyen'),
               'type'        => 'checkbox',
               'label'       => sprintf(__('Enable %s', 'woosa-adyen'), $this->get_default_title()),
               'default'     => 'no'
            ),
            'title'          => array(
               'title'       => __('Title', 'woosa-adyen'),
               'type'        => 'text',
               'desc_tip'    => __('The title which the user sees during checkout.', 'woosa-adyen' ),
               'default'     => $this->get_default_title(),
            ),
            'description'    => array(
               'title'       => __('Description', 'woosa-adyen'),
               'type'        => 'text',
               'desc_tip'    => __('The description which the user sees during checkout.', 'woosa-adyen'),
               'default'     => $this->get_settings_description(),
            )
         );
      }
   }



   /**
    * Generates the HTML for `show_notice` field type
    *
    * @since 1.0.0
    * @return string
    */
   public function generate_show_notice_html(){

      ob_start();
      ?>
      <tr valign="top">
			<td colspan="2" class="forminp" style="padding: 0;">
            <p>
               <?php printf(
                  __('This payment method is not enabled in your Adyen account. %sGo to my account.%s', 'woosa-adyen'),
                  '<a href="'.$this->get_service_base_url().'" target="_blank">',
                  '</a>'
               );?>
            </p>
            <span>
               <?php printf(
               __('%sNote:%s Please make sure you have removed the cache. %sGo to settings.%s', 'woosa-adyen'),
               '<b>',
               '</b>',
               '<a href="'.SETTINGS_URL.'&section=tools">',
               '</a>'
               );?>
               </span>
			</td>
		</tr>
      <?php

      return ob_get_clean();
   }



   /**
    * Displays payment information on thank you page.
    *
    * @since 1.0.0
    * @param int $order_id
    * @return string|null
    */
   public function order_received_page($order_id){

      $order = wc_get_order($order_id);
      $info = sprintf(__('Order completed using %s', 'woosa-adyen'), $order->get_payment_method_title());

      if( ! self::$payment_info_displayed && $order->get_payment_method() === $this->id){
         echo '<section class="woocommerce-info" >'.wptexturize( $info ).'</section>';
      }

      self::$payment_info_displayed = true;

      //collect payload if any
      if(isset($_GET['payload'])){
         $order->update_meta_data('_' . PREFIX . '_payment_payload', $_GET['payload']);
         $order->save();
      }

      //collect redirect result if any
      if(isset($_GET['redirectResult'])){
         $order->update_meta_data('_' . PREFIX . '_payment_redirectResult', $_GET['redirectResult']);
         $order->save();
      }
   }



   /**
    * Updates order status based on the payment result.
    *
    * @since 1.1.3 - set order on `on-hold` for `Received` status
    * @since 1.1.0
    * @param \WC_Order $order
    * @param array $response - payment request response
    * @return void
    */
   protected function update_order_status_based_on_payment_result(\WC_Order $order, $response){

      $statuses = [
         'Authorised' => 'processing',
         'Refused'    => 'failed',
         'Error'      => 'failed',
         'Cancelled'  => 'cancelled',
         'Received'  => 'on-hold',
      ];

      $result_code      = Utility::rgar($response, 'resultCode');
      $psp_reference    = Utility::rgar($response, 'pspReference');
      $recurr_reference = Utility::rgar($response, 'recurringDetailReference');
      $status           = Utility::rgar($statuses, $result_code);

      if( ! empty($status) ){
         $order->update_status($status);
      }

      if( ! empty($psp_reference) ){
         $order->update_meta_data('_'.PREFIX.'_payment_pspReference', $psp_reference);
      }

      if( ! empty($recurr_reference) ){
         $order->update_meta_data('_'.PREFIX.'_recurringDetailReference', $recurr_reference);
      }

      $order->save();
   }



   /**
    * Create a list with the order items which will be used in API request
    *
    * @since 1.1.0
    * @param \WC_Order $order
    * @return void
    */
   public function list_order_items(\WC_Order $order){

      $list_items = [];

      foreach($order->get_items() as $item){

         $product        = $item->get_product();
         $price_excl_tax = $product->get_price_excluding_tax($item->get_quantity(), $item->get_total());
         $tax_amount     = $item->get_total_tax();
         $tax_percentage = API::format_amount(number_format($tax_amount * 100 / $price_excl_tax, 2, '.', ''));

         $list_items[] = [
            'id'                 => $product->get_id(),
            'quantity'           => $item->get_quantity(),
            'amountIncludingTax' => API::format_amount($product->get_price_including_tax($item->get_quantity(), $item->get_total())),
            'amountExcludingTax' => API::format_amount($price_excl_tax),
            'taxPercentage'      => $tax_percentage,
            'taxAmount'          => API::format_amount($tax_amount),
            'description'        => $product->get_name(),
            'productUrl'         => get_permalink($product->get_id()),
            'imageUrl'           => wp_get_attachment_url($product->get_image_id()),
         ];
      }

      return $list_items;
   }



   /**
    * Builds the required payment payload
    *
    * @since 1.1.3 - add by default `shopperInteraction` and `recurringProcessingModel`
    * @since 1.1.1- fix wrong variable name
    * @since 1.1.0
    * @param \WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $payload = apply_filters(PREFIX . '\abstract_gateway\payment_payload', [
         'channel'                  => 'web',
         'origin'                   => home_url(),
         'reference'                => Order::add_reference_prefix($reference),
         'returnUrl'                => $this->get_return_url( $order ),
         'merchantAccount'          => API::account()->merchant,
         'countryCode'              => $order->get_billing_country(),
         'telephoneNumber'          => $order->get_billing_phone(),
         'lineItems'                => $this->list_order_items($order),
         'recurringProcessingModel' => Checkout::has_subscription() ? 'Subscription' : 'CardOnFile',
         'shopperInteraction'       => 'Ecommerce',
         'shopperIP'                => Utility::get_client_ip(),
         'shopperLocale'            => get_locale(),
         'shopperEmail'             => $order->get_billing_email(),
         'shopperReference'         => $order->get_meta('_'.PREFIX.'_shopper_reference', true),
         'shopperName' => [
            'firstName' => $order->get_billing_first_name(),
            'lastName'  => $order->get_billing_last_name(),
         ],
         'amount' => [
            "currency" => get_woocommerce_currency(),
            "value" => API::format_amount( $this->get_order_total() )
         ],
         'paymentMethod' => [
            'type' => $this->payment_method_type(),
         ],
         'billingAddress' => [
            'city'              => $order->get_billing_city(),
            'country'           => $order->get_billing_country(),
            'postalCode'        => str_replace('-', '', $order->get_billing_postcode()),
            'stateOrProvince'   => $order->get_billing_state(),
         ],
         'deliveryAddress' => [
            'city'              => empty($order->get_shipping_city()) ? $order->get_billing_city() : $order->get_shipping_city(),
            'country'           => empty($order->get_shipping_country()) ? $order->get_billing_country() : $order->get_shipping_country(),
            'postalCode'        => empty($order->get_shipping_postcode()) ? str_replace('-', '', $order->get_billing_postcode()) : str_replace('-', '', $order->get_shipping_postcode()),
            'stateOrProvince'   => empty($order->get_shipping_state()) ? $order->get_billing_state() : $order->get_shipping_state(),
         ],
         'browserInfo' => [
            'userAgent'      => $_SERVER['HTTP_USER_AGENT'],
            'acceptHeader'   => $_SERVER['HTTP_ACCEPT'],
            'language'       => Utility::get_locale(),
            'javaEnabled'    => true,
            'colorDepth'     => 24,
            'timeZoneOffset' => 0,
            'screenHeight'   => 723,
            'screenWidth'    => 1536
         ],
      ]);


      try{

         $b_address = AddressSplitter::splitAddress( $order->get_billing_address_1() );

         $payload['billingAddress']['street'] = Utility::rgar($b_address, 'streetName');
         $payload['billingAddress']['houseNumberOrName'] = Utility::rgar($b_address, 'houseNumber');

      }catch(\Exception $e){

         $payload['billingAddress']['street'] = $order->get_billing_address_1();
         $payload['billingAddress']['houseNumberOrName'] = $order->get_billing_address_2();
      }


      try{

         $s_address = AddressSplitter::splitAddress( $order->get_shipping_address_1() );

         $payload['deliveryAddress']['street'] = Utility::rgar($s_address, 'streetName');
         $payload['deliveryAddress']['houseNumberOrName'] = Utility::rgar($s_address, 'houseNumber');

      }catch(\Exception $e){

         $payload['deliveryAddress']['street'] = empty($order->get_shipping_address_1()) ? $order->get_billing_address_1() : $order->get_shipping_address_1();
         $payload['deliveryAddress']['houseNumberOrName'] = empty($order->get_shipping_address_2()) ? $order->get_billing_address_2() : $order->get_shipping_address_2();
      }


      return $payload;
   }



   /**
    * Sends received payment details to be processed
    *
    * @since 1.1.3 - add support for API Checkout v67
    * @since 1.0.0
    * @return void
    */
   public function send_payment_details(){

      if(is_checkout() && isset($_GET['key']) && isset($_GET['redirectResult'])){

         $order_id = wc_get_order_id_by_order_key($_GET['key']);
         $order = wc_get_order($order_id);
         $method_types = [
            'scheme'         => 'woosa_adyen_credit_card',
            'bcmc'           => 'woosa_adyen_bancontact',
            'klarna'         => 'woosa_adyen_klarna',
            'klarna_paynow'  => 'woosa_adyen_klarna_paynow',
            'klarna_account' => 'woosa_adyen_klarna_account',
            'paypal'         => 'woosa_adyen_paypal',
            'ideal'          => 'woosa_adyen_ideal',
            'directEbanking' => 'woosa_adyen_sofort',
            'giropay'        => 'woosa_adyen_giropay',
         ];

         if($order instanceof \WC_Order){

            //only if matches the order payment method
            if( isset($method_types[$this->payment_method_type()]) && $order->get_payment_method() === $method_types[$this->payment_method_type()] ){

               $payload = [
                  'details' => [
                     'redirectResult' => urldecode($_GET['redirectResult']),
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