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


class Googlepay extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.1.0 - add support for subscriptions
    * @since 1.0.4
    */
    public function __construct(){

      parent::__construct();

      $this->has_fields = true;

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



   /**
    * Gets default payment method title.
    *
    * @since 1.0.4
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Google Pay', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0 - display supported countries
    * @since 1.0.4
    * @return string
    */
   public function get_default_description(){
      return $this->show_supported_country();
   }



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
      return 'paywithgoogle';
   }



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.1.0 - add recurring method type
    * @since 1.0.4
    * @return string
    */
   public function recurring_payment_method(){
      return $this->payment_method_type();
   }



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
    * Generates extra fields HTML.
    *
    * @since 1.0.4
    * @return string
    */
   public function generate_extra_fields_html(){

      $description = WC()->session->get( $this->id . '_description' );
      $token = WC()->session->get( $this->id . '_token' );
      $show_desc = ! empty($description) && ! empty($token) ? 'display: block;' : '';
      ?>
      <div id="<?php echo PREFIX;?>-googlepay-container">
         <div id="<?php echo $this->id;?>_button"></div>
         <div class="googlepay-description" style="<?php echo $show_desc;?>"><?php echo $description;?></div>
         <input type="hidden" id="<?php echo $this->id;?>_token" name="<?php echo $this->id;?>_token" value='<?php echo $token;?>'>
         <input type="hidden" id="<?php echo $this->id;?>_description" name="<?php echo $this->id;?>_description" value="<?php echo $description;?>">

         <input type="hidden" id="<?php echo $this->id;?>_merchant_identifier" value="<?php echo $this->get_option('merchant_identifier');?>">
         <input type="hidden" id="<?php echo $this->id;?>_testmode" value="<?php echo $this->get_option('testmode', 'yes');?>">
      </div>
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
    * @since 1.1.0 - use parent function to get common data
    * @since 1.0.4
    * @param WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $token_raw = $_POST[$this->id.'_token'];
      $token = stripslashes($token_raw);
      $token = json_decode($token);

      $payload = array_merge(parent::build_payment_payload($order, $reference), [
         'paymentMethod' => [
            'type' => $this->payment_method_type(),
            'paywithgoogle.token' => $token
         ]
      ]);

      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.1.0 - add support for subscriptions
    * @since 1.0.4
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
         $reference   = Utility::rgar($request, 'pspReference');
         $action      = Utility::rgar($request, 'action');

         $order->update_meta_data('_'.PREFIX.'_payment_pspReference', $reference);
         $order->update_meta_data('_'.PREFIX.'_payment_resultCode', $result_code);
         $order->update_meta_data('_'.PREFIX.'_payment_action', $action);
         $order->save();

         //clear the token from the cart session
         WC()->session->__unset( $this->id . '_token');
         WC()->session->__unset( $this->id . '_description');

         if( 'RedirectShopper' == $result_code ){

            $result = [
               'result'   => 'success',
               'redirect' => add_query_arg([
                  PREFIX.'_googlepay_action' => $order->get_id(),
               ], $order->get_checkout_payment_url())
            ];

         }else{

            $result = [
               'result'   => 'success',
               'redirect' => $this->get_return_url( $order )
            ];
         }
      }

      return $result;

   }



   /**
    * Displays QR in a popup
    *
    * @since 1.0.4
    * @return string
    */
   public function display_payment_action(){

      if(isset($_GET[PREFIX.'_googlepay_action'])){

         $order = wc_get_order(Utility::rgar($_GET, PREFIX.'_googlepay_action'));

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
    * @since 1.0.4
    * @return void
    */
   public function init_form_fields() {

      $is_enabled = parent::init_form_fields();

      if( $is_enabled === false ) return;

      $desc = sprintf(__('1. If you already have a Google Pay Developer Profile then navigate to your Profile and find your Google merchant ID otherwise you have to %srequest one here%s', 'woosa-adyen'), '<a href="https://developers.google.com/pay/api/web/guides/test-and-deploy/request-prod-access" target="_blank">', '</a>').'</br>';
      $desc .= __('2. Register your fully qualified domains that will invoke Google Pay API', 'woosa-adyen').'</br>';

      if('paywithgoogle' === $this->payment_method_type()){
         $this->form_fields = array_merge($this->form_fields, array(
            'testmode'    => array(
               'title'       => __('Test mode', 'woosa-adyen'),
               'label'       => __('Enable/Disable', 'woosa-adyen'),
               'default' => 'yes',
               'type'        => 'checkbox',
            ),
            'merchant_identifier'    => array(
               'title'       => __('Merchant Identifier', 'woosa-adyen'),
               'type'        => 'text',
               'description'    => $desc,
            ),
         ));
      }
   }


}