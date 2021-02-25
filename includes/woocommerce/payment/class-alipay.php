<?php
/**
 * Alipay payment method
 *
 * This class creates Alipay payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Alipay extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    */
   public function __construct(){

      parent::__construct();

      add_action('woocommerce_pay_order_after_submit', [$this, 'display_payment_action']);
   }



   /**
    * List of countries where is available.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [
         'CN' => [
            'currencies' => [],
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
      return __('Adyen - Alipay', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0 - display supported countries
    * @since 1.0.0
    * @return string
    */
   public function get_default_description(){
      return $this->show_supported_country();
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
      return 'alipay';
   }


   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.0
    * @return string
    */
   public function recurring_payment_method(){}



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
    * @since 1.0.3 - added installments field
    * @since 1.0.0
    * @return string
    */
   public function generate_extra_fields_html(){
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
    * Builds the required payment payload
    *
    * @since 1.1.0 - use parent function to get common data
    * @since 1.0.0
    * @param WC_Order $order
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
    * @since 1.0.0
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      parent::process_payment($order_id);

      $order     = wc_get_order($order_id);
      $reference = $order_id;
      $payload   = $this->build_payment_payload($order, $reference);
      $result    = ['result' => 'failure'];

      $request   = API::send_payment($payload);

      if(is_string($request)){

         wc_add_notice($request, 'error');

      }else{

         $result_code = Utility::rgar($request, 'resultCode');
         $reference   = Utility::rgar($request, 'pspReference');
         $action      = Utility::rgar($request, 'action');

         $order->update_meta_data('_' . PREFIX . '_payment_pspReference', $reference);
         $order->update_meta_data('_' . PREFIX . '_payment_resultCode', $result_code);
         $order->update_meta_data('_' . PREFIX . '_payment_action', $action);
         $order->save();

         if( 'RedirectShopper' == $result_code ){

            $result = [
               'result'   => 'success',
               'redirect' => add_query_arg([
                  PREFIX.'_alipay_action' => $order->get_id(),
               ],  $order->get_checkout_payment_url())
            ];

         }
      }

      return $result;

   }



   /**
    * Displays the payment action in a popup
    *
    * @since 1.0.4
    * @return string
    */
   public function display_payment_action(){

      if(isset($_GET[PREFIX.'_alipay_action'])){

         $order = wc_get_order(Utility::rgar($_GET, PREFIX.'_alipay_action'));

         if( $order instanceof \WC_Order){

            $action = json_encode( $order->get_meta('_'.PREFIX.'_payment_action', true) );

            ?>
               <div class="<?php echo PREFIX;?>-popup" style="display: none;">
                  <div>
                     <div id="<?php echo PREFIX;?>-alipay-action" class="<?php echo PREFIX;?>-component" data-payment_action='<?php echo $action;?>' data-order_id="<?php echo $order->get_id();?>">
                        <div class="<?php echo PREFIX;?>-component__text"><?php _e('Processing...', 'woosa-adyen');?></div>
                     </div>
                  </div>
               </div>
            <?php
         }

      }
   }


}