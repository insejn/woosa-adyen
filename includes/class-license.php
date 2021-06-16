<?php
/**
 * License
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class License {

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * Package
    *
    * @var string
    */
   private $package;


   /**
    * Domain
    *
    * @var string
    */
   private $domain;


   /**
    * Update data
    *
    * @var object
    */
   public $update;


   /**
    * Licence key
    *
    * @var string
    */
   public $key;


   /**
    * Licence information
    *
    * @var object
    */
   public $info;


   /**
    * Whether or not the license is active
    *
    * @var string
    */
   public $is_active;


   /**
    * Image of the changelog popup
    *
    * @var string
    */
   public $changelog_image = PLUGIN_URL . '/assets/images/plugin-cover.jpg';


	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == License::$instance ) {
			License::$instance = new License;
		}

		return License::$instance;
   }



   /**
    * Construct of this class.
    *
    */
   public function __construct($key = '', $domain = '', $package = ''){

      $this->key     = $key === '' ? get_option(PREFIX . '_license_key', '') : $key;
      $this->domain  = $domain === '' ? get_bloginfo('wpurl') : $domain;
      $this->package = $package === '' ? PLUGIN_FOLDER : $package;

   }



   /**
    * Initiates hooks
    *
    * @return void
    */
   public function init_hooks() {

      add_action('woocommerce_get_sections_' . SETTINGS_TAB_ID, [$this, 'add_section'], 95);
      add_action('woocommerce_get_settings_' . SETTINGS_TAB_ID, [$this, 'add_section_fields'], 95, 2);
      add_action('woocommerce_admin_field_' . PREFIX .'_license_submission', [$this, 'output_section']);

      add_filter(PREFIX . '\assets\files', [$this, 'enqueue_assets']);

      add_filter('transient_update_plugins', [$this, 'show_update_notification']);
      add_filter('site_transient_update_plugins', [$this, 'show_update_notification']);

      add_action('wp_ajax_'.PREFIX.'_license_submission', [$this, 'process_license_submission']);

      add_action('rest_api_init', [ $this, 'register_api_routes' ]);

      add_filter('upgrader_pre_download', [$this, 'before_download_package'], 10, 3);

      add_action('install_plugins_pre_plugin-information', [$this, 'display_changelog'] , 9);

   }




   /*
   |--------------------------------------------------------------------------
   | SETTINGS SECTION
   |--------------------------------------------------------------------------
   */


   /**
    * Adds the section in setting sections.
    *
    * @param array $sections
    * @return array
    */
   public function add_section($sections){

      $sections['license'] = __('License', '{text_domain}');

      return $sections;
   }



   /**
    * Adds the section fields.
    *
    * @param array $fields
    * @param string $section
    * @return array
    */
   public function add_section_fields($fields, $section){

      if( 'license' === $section ){
         $fields = [
            [
               'name' => __('Automatic updates & support', '{text_domain}'),
               'type' => 'title',
            ],
            [
               'name' => __( 'Key', '{text_domain}' ),
               'type' => PREFIX . '_license_submission',
               'id'   => PREFIX . '_license_key',
               'option_key'  => PREFIX .'_license_key',
               'autoload' => false,
            ],
            [
               'type' => 'sectionend',
            ],
         ];
      }

      return $fields;
   }



   /**
    * Renders section content.
    *
    * @param array $values
    * @return string
    */
   public function output_section($values){

      $GLOBALS['hide_save_button'] = true;

      if ( $this->is_active() ) {
         $status = '<span style="color: green;">'.__('Active', '{text_domain}').'</span>';
         $btn_label = __('Deactivate', '{text_domain}');
         $btn_action = 'deactivate';
      } else {
         $status = '<span style="color: #a30000;">'.__('Inactive', '{text_domain}').'</span>';
         $btn_label = __('Activate', '{text_domain}');
         $btn_action = 'activate';
      }

      $info = $this->get_info();

      $activations = isset($info->license) ? $info->license->activations : '';
      $activation_limit = isset($info->license) ? $info->license->activation_limit : '';
      $activation_limit = $activation_limit < 1 ? '&infin;' : $activation_limit;
      $activaion_stats = '' === $activations || '' === $activation_limit ? '-' : $activations.'/'.$activation_limit;

      ?>
         <tr valign="top">
            <td style="padding: 0px;">
               <p><?php printf('Status: %s', $status);?></p>
               <p><?php printf('Activations: %s', $activaion_stats);?></p>
               <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 10px;">
                  <input type="text" id="<?php echo $values['id'];?>" name="<?php echo $values['id'];?>" value="<?php echo $this->key;?>" placeholder="<?php _e('License Key', '{text_domain}');?>" autocomplete="off">
                  <button type="button" class="button button-primary" data-<?php echo PREFIX;?>-license="<?php echo $btn_action;?>"><?php echo $btn_label;?></button>

                  <?php if($this->is_active()):?>
                     <button type="button" class="button button-secondary" data-<?php echo PREFIX;?>-license="get_update"><?php _e('Check for update', '{text_domain}');?></button>
                  <?php endif;?>
               </div>
            </td>
         </tr>
      <?php
   }




   /*
   |--------------------------------------------------------------------------
   | CONDITIONALS
   |--------------------------------------------------------------------------
   */


   /**
    * Checks whether or not the automatic updates is active
    *
    * @return boolean $active
    */
   public function is_active(){

      $active = false;

      if ( 'active' === get_option( PREFIX . '_license_status', false ) ) {
         $active = true;
      }

      return $active;
   }



   /**
    * Checks if an update is available.
    *
    * @return boolean $response
    */
   protected function is_update_available() {

      $result = false;

      if ( $this->is_active() ) {

         $update = $this->get_update();

         if ( isset($update->version) && version_compare( $update->version, PLUGIN_VERSION, ">" ) ) {
            $result = true;
         }
      }

      return $result;
   }




   /*
   |--------------------------------------------------------------------------
   | GETTERS
   |--------------------------------------------------------------------------
   */


   /**
    * Retreives the server URL of the license supplier.
    *
    * @return string
    */
   public function get_supplier_url(){

      $result = '';

      if ( ! empty( $this->key ) && ( strlen( $this->key ) % 2 == 0 ) && ctype_xdigit( $this->key ) ) {

         $iv = 'ab86d144ab86d144';
         $cipher = "aes-128-ctr";
         $license_data = openssl_decrypt( hex2bin( $this->key ), $cipher, null, $options=OPENSSL_RAW_DATA, $iv);
         $license_data = explode( '*', $license_data );

         if ( isset( $license_data[1] ) ) {
            $result = $license_data[0];
         }
      }

      return $result;
   }



   /**
    * Gets the full API url for a given endpoint.
    *
    * @param string $endpoint
    * @return string
    */
   public function get_api_url($endpoint){

      $result = '';
      $base_url = $this->get_supplier_url();

      if($base_url){
         $result = "https://{$base_url}/wp-json/lmn/v1/".ltrim($endpoint, '/');
      }

      return $result;
   }




   /*
   |--------------------------------------------------------------------------
   | SETTERS
   |--------------------------------------------------------------------------
   */


   /**
    * Sets the status as active.
    *
    * @param boolean $deactivate
    * @return void
    */
   protected function set_active() {
      update_option(PREFIX . '_license_status', 'active', false);
   }



   /**
    * Sets the status as inactive.
    *
    * @param boolean $deactivate
    * @return void
    */
   protected function set_inactive() {

      delete_option(PREFIX . '_license_status');

      $this->cache_info('');
      $this->cache_update('');
   }



   /**
    * Saves locally the update received from the supplier.
    *
    * @param mixed $data
    * @return void
    */
   protected function cache_update($data) {

      if(empty($data)){
         delete_option(PREFIX . '_plugin_update');
      }else{
         update_option(PREFIX . '_plugin_update', json_decode(json_encode($data)), false);
      }
   }



   /**
    * Saves locally the license info.
    *
    * @param mixed $data
    * @return void
    */
   protected function cache_info($data){

      if(empty($data)){
         delete_option(PREFIX . '_license_info');
      }else{
         update_option(PREFIX . '_license_info', json_decode(json_encode($data)), false);
      }
   }




   /*
   |--------------------------------------------------------------------------
   | LICENSE API
   |--------------------------------------------------------------------------
   */


   /**
    * Activate the license.
    *
    * @return object
    */
   public function activate() {

      $request = $this->send_request('activate', [
         'method' => 'POST',
         'body' => [
            'domain' => $this->domain,
            'key' => $this->key,
            'package' => $this->package,
         ]
      ]);

      $result = $request->data;

      if($request->status == 200){
         $this->cache_info($result);
      }

      return $result;
   }



   /**
    * Deactivate the license.
    *
    * @return object
    */
   public function deactivate() {

      $request = $this->send_request('deactivate', [
         'method' => 'POST',
         'body' => [
            'domain' => $this->domain,
            'key' => $this->key,
            'package' => $this->package,
         ]
      ]);

      $result = $request->data;

      if($request->status == 200){
         $this->cache_info($result);
      }

      return $result;
   }



   /**
    * Get the license information.
    *
    * @param bool $no_cache
    * @return object
    */
   public function get_info($no_cache = false){

      $result = get_option(PREFIX . '_license_info');

      if(empty($result) || $no_cache){

         $request = $this->send_request('get_info', [
            'body' => [
               'key' => $this->key,
               'domain' => $this->domain,
               'package' => $this->package,
            ],
         ]);
         $result = $request->data;

         $this->cache_info($result);
      }

      return $result;
   }



   /**
    * Get update the license
    *
    * @param bool $no_cache
    * @return object
    */
   public function get_update($no_cache = false){

      $result = get_option(PREFIX . '_plugin_update');

      if( empty($result) || $no_cache ){

         $request = $this->send_request('get_update', [
            'body' => [
               'key' => $this->key,
               'domain' => $this->domain,
               'package' => $this->package,
            ],
         ]);
         $result = $request->data;

         $this->cache_update($result);
      }

      return $result;
   }




   /*
   |--------------------------------------------------------------------------
   | UPDATE API
   |--------------------------------------------------------------------------
   */


   /**
    * Registers the update endpoint.
    *
    * @return void
    */
   public function register_api_routes() {

      register_rest_route(
         PLUGIN_FOLDER . '/v1',
         '/software/update',
         [
            'methods' => 'POST',
            'callback' => [ $this, 'process_update_software' ],
            'permission_callback' => '__return_true',
         ]
      );

      register_rest_route(
         PLUGIN_FOLDER . '/v1',
         '/license/deactivate',
         [
            'methods' => 'POST',
            'callback' => [ $this, 'process_deactivate_license' ],
            'permission_callback' => '__return_true',
         ]
      );
   }



   /**
    * Processes the received request to update the software.
    *
    * @return void
    */
   public function process_update_software( $request ) {

      if($request->has_param('version') && $request->has_param('url') && $request->has_param('file')){

         $this->cache_update( $request->get_params() );

         $response = new \WP_REST_Response( [], 204 );

         if(DEBUG){
            Utility::wc_debug_log([
               'method' => $request->get_method(),
               'body' => $request->get_params(),
               'headers' => $request->get_headers(),
            ], __FILE__, __LINE__ );
         }

      }else{

         $response = new \WP_Error( 'invalid_request_params', 'Bad request.', [ 'status' => 400 ] );

      }

      return $response;
   }



   /**
    * Processes the received request to deactivate the license.
    *
    * @return void
    */
   public function process_deactivate_license($request){

      if($request->has_param('domain')){

         $this->set_inactive();

         $response = new \WP_REST_Response( [], 204 );

         if(DEBUG){
            Utility::wc_debug_log([
               'method' => $request->get_method(),
               'body' => $request->get_params(),
               'headers' => $request->get_headers(),
            ], __FILE__, __LINE__ );
         }

      }else{

         $response = new \WP_Error( 'invalid_request_params', 'Bad request.', [ 'status' => 400 ] );

      }

      return $response;
   }



   /**
    * Shows update notification.
    *
    * @param array $update_plugins
    * @return array
    */
   public function show_update_notification( $update_plugins ) {

      if ( is_object( $update_plugins ) ) {

         if ( $this->is_update_available() ) {

            $update = $this->get_update();

            if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) ) {
               $update_plugins->response = array();
            }
            $update_plugins->response[PLUGIN_BASENAME] = (object) array(
               'slug'         => PLUGIN_FOLDER,
               'url'          => $update->url,
               'new_version'  => $update->version,
               'package'      => $update->file,
            );

         }

      }

      return $update_plugins;
   }



   /**
    * Performs a check to get package path.
    *
    * @param boolean $reply
    * @param array $package
    * @param object $upgrader
    * @return string
    */
   public function before_download_package( $reply, $package, $upgrader ) {

      if ( $package == PLUGIN_FOLDER . '.zip' ) {

         $upgrader->skin->feedback( 'downloading_package', $package );

         $request = $this->get_package_download_path();

         if( isset($request->host) && isset($request->hash) ) {

            $reply = $this->download_package( $request->host, $request->hash );

         } else {

            $reply = new \WP_Error( 'rest_invalid_package', __('Invalid package path.', 'woosa-adyen') );
         }
      }

      return $reply;

   }



   /**
    * Get the secret download path.
    *
    * @return void
    */
   protected function get_package_download_path() {

      $request = $this->send_request('prepare_update', [
         'timeout' => 120,
         'method' => 'POST',
         'body' => [
            'domain' => $this->domain,
            'key' => $this->key,
            'package' => $this->package,
         ]
      ]);

      $result = $request->data;

      return $result;

   }



   /**
    * Download the remote file locally in the upgrade process.
    *
    * @param string $package_host
    * @param string $package_hash
    * @return string
    */
   protected function download_package( $host, $hash ) {

      $result = false;
      $file_name = PLUGIN_FOLDER . '.zip';
      $tmpfname = wp_tempnam( $file_name );

      if ( ! $tmpfname ) {
         $result = new \WP_Error( 'http_no_file', __( 'Could not create Temporary file.', 'woosa-adyen' ) );
      }

      $request = $this->send_request('download_update', [
         'timeout' => 30,
         'stream'   => true,
         'filename' => $tmpfname,
         'body' => [
            'hash' => $hash,
            'key'  => $this->key
         ]
      ]);

      if($request->status == 200){

         if ( filesize( $tmpfname ) > 0 ) {
            $result = $tmpfname;
         } else {
            $content = wp_remote_retrieve_body( $response );
            $fs = new \WP_Filesystem_Direct(false);
            $fs->put_contents( $tmpfname, $content );
            $result = $tmpfname;
         }

      }else{

         $result = new \WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
      }

      return $result;

   }



   /**
    * Display plugin changelog
    *
    */
   public function display_changelog(){

      if ( $_REQUEST['plugin'] != PLUGIN_FOLDER ) {
         return;
      }

      if ( $this->is_update_available() ) {

         $update = $this->get_update();

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
               background-image: url("<?php echo $changelog_image; ?>");
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
               <?php echo $update->sections->changelog; ?>
            </div>
         </div>

         <?php
      }

      exit;
   }




   /*
   |--------------------------------------------------------------------------
   | MISCELLANEOUS
   |--------------------------------------------------------------------------
   */


   /**
    * Enqueues CSS/JS files.
    *
    * @param array $files
    * @return array
    */
    public function enqueue_assets($files){

      add_thickbox();

      $files['admin']['scripts'] = array_merge($files['admin']['scripts'], [
         [
            'handle' => PREFIX . '_license_js',
            'src' => PLUGIN_URL . '/assets/js/license.js',
            'deps' => ['jquery'],
            'version' => PLUGIN_VERSION,
         ]
      ]);

      return $files;
   }



   /**
    * Processes the AJAX call for the license actions.
    *
    * @return string
    */
   public function process_license_submission(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $key = sanitize_text_field($_POST['key']);
      $mode = sanitize_text_field($_POST['mode']);
      $license = new License($key);

      if(empty($key)){
         wp_send_json_error([
            'message' => __('Please provide a license key!', 'woosa-adyen'),
         ]);
      }

      //save the key
      update_option(PREFIX . '_license_key', $key, false);


      if('activate' === $mode){

         $request = $license->activate();

         if( isset($request->license) ){

            $this->set_active();

            wp_send_json_success();

         }else{

            wp_send_json_error([
               'message' => isset($request->message) ? $request->message : __('This license could have not been activated.', 'woosa-adyen'),
            ]);
         }

      }elseif('deactivate' === $mode){

         $request = $license->deactivate();

         if( isset($request->license) ){

            $this->set_inactive();

            wp_send_json_success();

         }else{

            //in case of particular errors do not deactivate it
            if(in_array($request->code, ['rest_domain_not_removed', 'rest_bad_request'])){

               wp_send_json_error([
                  'message' => isset($request->message) ? $request->message : __('This license could have not been deactivated.', 'woosa-adyen'),
               ]);

            }else{

               $this->set_inactive();
            }
         }

      }elseif('get_update' === $mode){

         $request = $license->get_update(true);

         if( isset($request->version) ){

            $message = version_compare( $request->version, PLUGIN_VERSION, ">" ) ? sprintf(__('A new update is available, please go to %sPlugins page%s and check.', 'woosa-adyen'), '<a href="'.admin_url('/plugins.php').'">', '</a>') : __('No updates available, the plugin is already up to date.', 'woosa-adyen');

            wp_send_json_success([
               'message' => $message,
            ]);

         }else{

            $this->set_inactive();
         }

      }else{
         wp_send_json_error([
            'message' => __('Invalid action provided.', 'woosa-adyen'),
         ]);
      }

   }



   /**
    * A wrapper function which sends requests.
    *
    * @param string $endpoint
    * @param array $args
    * @return void|object $response
    */
   public function send_request( $endpoint, $args = [] ) {

      if( empty($this->get_supplier_url()) ){

         $response = (object)[
            'status' => 404,
            'data' => [
               'service' => 'license-manager',
               'message' => __('Supplier url is invalid', 'woosa-adyen')
            ]
         ];

      }else{

         $hash = hash('md5', $endpoint);
         $locked = get_option(PREFIX . '_lock_' . $hash);

         if( ! $locked ){

            update_option(PREFIX . '_lock_' . $hash, true, false);

            $default_args = [
               'method' => 'GET',
            ];
            $args    = wp_parse_args( $args, $default_args );
            $url     = $this->get_api_url($endpoint);
            $request = wp_remote_request( $url, $args );

            delete_option(PREFIX . '_lock_' . $hash);

            if( is_wp_error($request) ){

               $response = (object)[
                  'status' => $request->get_error_code(),
                  'data' => [
                     'message' => $request->get_error_message()
                  ]
               ];

            } else {

               $code = wp_remote_retrieve_response_code( $request );
               $body = json_decode( wp_remote_retrieve_body( $request ) );

               $response = (object) [
                  'status' => $code,
                  'data' => $body
               ];
            }

            $request_log = [
               'DESCRIPTION' => '====== REMOTE REQUEST ======',
               'REQUEST' => array_merge([
                  'endpoint' => $url,
               ], $args),
               'RESPONSE' => $response,
            ];

            if( ! in_array($response->status, [200, 201, 204]) ){
               Utility::wc_error_log($request_log, __FILE__, __LINE__ );
            }

            if(DEBUG){
               Utility::wc_debug_log($request_log, __FILE__, __LINE__ );
            }
         }

      }

      return $response;

   }

}
