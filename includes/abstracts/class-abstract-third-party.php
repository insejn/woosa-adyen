<?php
/**
 * Abstract Third Party
 *
 * This is responsible for adding specific support/adjustments for 3rd party plugins.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Third_Party{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Third_Party::$instance ) {
			Third_Party::$instance = new Third_Party;
		}

		return Third_Party::$instance;
	}



   /**
    * Constructor of this class.
    *
    */
   public function __construct(){
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('admin_init', [$this, 'toggle_ACF_metabox_visibility']);
   }



   /**
    * Displays ACF metabox if our debug mode is enabled.
    *
    * @return void
    */
   public function toggle_ACF_metabox_visibility(){

      if(DEBUG){
         add_filter('acf/settings/remove_wp_meta_box', '__return_false');
      }
   }


}