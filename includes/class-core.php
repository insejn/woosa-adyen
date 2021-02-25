<?php
/**
 * Core class
 *
 * This sets all together.
 *
 * @package Woosa-Adyen
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Core{

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

      require_once PLUGIN_DIR . '/vendor/autoload.php';

      //--- check for dependencies
      try {
         Dependency::checker();
      } catch(\Exception $e){
         Utility::show_notice($e->getMessage(), 'error');
         return;
      }


      //--- general hooks
      self::run_hooks();

      //--- automatic updates
      Auto_Update::instance();

      //--- enqueu scritps/styles
      Assets::enqueue();

      //--- AJAX calls
      AJAX::init();

      //--- handles support for other plugins
      Plugins_Support::instance();


      //------------

      //--- extend WC settings
      Settings::instance();

      //--- extend WC orders
      Order::instance();

      //--- extend WC my account
      My_Account::instance();

      //--- extend WC checkout
      Checkout::instance();

      //--- display caught errors
      Errors::display();
   }



   /**
    * Runs general hooks.
    *
    * @since 1.0.0
    * @return void
    */
   public static function run_hooks(){

      //plugin action links
      add_action('admin_init', [__CLASS__, 'init_plugin_action_links']);

      add_action('upgrader_process_complete', [__CLASS__, 'on_update'], 10, 2);

      add_filter('is_protected_meta', [__CLASS__, 'hide_metadata_entries'], 10, 3);

      //init payments
      add_filter('woocommerce_payment_gateways', [__CLASS__, 'payment_gateway']);

      //register custom routes to WP
      add_action('rest_api_init', [Routes::class, 'register']);

   }



   /**
    * Adds new gateway to WooCommerce payments.
    *
    * @since 1.0.0
    * @param array $gateways
    * @return void
    */
    public static function payment_gateway($gateways) {

      $gateways[] = Ideal::class;
      $gateways[] = Sepa_Direct_Debit::class;
      $gateways[] = Credit_Card::class;
      $gateways[] = Giropay::class;
      $gateways[] = Sofort::class;
      $gateways[] = Bancontact::class;
      $gateways[] = Boleto::class;
      $gateways[] = Alipay::class;
      $gateways[] = Wechatpay::class;
      $gateways[] = Googlepay::class;
      // $gateways[] = Applepay::class;
      $gateways[] = Klarna::class;
      $gateways[] = Klarna_PayNow::class;
      $gateways[] = Klarna_Account::class;
      $gateways[] = Paypal::class;

      return $gateways;
   }



   /**
    * Checks whether or not a given country code is valid (exists in the Woo countries list).
    *
    * @since 1.0.0
    * @param string $code
    * @return boolean
    */
   public static function is_valid_country_code($code){

      $countries = (new \WC_Countries)->get_countries();

      if(array_key_exists(strtoupper($code), $countries)) return true;

      return false;
   }



   /**
    * Hides our metadata entries but shows them if debug mode is enabled
    *
    * @since 1.0.0
    * @param bool $protected
    * @param string $meta_key
    * @param string $meta_type
    * @return bool
    */
   public static function hide_metadata_entries($protected, $meta_key, $meta_type){

      if(strpos($meta_key, PREFIX.'_') !== false && DEBUG === false){
         $protected = true;
      }

      if(strpos($meta_key, '_' . PREFIX.'_') !== false && DEBUG === true){
         $protected = false;
      }

      return $protected;
   }



   /**
    * Displays plugin action links in plugins list page.
    *
    * @since 1.0.0
    * @return void
    */
   public static function init_plugin_action_links(){

      //add plugin action and meta links
      self::set_plugin_links(array(
         'actions' => array(
            SETTINGS_URL => __('Settings', 'woosa-adyen'),
            admin_url('admin.php?page=wc-settings&tab=checkout') => __('Payments', 'woosa-adyen'),
            LOGS_URL => __('Logs', 'woosa-adyen'),
            CHECK_FOR_UPDATE_URL => __('Check for Updates', 'woosa-adyen')
         ),
         'meta' => array(
            // '#1' => __('Docs', 'woosa-adyen'),
            // '#2' => __('Visit website', 'woosa-adyen')
         ),
      ));
   }



   /**
    * Sets plugin action and meta links.
    *
    * @since 1.0.0
    * @param array $sections
    * @return void
    */
   public static function set_plugin_links($sections = array()) {

      //actions
      if(isset($sections['actions'])){

         $actions = $sections['actions'];
         $links_hook = is_multisite() ? 'network_admin_plugin_action_links_' : 'plugin_action_links_';

         add_filter($links_hook.PLUGIN_BASENAME, function($links) use ($actions){

            foreach(array_reverse($actions) as $url => $label){
               $link = '<a href="'.$url.'">'.$label.'</a>';
               array_unshift($links, $link);
            }

            return $links;

         });
      }

      //meta row
      if(isset($sections['meta'])){

         $meta = $sections['meta'];

         add_filter( 'plugin_row_meta', function($links, $file) use ($meta){

            if(PLUGIN_BASENAME == $file){

               foreach($meta as $url => $label){
                  $link = '<a href="'.$url.'">'.$label.'</a>';
                  array_push($links, $link);
               }
            }

            return $links;

         }, 10, 2 );
      }

   }



   /**
    * Runs on plugin activation.
    *
    * @since 1.0.0
    * @return void
    */
   public static function on_activation(){

      //check for dependencies
      try{
         Dependency::checker();
      } catch(\Exception $e){
         $msg = $e->getMessage();
         $msg = $msg . ' ' . sprintf(
               __('%sGo back%s', 'woosa-adyen'),
               '<a href="' . admin_url('plugins.php') . '">',
               '</a>'
            );
         wp_die($msg);
      }
   }



   /**
    * Runs on plugin deactivation.
    *
    * @since 1.0.0
    * @return void
    */
   public static function on_deactivation(){

   }



   /**
    * Run on plugin update process
    *
    * @since 1.0.0
    * @param object $upgrader_object
    * @param array $options
    * @return void
    */
   public static function on_update( $upgrader_object, $options ) {

      if($options['action'] == 'update' && $options['type'] == 'plugin' ){

         foreach($options['plugins'] as $plugin){

            if($plugin == PLUGIN_BASENAME){
               //do stuff here
            }
         }
      }
   }



   /**
    * Runs when plugin is deleting.
    *
    * @since 1.0.5 - remove data
    * @since 1.0.0
    * @return void
    */
   public static function on_uninstall(){


      if('yes' === get_option(PREFIX .'_remove_config')){

         //license
         delete_option(PREFIX.'_license_key');
         delete_option(PREFIX.'_license_email');
         delete_option(PREFIX.'_plugin_update');
         delete_option(PREFIX.'_license_error');
         delete_option(PREFIX.'_license_active');

         //API
         delete_option(PREFIX.'_testmode');
         delete_option(PREFIX.'_test_api_key');
         delete_option(PREFIX.'_api_key');
         delete_option(PREFIX.'_test_merchant_account');
         delete_option(PREFIX.'_merchant_account');
         delete_option(PREFIX.'_url_prefix');
         delete_option(PREFIX.'_is_authorized');
         delete_option(PREFIX.'_origin_keys');
         delete_option(PREFIX.'_api_username');
         delete_option(PREFIX.'_api_password');
         delete_option(PREFIX.'_errors');

         //settings
         delete_option(PREFIX.'_capture_payment');
         delete_option(PREFIX.'_remove_config');

         //payments
         foreach(self::payment_gateway([]) as $method){
            $method_id = str_replace('\\', '_', strtolower($method));
            delete_option("woocommerce_{$method_id}_settings");
         }

      }
   }


}
Core::instance();