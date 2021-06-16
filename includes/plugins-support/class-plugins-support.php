<?php
/**
 * Plugins Support
 *
 * @package Woosa-Adyen/WooCommerce
 * @author Woosa Team
 * @since 1.1.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Plugins_Support{

   /**
    * The instance of this class.
    *
    * @since 1.1.0
    * @var null|object
    */
   protected static $instance = null;


	/**
	 * Returns an instance of this class.
	 *
	 * @since 1.1.0
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
    * @since 1.1.0
    */
   public function __construct(){

      add_action('admin_init', [ACF::class, 'maybe_display_wp_metabox']);
   }


}