<?php
/**
 * Checkout
 *
 * This class extends WooCommerce checkout page.
 *
 * @package Woosa-Adyen/WooCommerce
 * @author Woosa Team
 * @since 1.0.4
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Checkout{

   /**
    * The instance of this class.
    *
    * @since 1.0.4
    * @var null|object
    */
   protected static $instance = null;


	/**
	 * Returns an instance of this class.
	 *
	 * @since 1.0.4
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
    * @since 1.0.4
    */
   public function __construct(){

      add_action('woocommerce_checkout_update_order_review', [$this, 'save_googlepay_fields']);

   }



   /**
    * Saves custom fields of googlepay payment method in cart session
    *
    * @since 1.0.4
    * @param string $post_data
    * @return void
    */
   public function save_googlepay_fields($post_data){

      parse_str($post_data, $payload);

      $token = isset($payload['woosa_adyen_googlepay_token']) ? $payload['woosa_adyen_googlepay_token'] : '';
      $description = isset($payload['woosa_adyen_googlepay_description']) ? $payload['woosa_adyen_googlepay_description'] : '';

      if( ! empty($token) ){
         WC()->session->set( 'woosa_adyen_googlepay_token', $token );
      }

      if( ! empty($description) ){
         WC()->session->set( 'woosa_adyen_googlepay_description', $description );
      }

   }



   /**
    * Checks if checkout contains at least one subscription.
    *
    * @since 1.0.9 - added support for variable subscription
    *              - change name to `has_subscription`
    * @since 1.0.3
    * @return bool
    */
   public static function has_subscription(){

      if( WC()->cart ){

         foreach(WC()->cart->get_cart() as $item){
            if( $item['data']->is_type(['subscription_variation', 'subscription'])){
               return true;
            }
         }
      }

      return false;
   }
}
