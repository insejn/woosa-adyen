<?php
/**
 * Orders
 *
 * This class extends WooCommerce orders.
 *
 * @package Woosa-Adyen/WooCommerce
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Order{

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
    */
   public function __construct(){

      add_action('woocommerce_admin_order_data_after_order_details', [__CLASS__, 'capture_payment_button']);

      add_action('woocommerce_order_details_before_order_table', [__CLASS__, 'show_remove_data_protection']);
   }



   /**
    * Renders capture payment button.
    *
    * @since 1.1.0 - add capture button for manual payment methods
    * @since 1.0.3
    * @param WC_Order $order
    * @return string
    */
   public static function capture_payment_button($order){

      $capture     = get_option(PREFIX.'_capture_payment', 'immediate');
      $is_captured = get_post_meta($order->get_id(), '_'.PREFIX.'_payment_captured', true);
      $statuses    = ['processing', 'on-hold'];
      $payment_method_type = str_replace('woosa_adyen_', '', $order->get_payment_method()); //replace the prefix

      if(
         ! in_array($order->get_status(), $statuses) ||
         'yes' === $is_captured ||
         ('immediate' === $capture && ! API::is_manual_payment($payment_method_type)) ||
         strpos($order->get_payment_method(), 'woosa_adyen') === false
      ){
         return;
      }

      ?>
      <div class="form-field form-field-wide">
         <h3><?php _e('Capture Payment', 'woosa-adyen');?></h3>
         <p><?php _e('By pressing this button you will request Adyen to capture the payment for this order.', 'woosa-adyen');?></p>
         <p>
            <button type="button" class="button" data-capture-order-payment="<?php echo $order->get_id();?>"><?php _e('Capture', 'woosa-adyen');?></button>
         </p>
      </div>
      <?php
   }



   /**
    * Displays the section for removin data protection.
    *
    * @since 1.1.0
    * @param \WC_Order $order
    * @return string
    */
   public static function show_remove_data_protection($order){

      $payment_method = $order->get_payment_method();
      $is_removed = $order->get_meta('_' . PREFIX . '_gdpr_removed');

      $allow_removal = get_option(PREFIX .'_allow_remove_gdpr');

      if(strpos($payment_method, 'woosa_adyen') === false || 'yes' !== $allow_removal) return;

      ?>
      <div>
         <h2 class="woocommerce-order-details__title woocommerce-order-details__title--data-protection"><?php _e('General Data Protection Regulation (GDPR)', 'woosa-adyen');?></h2>
         <?php if('yes' === $is_removed):?>
            <p><?php _e('Your personal information has been removed from this order payment.', 'woosa-adyen');?></p>
         <?php else:?>
            <p><?php printf(__('This will erase your personal information attached to this order payment in Adyen system according to %sGDPR%s.', 'woosa-adyen'), '<a href="https://gdpr-info.eu/art-17-gdpr/" target="_blank">', '</a>');?></p>
            <p>
               <button type="button" class="button" data-remove-gdpr="<?php echo $order->get_id();?>"><?php _e('Yes remove', 'woosa-adyen');?></button>
            </p>
         <?php endif;?>
      </div>
      <?php

   }



   /**
    * Adds the prefix to the given reference.
    *
    * @since 1.1.0
    * @param string $reference
    * @return string
    */
   public static function add_reference_prefix($reference){

      $prefix = get_option(PREFIX . '_order_reference_prefix');
      $prefix = empty($prefix) ? '' : "{$prefix}-";

      return $prefix.$reference;

   }



   /**
    * Removes the prefix from the given reference.
    *
    * @since 1.1.0
    * @param string $reference
    * @return string
    */
   public static function remove_reference_prefix($reference){

      $prefix = get_option(PREFIX . '_order_reference_prefix');
      $prefix = empty($prefix) ? '' : "{$prefix}-";

      return str_replace("{$prefix}", '', $reference);

   }

}