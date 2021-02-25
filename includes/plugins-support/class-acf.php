<?php
/**
 * ACF
 *
 * This is the class which extends `Advanced Custom Fields` plugin
 *
 * @package Woosa-Adyen/Plugins-support
 * @author Woosa Team
 * @since 1.1.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class ACF{


   /**
    * Displays WP metabox if debug mode is enabled.
    *
    * @since 1.1.0
    * @return void
    */
   public static function maybe_display_wp_metabox(){

      if(DEBUG){
         add_filter('acf/settings/remove_wp_meta_box', '__return_false');
      }
   }
}