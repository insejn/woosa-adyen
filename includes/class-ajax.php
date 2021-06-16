<?php
/**
 * AJAX
 *
 * This class is used for processing AJAX requests.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class AJAX{

   /**
    * The instance of this class.
    *
    * @since 1.0.0
    * @var null|object
    */
   protected static $instance = null;



	/**
	 * Returns an instance of this class.
	 *
	 * @since 1.0.0
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
   }



   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    * @return void
    */
   public function __construct(){

      add_action('wp_ajax_'.PREFIX.'_revoke_authorization', [__CLASS__, 'process_revoke_authorization']);

      add_action('wp_ajax_nopriv_'.PREFIX.'_send_payment_details', [__CLASS__, 'send_payment_details']);
      add_action('wp_ajax_'.PREFIX.'_send_payment_details', [__CLASS__, 'send_payment_details']);

      add_action('wp_ajax_'.PREFIX.'_remove_card', [__CLASS__, 'remove_card']);
      add_action('wp_ajax_'.PREFIX.'_remove_gdpr', [__CLASS__, 'process_remove_gdpr']);

      add_action('wp_ajax_'.PREFIX.'_capture_payment', [__CLASS__, 'capture_payment']);
   }



   /**
    * Triggers the request to revoke the authorization
    *
    * @since 1.0.0
    * @return string
    */
   public static function process_revoke_authorization(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $extra = $_POST['extra'];
      $env = $extra['environment'];

      API::instance($env)->set_as_unauthorized();

      wp_send_json_success();
   }



   /**
    * Sends payment details received from payment action.
    *
    * @since 1.0.6 - use default checkout url if there are subscriptions
    * @since 1.0.4
    * @return string
    */
   public static function send_payment_details(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $order_id = Utility::rgar($_POST, 'order_id');
      $payload = Utility::rgar($_POST, 'action_data');
      $order = wc_get_order($order_id);
      $redirect_url = wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );


      if($order instanceof \WC_Order){

         $request = API::send_payment_details($payload);
         $redirect_url = $order->get_checkout_order_received_url();

         $result_code = Utility::rgar($request, 'resultCode');
         $reason      = Utility::rgar($request, 'refusalReason');
         $action      = Utility::rgar($request, 'action');

         if($result_code == 'ChallengeShopper'){
            update_post_meta($order->get_id(), '_'.PREFIX.'_payment_resultCode', $result_code);
            update_post_meta($order->get_id(), '_'.PREFIX.'_payment_action', $action);

            $checkout_url = Checkout::has_subscription() ? wc_get_checkout_url() : $order->get_checkout_payment_url();

            $redirect_url = add_query_arg([
               PREFIX.'_card_action' => $order->get_id(),
            ],  $checkout_url);
         }

         if( 'Refused' === $result_code ){
            $order->update_status('failed');
            $order->add_order_note(sprintf('Failed reason: %s', $reason));
         }
      }

      wp_send_json_success(['redirect' => $redirect_url]);
   }



   /**
    * Removes a card by the given id.
    *
    * @since 1.0.10 - update cached payment methods
    * @since 1.0.3
    * @return string
    */
   public static function remove_card(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $reference = Utility::rgar($_POST, 'reference');
      $request = API::disable_recurring( API::shopper_reference(), $reference );

      //update cache
      Settings::update_cached_payment_methods();

      wp_send_json_success();

   }



   /**
    * Processes the request of removing data protection.
    *
    * @since 1.1.0
    * @return string
    */
   public static function process_remove_gdpr(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $order_id = Utility::rgar($_POST, 'order_id');
      $order = wc_get_order($order_id);

      if($order instanceof \WC_Order){

         $reference = $order->get_meta('_' . PREFIX . '_payment_pspReference');
         $request = API::remove_data_protection($reference);

         if(in_array($request->code, [200, 201])){

            $order->update_meta_data('_' . PREFIX . '_gdpr_removed', 'yes');
            $order->save();

            wp_send_json_success();

         }else{

            $message = isset($request->body->message) ? $request->body->message : __('Something went wrong, please try again later or contact us.', 'woosa-adyen');

            wp_send_json_error([
               'message' => $message
            ]);

         }

      }else{

         wp_send_json_error([
            'message' => __('Order not found.', 'woosa-adyen')
         ]);
      }

   }



   /**
    * Captures payments.
    *
    * @since 1.0.3
    * @return string
    */
   public static function capture_payment(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $order_id  = Utility::rgar($_POST, 'order_id');
      $order     = wc_get_order($order_id);
      $reference = get_post_meta($order->get_id(), '_'.PREFIX.'_payment_pspReference', true);
      $amount    = get_post_meta($order->get_id(), '_order_total', true);

      $request = API::capture_payment($reference, $amount);

      if( isset($request['response']) && '[capture-received]' == $request['response']){
         update_post_meta($order->get_id(), '_'.PREFIX.'_payment_captured', 'yes');

         $order->payment_complete( $reference );
         $order->add_order_note( __('The payment has been successfully captured.', 'woosa-adyen') );
         $order->save();

         wp_send_json_success();

      }else{

         wp_send_json_error();
      }


   }

}