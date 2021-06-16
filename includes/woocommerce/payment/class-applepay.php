<?php
/**
 * Google Pay payment method
 *
 * This class creates Google Pay payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.4
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Applepay extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.0.4
    */
    public function __construct(){

      parent::__construct();

      $this->has_fields = true;
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.4
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Apple Pay', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.0.4
    * @return string
    */
   public function get_default_description(){}



   /**
    * Gets default description set in settings.
    *
    * @since 1.0.4
    * @return string
    */
   public function get_settings_description(){}



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.0.4
    * @return string
    */
   public function payment_method_type(){
      return 'applepay';
   }


   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.4
    * @return string
    */
   public function recurring_payment_method(){}



   /**
    * Adds extra fields.
    *
    * @since 1.0.4
    * @return void
    */
    public function payment_fields() {

      parent::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
	 * Checks if the gateway is available for use.
	 *
    * @since 1.0.4
	 * @return bool
	 */
	public function is_available() {

      if( empty($this->get_option('merchant_identifier')) ){
         return false;
      }

      return parent::is_available();
   }



   /**
    * Generates extra fields HTML.
    *
    * @since 1.0.4
    * @return string
    */
   public function generate_extra_fields_html(){

      ?>
      <input type="hidden" id="<?php echo $this->id;?>_merchant_identifier" value="<?php echo $this->get_option('merchant_identifier');?>">
      <input type="hidden" id="<?php echo $this->id;?>_merchant_name" value="<?php echo $this->get_option('merchant_name');?>">

      <input type="hidden" id="<?php echo $this->id;?>_token" name="<?php echo $this->id;?>_token">

      <div id="applepay-container"></div>
      <?php
   }


   /**
    * Validates extra added fields.
    *
    * @since 1.0.4
    * @return bool
    */
   public function validate_fields() {

      $is_valid = parent::validate_fields();
      $token = $_POST[$this->id.'_token'];

      if(empty($token)){
         wc_add_notice(__('Sorry it looks like Google token is not generated, please refresh the page and try again!', 'woosa-adyen'), 'error');
         $is_valid = false;
      }

      return $is_valid;
   }



   /**
    * Builds the required payment payload
    *
    * @param WC_Order $order
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $token_raw = stripslashes($_POST[$this->id.'_token']);
      $token = json_decode($token_raw);

      $payload = [
         'amount' => [
            "currency" => get_woocommerce_currency(),
            "value" => API::format_amount( $this->get_order_total() )
         ],
         'reference' => $order->get_id(),
         'paymentMethod' => [
            'type' => $this->payment_method_type(),
            'applepay.token' => $token
         ],
         'returnUrl' => $this->get_return_url($order),
         'merchantAccount' => API::account()->merchant
      ];


      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.0.4
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      parent::process_payment($order_id);

      $order   = wc_get_order($order_id);
      $payload = $this->build_payment_payload($order);
      $request = API::send_payment($payload);

      if(is_string($request)){

         wc_add_notice($request, 'error');

      }else{

         $result_code = Utility::rgar($request, 'resultCode');
         $reference   = Utility::rgar($request, 'pspReference');
         $action   = Utility::rgar($request, 'action');

         update_post_meta($order->get_id(), '_'.PREFIX.'_payment_pspReference', $reference);
         update_post_meta($order->get_id(), '_'.PREFIX.'_payment_resultCode', $result_code);
         update_post_meta($order->get_id(), '_'.PREFIX.'_payment_action', $action);

         if($result_code == 'RedirectShopper'){

            return array(
               'result'   => 'success',
               'redirect' => add_query_arg([
                  PREFIX.'_applepay_action' => $order->get_id(),
               ], $order->get_checkout_payment_url())
            );

         }

         return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
         );
      }

      return array('result' => 'failure');

   }



   /**
    * Displays QR in a popup
    *
    * @since 1.0.4
    * @return string
    */
   public function display_payment_action(){

      if(isset($_GET[PREFIX.'_applepay_action'])){

         $order = wc_get_order(Utility::rgar($_GET, PREFIX.'_applepay_action'));

         if( $order instanceof \WC_Order){

            $action = json_encode( $order->get_meta('_'.PREFIX.'_payment_action', true) );

            ?>
               <div class="<?php echo PREFIX;?>-popup" style="display: none;">
                  <div>
                     <div id="<?php echo PREFIX;?>-googlepay-action" class="<?php echo PREFIX;?>-component" data-payment_action='<?php echo $action;?>' data-order_id="<?php echo $order->get_id();?>">
                        <div class="<?php echo PREFIX;?>-component__text" style="display:none;"><?php _e('Processing...', 'woosa-adyen');?></div>
                     </div>
                  </div>
               </div>
            <?php
         }

      }
   }



   /**
    * Adds an array of fields to be displayed on the gateway's settings screen.
    *
    * @since 1.0.3
    * @return void
    */
   public function init_form_fields() {

      parent::init_form_fields();

      if('applepay' === $this->payment_method_type()){
         $this->form_fields = array_merge($this->form_fields, array(
            'merchant_identifier'    => array(
               'title'       => __('Merchant Identifier', 'woosa-adyen'),
               'type'        => 'text',
               'desc_tip'    => __('Your Apple Merchant ID', 'woosa-adyen'),
            ),
            'merchant_name'    => array(
               'title'       => __('Merchant Name', 'woosa-adyen'),
               'type'        => 'text',
               'desc_tip'    => __('The merchant name that you want displayed on the Apple Pay payment sheet', 'woosa-adyen'),
            ),
         ));
      }
   }


}