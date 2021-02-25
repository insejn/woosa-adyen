<?php
/**
 * Automatic Updates
 *
 * This is responsible for automatic plugin updates via API Manager WooCommerce extension.
 *
 * @package Woosa-Adyen
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Auto_Update{


   /**
   * Midlayer proxy URL.
   *
   * @since 1.0.9
   * @var string
   */
   public static $proxy_url = 'https://midlayer.woosa.nl/plugin-manager-proxy';


   /**
    * Site URL where the updates will be taken from.
    *
    * @since 1.0.0
    * @var string
    */
   public static $webshop_url = 'https://www.woosa.nl';


   /**
    * Image of the changelog popup.
    *
    * @since 1.0.0
    * @var string
    */
   public static $changelog_image = PLUGIN_URL . '/assets/images/plugin-cover.jpg';


   /**
    * Update information.
    *
    * @since    1.0.0
    * @var object
    */
   public static $update;


   /**
    * API licence key.
    *
    * @since 1.0.0
    * @var string
    */
   public static $key;


   /**
    * Activation email.
    *
    * @since 1.0.0
    * @var string
    */
   public static $email;


   /**
    * Whether or not the license is active.
    *
    * @since 1.0.0
    * @var string
    */
   public static $is_active;


   /**
    * The instance of this class.
    *
    * @since 1.0.9
    * @var null|object
    */
   protected static $instance = null;



   /**
    * Returns an instance of this class.
    *
    * @since 1.0.9
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

      if( defined('\WOOSA_TEST_LIC') && \WOOSA_TEST_LIC ){
         self::$proxy_url = 'https://midlayer-dev.woosa.nl/plugin-manager-proxy';
         self::$webshop_url = 'https://staging.woosa.nl';
      }

      self::$key       = get_option(PREFIX.'_license_key');
      self::$email     = get_option(PREFIX.'_license_email');
      self::$is_active = get_option(PREFIX.'_license_active');
      self::$update    = get_option(PREFIX.'_plugin_update', new \stdClass());

      add_action('admin_init', __CLASS__ . '::run_on_admin_init');

      add_action('install_plugins_pre_plugin-information', __CLASS__ . '::display_changelog', 9);
      add_filter('transient_update_plugins', __CLASS__ . '::update_notification');
      add_filter('site_transient_update_plugins', __CLASS__ . '::update_notification');

   }



   /**
    * Runs on admin init
    *
    * @since 1.0.0
    * @return void
    */
   public static function run_on_admin_init(){

      $error = get_option(PREFIX.'_license_error');

      //show error notice
      if($error){
         Utility::show_notice($error, 'error');
      }

      //check manually for updates
      if(is_admin() && Utility::rgar($_GET, 'wsa_action') == PREFIX.'_check_updates'){

         if(self::$is_active === false){
            self::process_license(self::$key, self::$email);
         }

         $check = self::check_for_updates();

         //if no updates available redirect to show the notice
         if( $check === true ){

            $do_update = version_compare( self::$update->new_version, PLUGIN_VERSION );

            if($do_update == 0 || $do_update == -1){
               wp_redirect(add_query_arg(array('wsa_action' => PREFIX.'_no_updates'), $_SERVER['REQUEST_URI']));
               exit;
            }
         }

         //refresh the page but remove `wsa_action`
         wp_redirect(remove_query_arg('wsa_action', $_SERVER['REQUEST_URI']));
         exit;
      }

      //check periodically for updates
      if(get_transient(PREFIX.'_plugin_checked') === false){
         if(self::$is_active === false){
            self::process_license(self::$key, self::$email);
         }
         self::check_for_updates();
      }

      //notice message if no updates found
      if(is_admin() && Utility::rgar($_GET, 'wsa_action') == PREFIX.'_no_updates'){
         Utility::show_notice(__('This plugin is already up to date, no new updates were found.', 'woosa-adyen'), 'success');
      }

   }



   /**
    * Checks whether or not the automatic updates is active
    *
    * @since 1.0.5
    * @return boolean
    */
   public static function is_active(){

      if(self::$is_active) return true;

      return false;
   }



   /**
    * Gets the license status.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return string|object
    */
   public static function get_license_status($key, $email){

      $remote = wp_remote_get(self::$proxy_url, array(
         'timeout' => 120,
         'headers' => array(
            'x-woosa-target-url' => self::$webshop_url
         ),
         'body' => array(
            'wc-api'      => 'am-software-api',
            'request'     => 'status',
            'email'       => $email,
            'licence_key' => $key,
            'product_id'  => PRODUCT_ID,
            'platform'    => $_SERVER['SERVER_NAME'],
            'instance'    => PLUGIN_INSTANCE,
         )
      ));

      if(is_wp_error($remote)){

         Utility::wc_error_log($remote, __FILE__, __LINE__);

         return (object) array(
            'error' => $remote->get_error_message()
         );
      }

      $data = json_decode($remote['body']);

      if(isset($data->error)){
         Utility::wc_error_log($data, __FILE__, __LINE__);
         return $data;
      }


      return $data->status_check;
   }



   /**
    * Activates the license.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return string|object
    */
   public static function activate_license($key, $email){

      $remote = wp_remote_get(self::$proxy_url, array(
         'timeout' => 120,
         'headers' => array(
            'x-woosa-target-url' => self::$webshop_url
         ),
         'body' => array(
            'wc-api'           => 'am-software-api',
            'request'          => 'activation',
            'email'            => $email,
            'licence_key'      => $key,
            'product_id'       => PRODUCT_ID,
            'platform'         => $_SERVER['SERVER_NAME'],
            'instance'         => PLUGIN_INSTANCE,
            'software_version' => PLUGIN_VERSION,
         )
      ));

      if(is_wp_error($remote)){

         Utility::wc_error_log($remote, __FILE__, __LINE__);

         return (object) array(
            'error' => $remote->get_error_message()
         );

      }

      $data = json_decode($remote['body']);

      if(isset($data->error)){
         Utility::wc_error_log($data, __FILE__, __LINE__);
         return (object) array('error' => $data->error);

      }


      return $data;
   }



   /**
    * Deactivates the license.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return string|object
    */
   public static function deactivate_license($key, $email){

      $remote = wp_remote_get(self::$proxy_url, array(
         'timeout' => 120,
         'headers' => array(
            'x-woosa-target-url' => self::$webshop_url
         ),
         'body' => array(
            'wc-api'           => 'am-software-api',
            'request'          => 'deactivation',
            'email'            => $email,
            'licence_key'      => $key,
            'product_id'       => PRODUCT_ID,
            'platform'         => $_SERVER['SERVER_NAME'],
            'instance'         => PLUGIN_INSTANCE,
            'software_version' => PLUGIN_VERSION,
         )
      ));

      if(is_wp_error($remote)){

         Utility::wc_error_log($remote, __FILE__, __LINE__);

         return (object) array(
            'error' => $remote->get_error_message()
         );

      }

      $data = json_decode($remote['body']);

      if(isset($data->error)){
         Utility::wc_error_log($data, __FILE__, __LINE__);
         return (object) array('error' => $data->error);

      }

      return $data;
   }



   /**
    * Gets plugin information.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return mixed
    */
   public static function get_information($key, $email){

      $remote = wp_remote_get(self::$proxy_url, array(
         'timeout' => 120,
         'headers' => array(
            'x-woosa-target-url' => self::$webshop_url
         ),
         'body' => array(
            'wc-api' => 'upgrade-api',
            'request' => 'plugininformation',
            'plugin_name' => PLUGIN_BASENAME,
            'product_id' => PRODUCT_ID,
            'api_key' => $key,
            'activation_email' => $email,
            'instance' => PLUGIN_INSTANCE,
            'domain' => $_SERVER['SERVER_NAME'],
         )
      ));

      if(is_wp_error($remote)){

         Utility::wc_error_log($remote, __FILE__, __LINE__);

         return (object) array(
            'error' => $remote->get_error_message()
         );

      }

      $data = maybe_unserialize($remote['body']);

      if(is_object($data)){

         if(isset($data->errors)){
            Utility::wc_error_log($data, __FILE__, __LINE__);
            return (object) array('error' => $data->errors);
         }

      }else{

         $data = json_decode($remote['body']);

         if(isset($data->error)){
            Utility::wc_error_log($data, __FILE__, __LINE__);
            return (object) array('error' => $data->error);
         }
      }

      return $data;
   }



   /**
    * Gets plugin update.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return object
    */
   public static function get_update($key, $email){

      $remote = wp_remote_get(self::$proxy_url, array(
         'timeout' => 120,
         'headers' => array(
            'x-woosa-target-url' => self::$webshop_url
         ),
         'body' => array(
            'wc-api' => 'upgrade-api',
            'request' => 'pluginupdatecheck',
            'plugin_name' => PLUGIN_BASENAME,
            'product_id' => PRODUCT_ID,
            'api_key' => $key,
            'activation_email' => $email,
            'instance' => PLUGIN_INSTANCE,
            'domain' => $_SERVER['SERVER_NAME'],
         )
      ));

      if(is_wp_error($remote)){

         Utility::wc_error_log($remote, __FILE__, __LINE__);

         return (object) array(
            'error' => $remote->get_error_message()
         );

      }

      $data = maybe_unserialize($remote['body']);

      if(is_object($data)){

         if(isset($data->errors)){
            Utility::wc_error_log($data, __FILE__, __LINE__);
            return (object) array('error' => $data->errors);
         }

      }else{

         $data = json_decode($remote['body']);

         if(isset($data->error)){
            Utility::wc_error_log($data, __FILE__, __LINE__);
            return (object) array('error' => $data->error);
         }
      }

      return $data;


   }



   /**
    * Displays available update notification.
    *
    * @since 1.0.0
    * @return object
    */
   public static function update_notification($update_plugins){

      if(!isset(self::$update->new_version)) return $update_plugins;

      // Check the versions if we need to do an update
      $do_update = version_compare( self::$update->new_version, PLUGIN_VERSION );

      if($do_update == 1 ){
         $update_plugins->response[PLUGIN_BASENAME] = (object) array(
            'slug'         => PLUGIN_FOLDER,
            'url'          => self::$proxy_url,
            'new_version'  => self::$update->new_version,
            'package'      => self::$is_active ? self::$update->package : '',
         );
      }

      return $update_plugins;
   }



   /**
    * Checks for available updates.
    *
    * @since 1.0.0
    * @return bool
    */
   public static function check_for_updates(){

      //reset interval
      set_transient(PREFIX.'_plugin_checked', 'true', 60*60*48);

      $no_license_msg = sprintf(
         __('Automatic updates & support is not active, please activate your license key. %sGo to settings%s', 'woosa-adyen'),
         '<a href="'.SETTINGS_URL.'">',
         '</a>'
      );


      if(empty(self::$key) && empty(self::$email)){
         update_option(PREFIX.'_license_error', $no_license_msg);
         delete_option(PREFIX.'_license_active');

         return false;
      }


      $status  = self::get_license_status(self::$key, self::$email);

      if($status == 'active'){

         $info = self::get_information(self::$key, self::$email);
         $update = self::get_update(self::$key, self::$email);

         if(isset($info->error)){
            update_option(PREFIX.'_license_error', __('An error occurred while getting the plugin information', 'woosa-adyen'));
         }else{

            $update->changelog = $info->sections['changelog'];
            self::$update = $update;

            update_option(PREFIX.'_plugin_update', $update);
            delete_option(PREFIX.'_license_error');
         }

         return true;

      }elseif($status == 'inactive'){

         delete_option(PREFIX.'_license_active');

         update_option(PREFIX.'_license_error', $no_license_msg);

      }elseif(isset($status->error)){

         delete_option(PREFIX.'_license_active');

         update_option(PREFIX.'_license_error', $status->error);

      }


      return false;

   }



   /**
    * Shows automatic update status.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @return string
    */
   public static function api_status(){

      if(self::$is_active == 'yes'){
         return sprintf(__('%sStatus:%s %sActive%s', 'woosa-adyen'), '<b>', '</b>', '<span style="color: green;">', '</span>');
      }

      return sprintf(
         __('%sStatus:%s %sInactive%s', 'woosa-adyen'),
         '<b>',
         '</b>',
         '<span style="color: #cc0000;">',
         '</span>'
      );
   }




   /**
    * Checks license status and activate it if it's inactive.
    *
    * @since 1.0.0
    * @param string $key
    * @param string $email
    * @param bool $reload
    * @return void
    */
   public static function process_license($key, $email, $reload = false){

      $status = self::get_license_status($key, $email);

      if($status == 'inactive'){

         $active = self::activate_license($key, $email);

         if(isset($active->error)){

            delete_option(PREFIX.'_license_active');
            update_option(PREFIX.'_license_error', $active->error);

         }else{

            update_option(PREFIX.'_license_active', 'yes');
            delete_option(PREFIX.'_license_error');
         }

      }elseif($status == 'active'){

         update_option(PREFIX.'_license_active', 'yes');
         delete_option(PREFIX.'_license_error');

         delete_transient(PREFIX.'_plugin_checked');

      }elseif(isset($status->error)){

         update_option(PREFIX.'_license_error', $status->error);
         delete_option(PREFIX.'_license_active');
      }

      if($reload) wp_redirect($_SERVER['REQUEST_URI']);

   }



   /**
    * Displays plugin changelog.
    *
    * @since 1.0.0
    */
   public static function display_changelog(){

      if ( $_REQUEST['plugin'] != PLUGIN_FOLDER ) {
         return;
      }

      if(isset(self::$update->changelog)){
         ?>
         <style>
            body{
               margin: 0;
               color: #4e4e4e;
               font-size: 14px;
               line-height: 1.6em;
               font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
            }
            .plugin-information__cover{
               background-image: url("<?php echo self::$changelog_image;?>");
               background-size: cover;
               background-position: center center;
               height: 250px;
            }
            .plugin-information__cover h2{
               position: relative;
               font-family: "Helvetica Neue",sans-serif;
               display: inline-block;
               font-size: 30px;
               line-height: 50px;
               box-sizing: border-box;
               max-width: 100%;
               padding: 0 15px;
               margin: 174px 0 0 25px;
               color: #fff;
               background: rgba(30,30,30,.9);
               text-shadow: 0 1px 3px rgba(0,0,0,.4);
               box-shadow: 0 0 30px rgba(255,255,255,.1);
               border-radius: 8px;
            }
            .plugin-content{
               padding: 25px;
            }
            .plugin-content ul{
               margin: 0;
               padding: 0 0 0 15px;
            }
               .plugin-content ul li{
                  margin-bottom: 10px;
                  list-style: none;
               }
            .plugin-content .changelog {
               padding: 5px 8px;
               border-radius: 4px;
               font-size: 12px;
               text-transform: uppercase;
               letter-spacing: 0.2px;
               font-weight: 600;
               color: white;
            }
            .plugin-content .changelog.tweak {
               background: #6aa84f;
            }
            .plugin-content .changelog.feature {
               background: #3c78d8;
            }
            .plugin-content .changelog.fix {
               background: #cc0000;
            }
         </style>
         <div class="plugin-information">
            <div class="plugin-information__cover">
               <h2><?php echo PLUGIN_NAME;?></h2>
            </div>
            <div class="plugin-content">
               <?php echo self::$update->changelog;?>
            </div>
         </div>
         <?php
      }

      exit;
   }

}