<?php
/**
 * Paypal payment method
 *
 * This class creates Paypal payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.1.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Paypal extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.1.0
    */
   public function __construct(){

      parent::__construct();

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
         '_ANY_' => [
            'currencies' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'INR', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
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
      return __('Adyen - Paypal', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0
    * @return string
    */
   public function get_default_description(){
      return $this->show_supported_country();
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
      return 'paypal';
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
    * Adds extra fields.
    *
    * @since 1.1.0
    * @return void
    */
   public function payment_fields() {

      parent::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
    * Adds an array of fields to be displayed on the gateway's settings screen.
    *
    * @since 1.1.0
    * @return void
    */
   public function init_form_fields() {

      $is_enabled = parent::init_form_fields();

      if( $is_enabled === false ) return;

      if('paypal' === $this->payment_method_type()){
         $this->form_fields = array_merge($this->form_fields, array(
            'config_desc'    => array(
               'title'       => __('Test mode', 'woosa-adyen'),
               'label'       => __('Enable/Disable', 'woosa-adyen'),
               'default' => 'yes',
               'type'        => 'config_desc',
            ),
         ));
      }
   }



   /**
    * Generates the HTML for `config_desc` field type
    *
    * @since 1.1.0
    * @return string
    */
   public function generate_config_desc_html(){

      ob_start();
      ?>
      <tr valign="top">
			<td colspan="2" class="forminp" style="padding: 0;">
            <h3><?php _e('Configure PayPal API permissions', 'woosa-adyen');?></h3>
            <p><?php _e("To connect your PayPal account with your Adyen integration you need to grant permission to Adyen's API to integrate with your PayPal account.", 'woosa0-adyen');?></p>
            <ol>
               <li>
                  <p><?php printf(__("Follow %sPayPal's instructions on granting third party permissions%s", 'woosa-adyen'), '<a href="https://developer.paypal.com/docs/classic/admin/third-party" target="_blank">', '</a>');?></p>
               </li>
               <li>
                  <p><?php printf(__('Under %s, depending on your account type, enter:', 'woosa-adyen'), '<b>Third Party Permission Username</b>');?></p>
                  <ul style="list-style: disc;padding-left: 20px;">
                     <li><b>Live:</b> <?php _e('Enter', 'woosa-adyen');?> <code>paypal_api2.adyen.com</code></li>
                     <li><b>Test:</b> <?php _e('Enter', 'woosa-adyen');?> <code>sell1_1287491142_biz_api1.adyen.com</code></li>
                  </ul>
               </li>
               <li>
                  <p><?php printf(__('In the %s list, select the following boxes: ', 'woosa-adyen'), '<b>Available Permissions</b>');?></p>
                  <ul style="list-style: disc;padding-left: 20px;">
                     <li><b>Use Express Checkout to process payments.</b></li>
                     <li><b>Issue a refund for a specific transaction.</b></li>
                     <li><b>Process your shopper's credit or debit card payments.</b></li>
                     <li><b>Authorize and capture your PayPal transactions.</b></li>
                     <li><b>Obtain information about a single transaction.</b></li>
                     <li><b>Obtain authorization for pre-approved payments and initiate pre-approved transactions.</b></li>
                     <li><b>Generate consolidated reports for all accounts. (if available in your region)</b></li>
                     <li><b>Use Express Checkout to process mobile payments. (if you plan on supporting mobile payments)</b></li>
                     <li><b>Charge an existing customer based on a prior transaction.</b></li>
                     <li><b>Create and manage Recurring Payments.</b></li>
                     <li><b>Obtain your PayPal account balance.</b></li>
                     <li><b>Initiate transactions to multiple recipients in a single batch.</b></li>
                  </ul>
               </li>
               <li>
                  <p><?php printf(__('Click %s.', 'woosa-adyen'), '<b>Add</b>');?></p>
               </li>
            </ol>
			</td>
		</tr>
      <?php

      return ob_get_clean();
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

      if(is_checkout() && isset($_GET['key']) && isset($_GET['payload'])){

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
                     'payload' => $_GET['payload'],
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