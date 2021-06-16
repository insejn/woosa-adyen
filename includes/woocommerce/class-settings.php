<?php
/**
 * Settings
 *
 * This class extends WooCommerce settings.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Settings extends Abstract_Settings {


   /**
    * Initiates hooks.
    *
    * @since 1.0.0
    * @return void
    */
   public function init_hooks(){
      parent::init_hooks();

      add_filter('woocommerce_admin_settings_sanitize_option_' . PREFIX . '_order_reference_prefix', [__CLASS__, 'sanitize_order_reference_prefix']);

   }



   /**
   * List of available tab sections.
   *
   * @since 1.0.0
   * @return array
   */
   public function tab_sections() {

      $sections = parent::tab_sections();

      $sections['notifications'] = __('Notifications', 'woosa-adyen');

      return $sections;
   }



   /**
    * The list of sections where the Save button is hidden.
    *
    * @since 1.0.0
    * @return array
    */
   protected function hide_save_button_sections(){

      $sections = parent::hide_save_button_sections();

      return $sections;
   }



   /**
    * This is the General section groups.
    *
    * @since 1.0.0
    * @return array
    */
   protected function general__section(){

      $groups = parent::general__section();
      $new_groups = [];

      $new_groups['general'] = [
         [
            'name' => __('Payment', 'woosa-adyen'),
            'type' => 'title',
         ],
         [
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
         ],
         [
            'name' => __('Reference Prefix', 'woosa-adyen'),
            'type' => 'text',
            'desc_tip' => __('Specify a prefix (unique per webshop) for the payment reference. NOTE: Use this option only if you have a multisite installation otherwise you can leave it empty.', 'woosa-adyen'),
            'id'   => PREFIX .'_order_reference_prefix',
            'autoload' => false,
         ],
         [
            'name' => __('Remove Customer\'s Data', 'woosa-adyen'),
            'desc' => __('Enable', 'woosa-adyen'),
            'type' => 'checkbox',
            'desc_tip' => sprintf(__('This allows your customers to remove their personal data (%s) attached to an order payment. This only deletes the customer-related data for the specific payment, but does not cancel the existing recurring transaction.', 'woosa-adyen'), '<a href="https://gdpr-info.eu/art-17-gdpr/" target="_blank">GDPR</a>'),
            'default' => 'no',
            'id'   => PREFIX .'_allow_remove_gdpr',
            'autoload' => false,
         ],
         [
            'type' => 'sectionend',
         ],
      ];

      $new_groups['misc'] = $groups['misc'];

      return $new_groups;
   }



   /**
    * This is the Notifications section groups.
    *
    * @since 1.0.0
    * @return array
    */
   public function notifications__section(){

      $groups = [
         'notification' => [
            [
               // 'name' => __('Adyen Notifications', 'woosa-adyen'),
               'type' => 'title',
               'desc' => self::notification_desc(),
            ],
            [
               'name'     => __('Username', 'woosa-adyen'),
               'id'       => PREFIX.'_api_username',
               'autoload' => false,
               'type'     => 'text',
               'desc_tip' => __('Provide the username you set in authentication section (see step 5.)', 'woosa-adyen'),
            ],
            [
               'name'     => __('Password', 'woosa-adyen'),
               'id'       => PREFIX.'_api_password',
               'autoload' => false,
               'type'     => 'password',
               'desc_tip' => __('Provide the password you set in authentication section (see step 5.)', 'woosa-adyen'),
            ],
            [
               'type' => 'sectionend',
            ],
         ]
      ];

      return $groups;
   }



   /**
    * Displays the description for `Capture mode` option.
    *
    * @since 1.0.0
    * @return void
    */
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
    * Displays the description for 'Notifications` section.
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



   /**
    * Runs after saving settings.
    *
    * @since 1.1.3
    * @param string $section
    * @param array $data
    * @return void
    */
   protected function after_save($section = '', $data = []){
      parent::after_save($section, $data);

      if('authorization' === $section){
         API::instance()->check_connection();
      }
   }



   /**
    * Remove all defined settings
    *
    * @since 1.1.3
    * @return void
    */
   public function remove_settings(){

      if('yes' === get_option(PREFIX .'_remove_config')){

         //payments
         foreach(Core::payment_gateway([]) as $method){
            $method_id = str_replace('\\', '_', strtolower($method));
            delete_option("woocommerce_{$method_id}_settings");
         }

         //all settings
         foreach($this->get_sections() as $key => $value){

            if(empty($key)){
               $key = 'general';
            }

            foreach($this->get_settings($key) as $item){
               if(isset($item['id'])){
                  delete_option($item['id']);
               }
            }
         }

         //extra
         delete_option(PREFIX.'_capture_payment');
         delete_option(PREFIX.'_origin_keys');
         delete_option(PREFIX.'_errors');
         delete_option(PREFIX.'_is_authorized');
         delete_option(PREFIX.'_is_authorized_live');
         delete_option(PREFIX.'_is_authorized_test');

      }
   }


}