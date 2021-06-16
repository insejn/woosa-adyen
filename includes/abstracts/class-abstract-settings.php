<?php
/**
 * Abstract Settings
 *
 * This class extends WooCommerce settings.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


if(!class_exists('\WC_Settings_Page')){
   include_once WP_CONTENT_DIR . '/plugins/woocommerce/includes/admin/settings/class-wc-settings-page.php';
}


abstract class Abstract_Settings extends \WC_Settings_Page {

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * Setting page id.
    *
    * @var string
    */
   protected $id = SETTINGS_TAB_ID;


   /**
    * Setting page label.
    *
    * @var string
    */
   protected $label = SETTINGS_TAB_LABEL;



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Settings::$instance ) {
			Settings::$instance = new Settings;
		}

		return Settings::$instance;
   }


   /**
    * Constructor of this class.
    *
    */
   public function __construct() {
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action(PREFIX . '\on_uninstall', [$this, 'remove_settings']);

      add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 50);
      add_action('woocommerce_settings_' . $this->id, [$this, 'output_sections']);
      add_action('woocommerce_settings_' . $this->id, [$this, 'render_tab']);
      add_action('woocommerce_settings_save_' . $this->id, [$this, 'save_tab']);

      add_filter('woocommerce_settings_groups', [$this, 'register_setting_tab']);
      add_filter('woocommerce_settings-' . $this->id, [$this, 'register_setting_tab_fields']);
   }



   /**
    * Registers setting tab which will be available via REST API.
    *
    * @param array $locations
    * @return array
    */
   public function register_setting_tab($locations){

      $locations[] = [
         'id'    => $this->id,
         'label' => $this->label,
      ];

      return $locations;
   }



   /**
    * Registers setting fields which will be available via REST API
    *
    * @param array $locations
    * @return array
    */
   public function register_setting_tab_fields($settings){

      return array_merge(
         $settings,
         $this->get_settings(),
         $this->get_settings('authorization'),
         $this->get_settings('license')
      );

   }



   /**
   * Gets tab sections.
   *
   * @return array
   */
   public function get_sections() {
      return apply_filters( 'woocommerce_get_sections_' . $this->id, $this->tab_sections() );
   }



   /**
   * List of available tab sections.
   *
   * @return array
   */
   public function tab_sections(){

      return [
         '' => __( 'Settings', '{text_domain}' ),
      ];
   }



   /**
    * This is the General section groups.
    *
    * @return array
    */
   protected function general__section(){

      return [
         'misc' => [
            [
               'name' => __('Misc', '{text_domain}'),
               'type' => 'title',
               'desc' => '',
            ],
            [
               'name' => __('Debug Mode', '{text_domain}'),
               'desc' => __('Enable', '{text_domain}'),
               'id'   => PREFIX .'_debug',
               'option_key'  => PREFIX .'_debug',
               'type' => 'checkbox',
               'desc_tip' => __('Set whether or not to enable debug mode.', '{text_domain}'),
               'default' => 'no',
               'autoload' => false,
            ],
            [
               'name' => __('Remove Configuration', '{text_domain}'),
               'desc' => __('Yes', '{text_domain}'),
               'id'   => PREFIX .'_remove_config',
               'option_key'  => PREFIX .'_debug',
               'type' => 'checkbox',
               'desc_tip' => __('Set whether or not to remove the plugin configuration on uninstall.', '{text_domain}'),
               'default' => 'no',
               'autoload' => false,
            ],
            [
               'type' => 'sectionend',
            ],
         ]
      ];

   }



   /**
   * Gets settings per section.
   *
   * @param string $section
   * @return array
   */
   public function get_settings( $section = null ) {

      if( array_key_exists( $section, $this->get_sections() ) && method_exists($this, "{$section}__section") ){
         $groups = $this->{"{$section}__section"}();
      }else{
         $groups = $this->general__section();
      }

      $settings = [];

      foreach($groups as $fields){
         $settings = array_merge($settings, $fields);
      }

      return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $section );
   }



   /**
    * Remove all defined settings
    *
    * @return void
    */
    public function remove_settings(){

      if('yes' === get_option(PREFIX .'_remove_config')){

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

      }
   }



   /**
    * Adds setting tab.
    *
    * @param array $settings_tabs
    * @return array
    */
   public function add_tab($settings_tabs) {

      $settings_tabs[$this->id] = $this->label;

      return $settings_tabs;
   }



   /**
    * Render settings tab.
    *
    * @return string
    */
   public function render_tab() {

      global $current_section;

      $settings = $this->get_settings( $current_section );

      if( in_array( Utility::rgar($_GET, 'section'), $this->hide_save_button_sections() ) ) {
         $GLOBALS['hide_save_button'] = true;
      }

      woocommerce_admin_fields( $settings );

      if ( 'authorization' == Utility::rgar($_GET, 'section') ) {

         if ( API::instance()->is_authorized() ) {
            $button_value = esc_attr( __( 'Save Changes', '{text_domain}' ) );
            $button_label = esc_html( __( 'Save Changes', '{text_domain}' ) );
         } else {
            $button_value = esc_attr( __( 'Save & Authorize', '{text_domain}') );
            $button_label = esc_html( __( 'Save & Authorize', '{text_domain}') );
         }

         echo '<p><button name="save" class="button-primary woocommerce-save-button" type="submit" value="'.$button_value.'">'.$button_label.'</button></p>';
      }

   }



   /**
    * The list of sections where the Save button is hidden.
    *
    * @return array
    */
   protected function hide_save_button_sections(){

      return [
         'authorization',
      ];
   }



   /**
    * Saves settings tab.
    *
    * @return void
    */
   public function save_tab() {

      global $current_section;

      $ac = new Action_Checker;

      if ( $ac->in_progress() ) {

         \WC_Admin_Settings::add_error( $ac->get_info_message() );

      } else {
         $settings = $this->get_settings( $current_section );

         $this->before_save($current_section, $_POST);

         woocommerce_update_options( $settings );

         $this->after_save($current_section, $_POST);
      }
   }



   /**
    * Runs before saving settings.
    *
    * @param string $section
    * @param array $data
    * @return void
    */
   protected function before_save($section = '', $data = []){
   }



   /**
    * Runs after saving settings.
    *
    * @param string $section
    * @param array $data
    * @return void
    */
   protected function after_save($section = '', $data = []){
   }

}