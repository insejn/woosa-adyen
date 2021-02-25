<?php
/**
 * Dependency
 *
 * @package Adyen
 * @author Woosa Team
 * @since 1.0.0
 */


namespace Woosa\Adyen;


class Dependency {


   /**
    * PHP required version.
    *
    * @since 1.0.0
    * @var string
    */
   public static $php_version = '7.1';


   /**
    * PHP required extensions.
    *
    * @since 1.0.0
    * @var array
    */
   public static $php_required_extensions = [
      // 'soap' => 'SoapClient',
      // 'ssh2' => 'ssh2',
   ];


   /**
    * Wordpress required version.
    *
    * @since 1.0.0
    * @var string
    */
   public static $wp_version = '5.0';


   /**
    * Wordpress required plugins.
    *
    * @since 1.0.0
    * @var array
    */
   public static $wp_required_plugins = [
      'woocommerce/woocommerce.php' => [
         'name' => 'WooCommerce',
         'version' => '3.5',
      ],
   ];



   /**
    * Checks all dependency types.
    *
    * @since 1.0.0
    * @return void
    */
   public static function checker(){

      self::check_php_version();
      self::check_php_extensions();

      self::check_wp_version();
      self::check_wp_plugins();
   }



   /**
    * Checks PHP version.
    *
    * @since 1.0.0
    * @throws \Exception
    * @return void
    */
   public static function check_php_version(){

      if(version_compare(phpversion(), self::$php_version, '<')){
         throw new \Exception(sprintf(
            __('The server must have at least %s installed.', 'woosa-adyen'),
            '<b>PHP '.self::$php_version.'</b>'
         ));
      }
   }



   /**
    * Checks PHP extensions.
    *
    * @since 1.0.0
    * @throws \Exception
    * @return void
    */
   public static function check_php_extensions(){

      $active = get_loaded_extensions();

      foreach(self::$php_required_extensions as $slug => $name){
         if(!in_array($slug, $active)){
            throw new \Exception(sprintf(
               __('This plugin requires %s extension to be installed on the server.', 'woosa-adyen'),
               "<b>{$name}</b>"
            ));
         }
      }
   }



   /**
    * Checks Wordpress version.
    *
    * @since 1.0.0
    * @return void
    */
   public static function check_wp_version(){

      if(version_compare(get_bloginfo('version'), self::$wp_version, '<')){
         throw new \Exception(sprintf(
            __('This plugin requires at least %s', 'woosa-adyen'),
            '<b>Wordpress '.self::$wp_version.'</b>'
         ));
      }
   }



   /**
    * Checks whether the required WP plugins are installed and active.
    *
    * @since 1.0.0
    * @return void
    */
   public static function check_wp_plugins(){

      $active = self::get_active_wp_plugins();

      foreach(self::$wp_required_plugins as $path => $item){

         $message = sprintf(
            __('This plugin requires at least %s to be installed and active.', 'woosa-adyen'),
            "<b>{$item['name']} {$item['version']}</b>"
         );

         if(in_array($path, $active)){

            if( ! function_exists('get_plugin_data') ){
               require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            $data = get_plugin_data(dirname(PLUGIN_DIR).'/'.$path);

            if(version_compare($data['Version'], $item['version'], '<')){
               throw new \Exception($message);
            }

         }else{

            throw new \Exception($message);
         }

      }
   }



   /**
    * Get active WP plugins
    *
    * @since 1.0.0
    * @return array
    * */
   public static function get_active_wp_plugins(){

      $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

      if (is_multisite()) {
         $active_sitewide_plugins = get_site_option('active_sitewide_plugins');

         foreach ($active_sitewide_plugins as $path => $item) {
            $active_plugins[] = $path;
         }
      }

      return $active_plugins;
   }

}