<?php
/**
 * Settings
 *
 * This class extends WooCommerce settings.
 *
 * @package Woosa-Adyen/WooCommerce
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


if(!class_exists('\WC_Settings_Page')){
   include_once WP_CONTENT_DIR . '/plugins/woocommerce/includes/admin/settings/class-wc-settings-page.php';
}


class Settings extends \WC_Settings_Page {

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
   public function __construct() {

      $this->id = 'adyen';

      add_filter('woocommerce_settings_tabs_array', array( $this, 'add_settings_tab'), 50);
      add_action('woocommerce_sections_' . $this->id, array( $this, 'output_sections' ));
      add_action('woocommerce_settings_' . $this->id, array( $this, 'output' ));
      add_action('woocommerce_settings_save_' . $this->id, array( $this, 'save' ));

      add_action('woocommerce_admin_field_' . PREFIX . '_tools', array($this, 'output_tools_field'));

      add_action('admin_init', [__CLASS__, 'generate_client_key']);
      add_action('admin_init', [__CLASS__, 'clear_cache']);
      add_action('admin_init', [__CLASS__, 'clear_admin_errors']);
      add_action('admin_init', [__CLASS__, 'show_admin_notice']);

      add_filter('woocommerce_admin_settings_sanitize_option_' . PREFIX . '_order_reference_prefix', [__CLASS__, 'sanitize_order_reference_prefix']);
   }



   /**
    * Adds new settings tab name.
    *
    * @since 1.0.0
    * @param array $settings_tabs
    * @return array
    */
   public function add_settings_tab($settings_tabs) {

      $settings_tabs[$this->id] = __('Adyen', 'woosa-adyen');

      return $settings_tabs;
   }



   /**
   * Gets setting sections.
   *
   * @since 1.0.0
   * @return array
   */
   public function get_sections() {

      $sections = [
         '' => __( 'Settings', 'woosa-adyen' ),
         'authorization' => __( 'Authorization', 'woosa-adyen' ),
         'notifications' => __( 'Notifications', 'woosa-adyen' ),
         'tools' => __( 'Tools', 'woosa-adyen' ),
         'license' => __( 'License', 'woosa-adyen' ),
      ];

      return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
   }



   /**
   * Gets settings per sections.
   *
   * @since 1.0.0
   * @param string $section
   * @return array
   */
   public function get_settings( $section = null ) {

      switch($section){

         case 'authorization':

            $settings = [
               array(
                  'name' => __('Authorization', 'woosa-adyen'),
                  'type' => 'title',
                  'desc' => self::api_desc(),
               ),
               array(
                  'name'     => __('Live Merchant Account', 'woosa-adyen'),
                  'id'       => PREFIX.'_merchant_account',
                  'autoload' => false,
                  'type'     => 'text',
                  'desc_tip' => __('The Merchant Account used in production environment.', 'woosa-adyen'),
               ),
               array(
                  'name'     => __('Live API Key', 'woosa-adyen'),
                  'id'       => PREFIX.'_api_key',
                  'autoload' => false,
                  'type'     => 'password',
                  'desc_tip' => __('The API Key used in production environment.', 'woosa-adyen'),
               ),
               array(
                  'name'     => __('Live URL-prefix', 'woosa-adyen'),
                  'id'       => PREFIX.'_url_prefix',
                  'autoload' => false,
                  'type'     => 'text',
                  'desc_tip' => __('Provide here the LIVE URL-prefix you have from Adyen', 'woosa-adyen'),
               ),
               array(
                  'name'    => __('Test Mode', 'woosa-adyen'),
                  'id'      => PREFIX.'_testmode',
                  'autoload' => false,
                  'type'    => 'checkbox',
                  'desc'    => __('Enable/Disable', 'woosa-adyen'),
                  'default' => 'no'
               ),
               array(
                  'name'     => __('Test Merchant Account', 'woosa-adyen'),
                  'id'       => PREFIX.'_test_merchant_account',
                  'autoload' => false,
                  'type'     => 'text',
                  'autoload' => false,
                  'class' => 'api_testmode_field',
                  'desc_tip' => __('The Merchant Account used in production environment.', 'woosa-adyen'),
               ),
               array(
                  'name'     => __('Test API Key', 'woosa-adyen'),
                  'id'       => PREFIX.'_test_api_key',
                  'autoload' => false,
                  'type'     => 'password',
                  'autoload' => false,
                  'class' => 'api_testmode_field',
                  'desc_tip' => __('The API Key used for test environment.', 'woosa-adyen'),
               ),
               array(
                  'type' => 'sectionend',
               ),
               array(
                  'type' => 'sectionend',
               )
            ];

         break;


         case 'notifications':

            $settings = [
               array(
                  // 'name' => __('Adyen Notifications', 'woosa-adyen'),
                  'type' => 'title',
                  'desc' => self::notification_desc(),
               ),
               array(
                  'name'     => __('Username', 'woosa-adyen'),
                  'id'       => PREFIX.'_api_username',
                  'autoload' => false,
                  'type'     => 'text',
                  'desc_tip' => __('Provide the username you set in authentication section (see step 5.)', 'woosa-adyen'),
               ),
               array(
                  'name'     => __('Password', 'woosa-adyen'),
                  'id'       => PREFIX.'_api_password',
                  'autoload' => false,
                  'type'     => 'password',
                  'desc_tip' => __('Provide the password you set in authentication section (see step 5.)', 'woosa-adyen'),
               ),
               array(
                  'type' => 'sectionend',
               ),
            ];

         break;


         case 'tools':

            $settings = [
               [
                  // 'name' => __('Tools', 'woosa-adyen'),
                  'type' => 'title',
               ],
               [
                  'name'     => __('Tools', 'woosa-adyen'),
                  'id'       => PREFIX . '_tools',
                  'type'     => PREFIX . '_tools',
                  'autoload' => false,
               ],
               [
                  'type' => 'sectionend',
               ]
            ];

         break;


         case 'license':

            $settings = array(
               array(
                  'name' => __('Automatic updates & support', 'woosa-adyen'),
                  'type' => 'title',
                  'desc' => Auto_Update::api_status()
               ),
               array(
                  'name' => __('API Key', 'woosa-adyen'),
                  'id'   => PREFIX.'_license_key',
                  'autoload' => false,
                  'type' => 'text',
               ),
               array(
                  'name' => __('API Email', 'woosa-adyen'),
                  'id'   => PREFIX.'_license_email',
                  'autoload' => false,
                  'type' => 'text',
               ),
               array(
                  'type' => 'sectionend',
               ),
            );

         break;


         default:

            $settings = [
               array(
                  'name' => __('Payment', 'woosa-adyen'),
                  'type' => 'title',
               ),
               array(
                  'name'     => __('Capture Mode', 'woosa-adyen'),
                  'id'       => PREFIX.'_capture_payment',
                  'autoload' => false,
                  'type'     => 'select',
                  'desc' => self::capture_desc(),
                  'default' => 'immediate',
                  'options' => [
                     'immediate' => __('Immediate', 'woosa-adyen'),
                     'delay' => __('With delay', 'woosa-adyen'),
                     'manual' => __('Manual', 'woosa-adyen'),
                  ]
               ),
               array(
                  'name' => __('Reference Prefix', 'woosa-adyen'),
                  'type' => 'text',
                  'desc_tip' => __('Specify a prefix (unique per webshop) for the payment reference. NOTE: Use this option only if you have a multisite installation otherwise you can leave it empty.', 'woosa-adyen'),
                  'id'   => PREFIX .'_order_reference_prefix',
                  'autoload' => false,
               ),
               array(
                  'name' => __('Remove Customer\'s Data', 'woosa-adyen'),
                  'desc' => __('Enable', 'woosa-adyen'),
                  'type' => 'checkbox',
                  'desc_tip' => sprintf(__('This allows your customers to remove their personal data (%s) attached to an order payment. This only deletes the customer-related data for the specific payment, but does not cancel the existing recurring transaction.', 'woosa-adyen'), '<a href="https://gdpr-info.eu/art-17-gdpr/" target="_blank">GDPR</a>'),
                  'default' => 'no',
                  'id'   => PREFIX .'_allow_remove_gdpr',
                  'autoload' => false,
               ),
               array(
                  'type' => 'sectionend',
               ),
               array(
                  'name' => __('Misc', 'woosa-adyen'),
                  'type' => 'title',
                  'desc' => '',
               ),
               array(
                  'name' => __('Debug Mode', 'woosa-adyen'),
                  'desc' => __('Enable', 'woosa-adyen'),
                  'type' => 'checkbox',
                  'desc_tip' => __('Set whether or not to enable debug mode.', 'woosa-adyen'),
                  'default' => 'no',
                  'id'   => PREFIX .'_debug',
                  'autoload' => false,
               ),
               array(
                  'name' => __('Remove Configuration', 'woosa-adyen'),
                  'desc' => __('Yes', 'woosa-adyen'),
                  'type' => 'checkbox',
                  'desc_tip' => __('Set whether or not to remove the plugin configuration on uninstall.', 'woosa-adyen'),
                  'default' => 'no',
                  'id'   => PREFIX .'_remove_config',
                  'autoload' => false,
               ),
               array(
                  'type' => 'sectionend',
               ),
            ];

      }

      return $settings;
   }



   /**
    * API description
    *
    * @since 1.0.7 - display the domain of the origin key
    * @since 1.0.0
    * @return string
    */
   public static function api_desc(){

      ob_start();
      ?>

      <ol>
         <li><?php printf(__('Please provide the Merchant Account and API Key to connect this webshop with Adyen platform, %sclick here%s for more details', 'woosa-adyen'), '<a href="https://docs.adyen.com/user-management/how-to-get-the-api-key" target="_blank">', '</a>');?></li>
         <li><?php printf(__('%sImportant!%s Before go live please contact %sAdyen support team%s to set up endpoints for live payments and to get their URL-prefix', 'woosa-adyen'), '<b>', '</b>', '<a href="https://support.adyen.com/hc/en-us/requests/new" target="_blank">','</a>');?></li>
      </ol>

      <?php

      $show_origin_keys = '';
      $origin_keys = get_option( PREFIX . '_origin_keys', [] );

      if(empty($origin_keys)){
         $show_origin_keys = '<span style="color: #a30000;">'.__( 'No origin key found, please make sure you provided all the information below and hit the "Save Changes" button!', 'woosa-adyen' ).'</span>';
      }else{

         foreach($origin_keys as $org_domain => $org_key){
            $show_origin_keys .= '<code>'. $org_domain . '</code> - <code>'.$org_key.'</code>';
         }
      }

      $output = self::authorization_status();
      $output .= '<br/><b>'.__('Origin domain & key:', 'woosa-adyen').'</b> '.$show_origin_keys;
      $output .= str_replace(array("\r","\n"), '', trim(ob_get_clean()));

      return $output;
   }



   /**
    * Show notification description
    *
    * @since 1.0.0
    * @return string
    */
   public static function notification_desc(){

      ob_start();
      ?>
      <h2><?php _e('Standard Notification', 'woosa-adyen');?></h2>
      <ol>
         <li><?php printf(__('Log in to your %s to configure notifications', 'woosa-adyen'), '<a href="https://ca-test.adyen.com/" target="_blank">Customer Area</a>');?></li>
         <li><?php printf(__('Go to %s', 'woosa-adyen'), '<b>Account > Server communication</b>');?></li>
         <li><?php printf(__('Next to %s, click %s', 'woosa-adyen'), '<b>Standard Notification</b>','<b>Add</b>');?></li>
         <li>
            <?php printf(__('Under %s, enter your server\'s:', 'woosa-adyen'), '<b>Transport</b>');?>
            <ul class="<?php echo PREFIX;?>-ullist">
               <li><b>URL</b> - <code><?php echo home_url('/wp-json/woosa-adyen/payment-status');?></code></li>
               <li><b>Method</b> - HTTP POST</li>
               <li><?php printf(__('Check the %s checkbox', 'woosa-adyen'), '<b>Active</b>');?></li>
            </ul>
         </li>
         <li><?php printf(__('In the %s section, enter a username and password that will be used to authenticate Adyen notifications in your webshop.', 'woosa-adyen'), '<b>Authentication</b>');?></li>
         <li><?php printf(__('Click %s', 'woosa-adyen'), '<b>Save Configuration</b>');?></li>
         <li><?php printf(__('%sImportant!%s Please contact %sAdyen support team%s to activate additional configuration as it\'s described %shere%s', 'woosa-adyen'), '<b>', '</b>', '<a href="https://support.adyen.com/hc/en-us/requests/new" target="_blank">', '</a>', '<a href="https://docs.adyen.com/development-resources/notifications/understand-notifications#additional-configuration" target="_blank">', '</a>');?></li>
      </ol>


      <h2><?php _e('Boleto Bancario Pending Notification', 'woosa-adyen');?></h2>
      <ol>
         <li><?php printf(__('Log in to your %s to configure notifications', 'woosa-adyen'), '<a href="https://ca-test.adyen.com/" target="_blank">Customer Area</a>');?></li>
         <li><?php printf(__('Go to %s', 'woosa-adyen'), '<b>Account > Server communication</b>');?></li>
         <li><?php printf(__('Next to %s, click %s', 'woosa-adyen'), '<b>Boleto Bancario Pending Notification</b>','<b>Add</b>');?></li>
         <li>
            <?php printf(__('Under %s, enter your server\'s:', 'woosa-adyen'), '<b>Transport</b>');?>
            <ul class="<?php echo PREFIX;?>-ullist">
               <li><b>URL</b> - <code><?php echo home_url('/wp-json/woosa-adyen/boleto-payment-status');?></code></li>
               <li><b>Method</b> - HTTP POST</li>
               <li><?php printf(__('Check the %s checkbox', 'woosa-adyen'), '<b>Active</b>');?></li>
            </ul>
         </li>
         <li><?php printf(__('In the %s section, enter a username and password that will be used to authenticate Adyen notifications in your webshop.', 'woosa-adyen'), '<b>Authentication</b>');?></li>
         <li><?php printf(__('Click %s', 'woosa-adyen'), '<b>Save Configuration</b>');?></li>
      </ol>
      <?php

      $output = str_replace(array("\r","\n"), '', trim(ob_get_clean()));

      return $output;

   }


   public static function capture_desc(){

      ob_start();
      ?>

      <p class="description"><?php _e('NOTE: you have to enable this option in Adyen account as well!', 'woosa-adyen');?></p>
      <p class="description"><?php _e('Manual: you need to explicitly request a capture for each payment.', 'woosa-adyen');?></p>

      <?php

      $output = str_replace(array("\r","\n"), '', trim(ob_get_clean()));

      return $output;
   }



   /**
    * Outputs settings.
    *
    * @since 1.0.0
    * @return void
    */
   public function output() {

      global $current_section;

      $settings = $this->get_settings( $current_section );

      woocommerce_admin_fields( $settings );
   }



   /**
    * Runs before saving settings.
    *
    * @since 1.0.0
    * @param string $section
    * @param array $data
    * @return void
    */
   public static function before_save($section = '', $data = array()){

      //process license
      if(isset($data[PREFIX.'_license_key']) && isset($data[PREFIX.'_license_email'])){
         Auto_Update::process_license( Utility::rgar($data, PREFIX.'_license_key'), Utility::rgar($data, PREFIX.'_license_email'), true);
      }
   }



   /**
    * Saves settings.
    *
    * @since 1.0.8 - change `authentication` to `authorization`
    * @since 1.0.0
    * @return void
    */
   public function save() {

      global $current_section;

      $settings = $this->get_settings( $current_section );

      self::before_save($current_section, $_POST);

      woocommerce_update_options( $settings );


      if($current_section === 'authorization'){
         API::check_connection();
      }
   }




   /**
    * Shows the authorization status
    *
    * @since 1.0.4
    * @return void
    */
   public static function authorization_status(){

      $unauthorized_msg = sprintf(__('%sStatus:%s %sUnauthorized%s', 'woosa-adyen'), '<b>', '</b>', '<span style="color: #cc0000;">', '</span>');
      $authorized_msg = sprintf(__('%sStatus:%s %sAuthorized%s', 'woosa-adyen'), '<b>', '</b>', '<span style="color: green;">', '</span>');

      return get_option(PREFIX.'_is_authorized') === false ? $unauthorized_msg : $authorized_msg;
   }



   /**
    * Clears transient data.
    *
    * @since 1.0.7 - remove payment_methods transient
    * @since 1.0.4
    * @return void
    */
   public static function clear_cache(){

      //cache cleared message
      if( 'yes' === Utility::rgar($_GET, 'cache_cleared') ){
         Utility::show_notice(__('The cache has been cleared!', 'woosa-adyen' ), 'success');
      }

      if( 'yes' === Utility::rgar($_GET, 'clear_cache') ){

         self::clear_cached_payment_methods();

         wp_redirect( SETTINGS_URL . '&section=tools&cache_cleared=yes' );
      }

   }



   /**
    * Generates a client key for the current domain.
    *
    * @since 1.1.1
    * @return void
    */
   public static function generate_client_key(){

      //cache cleared message
      if( 'yes' === Utility::rgar($_GET, 'generated_client_key') ){
         Utility::show_notice(
            sprintf(
               __('The client key for %s has been generated!', 'woosa-adyen' ),
               '<code>'.API::get_origin_domain().'</code>'
            )
         , 'success');
      }

      if( 'yes' === Utility::rgar($_GET, 'generate_client_key') &&
         'adyen' === Utility::rgar($_GET, 'tab') &&
         wp_verify_nonce( Utility::rgar($_GET, '_wpnonce'), 'wsa-nonce' )
      ){

         API::generate_origin_keys();

         wp_redirect( SETTINGS_URL . '&section=tools&generated_client_key=yes' );
      }

   }



   /**
    * Clears admin errors.
    *
    * @since 1.1.0
    * @return void
    */
   public static function clear_admin_errors(){

      if( 'yes' === Utility::rgar($_GET, 'admin_errors_cleared') ){
         Utility::show_notice(__('The admin errors have been removed!', 'woosa-adyen' ), 'success');
      }

      if(
         'yes' === Utility::rgar($_GET, 'clear_admin_errors') &&
         'adyen' === Utility::rgar($_GET, 'tab') &&
         wp_verify_nonce( Utility::rgar($_GET, '_wpnonce'), 'wsa-nonce' )
      ){
         delete_option(PREFIX.'_errors');

         wp_redirect( SETTINGS_URL . '&section=tools&admin_errors_cleared=yes' );
      }
   }



   /**
    * Displays admin notices.
    *
    * @since 1.1.1
    * @return void
    */
   public static function show_admin_notice(){

      if(empty(API::get_origin_key())){
         Utility::show_notice(sprintf(
            __('The client key for %s is missing, please %sgo to this page%s to generate one.', 'woosa-adyen'),
            '<code>'.API::get_origin_domain().'</code>',
            '<a href="'.SETTINGS_URL .'&section=tools">',
            '</a>'
         ));
      }
   }



   /**
    * Clears cached payment methods.
    *
    * @since 1.1.1 - remove transient with a DB query instead of dedicated function
    *              - flush WP cache
    * @since 1.0.10
    * @return void
    */
   public static function clear_cached_payment_methods(){

      global $wpdb;

      $names = [
         PREFIX . '_is_active_',
         PREFIX . '_payment_method',
         PREFIX . '_stored_payment_methods_',
      ];

      foreach($names as $name){
         $wpdb->query("
            DELETE
               FROM `$wpdb->options`
            WHERE `option_name`
               LIKE ('_transient_$name%')
            OR `option_name`
               LIKE ('_transient_timeout_$name%')
         ");
      }

      wp_cache_flush();
   }



   /**
    * Updates cached payment methods.
    *
    * @since 1.0.10
    * @return void
    */
   public static function update_cached_payment_methods(){

      self::clear_cached_payment_methods();
      API::get_response_payment_methods();
   }



   /**
    * Displayes the section contend for `Tools`
    *
    * @since 1.1.0
    * @param array $values
    * @return string
    */
   public static function output_tools_field($values){

      wc_get_template('section-tools.php', [], '', PLUGIN_DIR . '/templates/settings/');
   }



   /**
    * Sanitizes the value before saving it.
    *
    * @since 1.1.0
    * @param string $value
    * @return string
    */
   public static function sanitize_order_reference_prefix($value){

      $value = preg_replace('/[^a-zA-Z0-9]/', '', $value);
      $value = strtoupper(substr($value, 0, 4));

      return $value;
   }


}