<?php
/**
 * Abstract Dependency
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


abstract class Abstract_Dependency {


   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * PHP required version.
    *
    * @var string
    */
   public $php_version = '7.1';


   /**
    * PHP required extensions.
    *
    * @var array
    */
   public $php_required_extensions = [
      // 'soap' => 'SoapClient',
      // 'ssh2' => 'ssh2',
   ];


   /**
    * Wordpress required version.
    *
    * @var string
    */
   public $wp_version = '5.0';


   /**
    * Wordpress required plugins.
    *
    * @var array
    */
   public $wp_required_plugins = [
      'woocommerce/woocommerce.php' => [
         'name' => 'WooCommerce',
         'version' => '3.5',
      ],
   ];



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Dependency::$instance ) {
			Dependency::$instance = new Dependency;
		}

		return Dependency::$instance;
	}



   /**
    * Checks all dependency types.
    *
    * @return void
    */
   public function checker(){

      $this->check_php_version();
      $this->check_php_extensions();

      $this->check_wp_version();
      $this->check_wp_plugins();
   }



   /**
    * Checks PHP version.
    *
    * @throws \Exception
    * @return void
    */
   public function check_php_version(){

      if(version_compare(phpversion(), $this->php_version, '<')){
         throw new \Exception(sprintf(
            __('The server must have at least %s installed.', 'woosa-adyen'),
            '<b>PHP '.$this->php_version.'</b>'
         ));
      }
   }



   /**
    * Checks PHP extensions.
    *
    * @throws \Exception
    * @return void
    */
   public function check_php_extensions(){

      $active = get_loaded_extensions();

      foreach($this->php_required_extensions as $slug => $name){
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
    * @return void
    */
   public function check_wp_version(){

      if(version_compare(get_bloginfo('version'), $this->wp_version, '<')){
         throw new \Exception(sprintf(
            __('This plugin requires at least %s', 'woosa-adyen'),
            '<b>Wordpress '.$this->wp_version.'</b>'
         ));
      }
   }



   /**
    * Checks whether the required WP plugins are installed and active.
    *
    * @return void
    */
   public function check_wp_plugins(){

      $active = $this->get_active_wp_plugins();

      foreach($this->wp_required_plugins as $path => $item){

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
    * @return array
    * */
   public function get_active_wp_plugins(){

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