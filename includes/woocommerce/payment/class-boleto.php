<?php
/**
 * Boleto payment method
 *
 * This class creates Boleto payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.3
 */

namespace Woosa\Adyen;

use Woosa\Adyen\VIISON\AddressSplitter\AddressSplitter;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Boleto extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.0.3
    */
    public function __construct(){

      parent::__construct();

      $this->has_fields = true;

      add_action('woocommerce_thankyou_' . $this->id, [$this, 'display_payment_action']);

      add_action('woocommerce_order_details_after_order_table', [$this, 'display_order_items']);
   }



   /**
    * List of countries where is available.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [
         'BR' => [
            'currencies' => ['BRL'],
         ],
      ];
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.3
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Boleto', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0 - display supported countries
    * @since 1.0.3
    * @return string
    */
   public function get_default_description(){
      return $this->show_supported_country();
   }



   /**
    * Gets default description set in settings.
    *
    * @since 1.0.3
    * @return string
    */
   public function get_settings_description(){}



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.0.3
    * @return string
    */
   public function payment_method_type(){
      return 'boletobancario_santander';
   }


   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.3
    * @return string
    */
   public function recurring_payment_method(){}



   /**
    * Adds extra fields.
    *
    * @since 1.0.3
    * @return void
    */
    public function payment_fields() {

      parent::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
	 * Gets the transaction URL.
	 *
    * @since 1.0.3
	 * @param  WC_Order $order Order object.
	 * @return string
	 */
	public function get_transaction_url( $order ) {

      $this->view_transaction_url = $this->get_service_base_url().'/ca/ca/accounts/showTx.shtml?txType=Offer&pspReference=%s&accountKey=MerchantAccount.'.API::account()->merchant;

		return \WC_Payment_Gateway::get_transaction_url( $order );
   }



   /**
    * Generates extra fields HTML.
    *
    * @since 1.0.3
    * @return string
    */
   public function generate_extra_fields_html(){

      ?>
      <div class="<?php echo PREFIX;?>-card-form">

         <div class="adyen-checkout__field adyen-checkout__field--cardNumber">
            <div class="adyen-checkout__label__text">
               <?php _e('Social Security Number', 'woosa-adyen');?> <abbr class="required" title="required">*</abbr>
            </div>
            <div>
               <input type="text" name="<?php echo $this->id;?>_social_number" />
            </div>
         </div>

         <div class="adyen-checkout__field adyen-checkout__field--cardNumber">
            <div class="adyen-checkout__label__text">
               <?php _e('Delivery Date', 'woosa-adyen');?> <abbr class="required" title="required">*</abbr>
            </div>
            <div>
               <input type="text" class="<?php echo PREFIX;?>-datepicker" name="<?php echo $this->id;?>_delivery_date" />
            </div>
         </div>

      </div>
      <?php
   }



   /**
    * Validates extra added fields.
    *
    * @since 1.0.3
    * @return bool
    */
   public function validate_fields() {

      $is_valid = parent::validate_fields();

      $social_number = Utility::rgar($_POST, $this->id . '_social_number');
      $delivery_date = Utility::rgar($_POST, $this->id . '_delivery_date');
      $address = AddressSplitter::splitAddress(Utility::rgar($_POST, 'billing_address_1'));

      if( empty($social_number) ){
         wc_add_notice(__('Social Securiry Number (CPF/CNPJ) is a required field', 'woosa-adyen'), 'error');
         $is_valid = false;
      }

      if( empty($delivery_date) ){
         wc_add_notice(__('Delivery date is a required field', 'woosa-adyen'), 'error');
         $is_valid = false;
      }else{
         try{
            new \DateTime($delivery_date);
         }catch (\Exception $e){
            $is_valid = false;
            wc_add_notice(__('Delivery date format is invalid (YYYY-MM-DD).', 'woosa-adyen'), 'error');
         }
      }

      if( empty(Utility::rgar($address, 'houseNumber')) ){
         wc_add_notice(__('Please specify the house number in the address', 'woosa-adyen'), 'error');
         $is_valid = false;
      }

      return $is_valid;
   }



   /**
    * Builds the required payment payload
    *
    * @since 1.1.0 - use parent function to get common data
    * @since 1.0.3
    * @param WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $social_number = Utility::rgar($_POST, $this->id.'_social_number');
      $delivery_date = new \DateTime( Utility::rgar($_POST, $this->id.'_delivery_date') );

      $payload = array_merge(parent::build_payment_payload($order, $reference), [
         'socialSecurityNumber' => $social_number,
         'deliveryDate' => $delivery_date->format('Y-m-d'),
      ]);

      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.0.3
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

         $result_code  = Utility::rgar($request, 'resultCode');
         $reference    = Utility::rgar($request, 'pspReference');
         $download_url = Utility::rgars($request, 'action/downloadUrl', '', 'url');
         $bar_code     = Utility::rgars($request, 'action/reference');
         $expire_date  = Utility::rgars($request, 'action/expiresAt');
         $action       = Utility::rgar($request, 'action');

         $order->update_meta_data('_'.PREFIX.'_payment_pspReference', $reference);
         $order->update_meta_data('_'.PREFIX.'_payment_resultCode', $result_code);
         $order->update_meta_data('_'.PREFIX.'_download_url', $download_url);
         $order->update_meta_data('_'.PREFIX.'_bar_code', $bar_code);
         $order->update_meta_data('_'.PREFIX.'_expire_date', $expire_date);
         $order->update_meta_data('_'.PREFIX.'_payment_action', $action);
         $order->save();

         if( 'PresentToShopper' === $result_code ){

            $result = [
               'result'   => 'success',
               'redirect' => add_query_arg([
                  PREFIX.'_boleto_action' => $order->get_id(),
               ], $this->get_return_url($order))
            ];
         }
      }

      return $result;

   }



   /**
    * Displays action in a popup
    *
    * @since 1.0.3
    * @return string
    */
   public function display_payment_action(){

      if(isset($_GET[PREFIX.'_boleto_action'])){

         $order = wc_get_order(Utility::rgar($_GET, PREFIX.'_boleto_action'));

         if( $order instanceof \WC_Order){

            $action = json_encode( $order->get_meta('_'.PREFIX.'_payment_action', true) );

            ?>
               <div class="<?php echo PREFIX;?>-popup" style="display: none;" data-blur="true" data-escape="true">
                  <div>
                     <div id="<?php echo PREFIX;?>-boleto-action" class="<?php echo PREFIX;?>-component" data-payment_action='<?php echo $action;?>' data-order_id="<?php echo $order->get_id();?>">
                        <div class="<?php echo PREFIX;?>-component__text" style="display:none;"><?php _e('Processing...', 'woosa-adyen');?></div>
                     </div>
                  </div>
               </div>
            <?php
         }

      }
   }



   /**
    * Displays extra details in customer's order.
    *
    * @since 1.0.3
    * @param WC_Order $order
    * @return string
    */
   public function display_order_items($order){

      if( 'woosa_adyen_boleto' !== $order->get_payment_method() ) return;

      $download_url = $order->get_meta('_'.PREFIX . '_download_url', true);
      $bar_code     = $order->get_meta('_'.PREFIX . '_bar_code', true);
      $expire_date  = $order->get_meta('_'.PREFIX . '_expire_date', true);
      $expire_date  = new \DateTime($expire_date);

      ?>
      <h2 class="woocommerce-order-details__title"><?php _e('Boleto Details', 'woosa-adyen');?></h2>
      <table>
         <tr>
            <th><?php _e('Expiration Date', 'woosa-adyen');?></th>
            <td><?php echo $expire_date->format('Y-m-d');?></td>
         </tr>
         <tr>
            <th><?php _e('Barcode', 'woosa-adyen');?></th>
            <td><?php echo $bar_code;?></td>
         </tr>
         <tr>
            <th><?php _e('PDF file', 'woosa-adyen');?></th>
            <td><a class="button" href="<?php echo $download_url;?>" target="_blank"><?php _e('Click to Download', 'woosa-adyen');?></a></td>
         </tr>
      </table>
      <?php
   }



   /**
    * Adds an array of fields to be displayed on the gateway's settings screen.
    *
    * @since 1.0.3
    * @return void
    */
    public function init_form_fields() {

      parent::init_form_fields();

      $this->form_fields = array_merge(array(
         'notification_text'    => array(
            'type'        => 'notification_text',
         )
      ), $this->form_fields);
   }



   /**
    * Generates custom section for displaying notification info.
    *
    * @since 1.0.3
    * @param string $key
    * @param array $data
    * @return string
    */
   public function generate_notification_text_html( $key, $data ) {

      $url = add_query_arg([
         'section' => 'notifications',
      ], SETTINGS_URL);

		ob_start();
		?>
		<tr valign="top">
         <th><?php _e('Set Notification', 'woosa-adyen');?></th>
			<td class="forminp">
            <?php printf(
               __('Please make sure %s is aleady set in Adyen account!', 'woosa-adyen'),
               '<a href="'.$url.'">Boleto Bancario Pending Notification</a>'
            );?>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}


}