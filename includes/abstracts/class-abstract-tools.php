<?php
/**
 * Abstract Tools
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Tools{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * Name of the section.
    *
    * @var string
    */
   protected $section = 'tools';



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Tools::$instance ) {
			Tools::$instance = new Tools;
		}

		return Tools::$instance;
	}


   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('woocommerce_get_sections_' . SETTINGS_TAB_ID, [$this, 'add_section'], 98);
      add_action('woocommerce_get_settings_' . SETTINGS_TAB_ID, [$this, 'add_section_fields'], 98, 2);
      add_action('woocommerce_admin_field_' . PREFIX . '_tools', [$this, 'output_section']);

      foreach($this->get_tools() as $key => $callback){
         add_action('admin_init', $callback);
      }
   }



   /**
    * The default list of tools.
    *
    * @return array
    */
   protected function default_tools(){

      return [
         'clear_cache' => [$this, '_process_clear_cache'],
      ];
   }



   /**
    * The list of tools.
    *
    * @return array
    */
   protected function get_tools(){
      return apply_filters(PREFIX .'\tools', $this->default_tools());
   }



   /*
   |--------------------------------------------------------------------------
   | Extend WC settings
   |--------------------------------------------------------------------------
   */


   /**
    * Adds the section in setting sections.
    *
    * @param array $sections
    * @return array
    */
   public function add_section($sections){

      $sections[$this->section] = __('Tools', '{text_domain}');

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

      if( $this->section === $section ){
         $fields = [
            [
               'title' => __('Tools', '{text_domain}'),
               'type' => 'title',
            ],
            [
               'id'   => PREFIX .'_tools',
               'option_key'  => PREFIX .'_tools',
               'type' => PREFIX .'_tools',
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
    * @param array $value
    * @return string
    */
   public function output_section($value){

      $GLOBALS['hide_save_button'] = true;

      ?>
      <tr>
         <td style="padding: 0;">
            <table class="widefat striped" style="max-width: 800px;">
               <?php foreach($this->get_tools() as $key => $callback){
                  if(method_exists($this, "{$key}__template")){
                     $this->{"{$key}__template"}();
                  }
               }?>
            </table>
         </td>
      </tr>
      <?php
   }



   /*
   |--------------------------------------------------------------------------
   | Tools
   |--------------------------------------------------------------------------
   */


   /**
    * Process the clear cache tool. Internal only!
    *
    * @return void
    */
   public function _process_clear_cache(){

      if( 'yes' === Utility::rgar($_GET, PREFIX . '_cache_cleared') ){
         Utility::show_notice(__('The caching data has been removed!', '{text_domain}' ), 'success');
      }

      if( 'yes' === Utility::rgar($_GET, PREFIX . '_clear_cache') && wp_verify_nonce( Utility::rgar($_GET, '_wpnonce'), 'wsa-nonce' ) ){

         $this->remove_transients();
         $this->process_clear_cache();

         do_action(PREFIX . '\clear_cache_tool');

         wp_cache_flush();

         wp_redirect( SETTINGS_URL . '&section='.$this->section.'&' . PREFIX . '_cache_cleared=yes' );
      }

   }



   /**
    * Process the clear cache tool. This is triggered when the clear cache tool is running.
    *
    * @return void
    */
   protected function process_clear_cache(){
   }



   /**
    * Removes all transients created by the plugin.
    *
    * @return void
    */
   protected function remove_transients(){
      global $wpdb;

		$wpdb->query("
			DELETE
				FROM `$wpdb->options`
			WHERE `option_name`
				LIKE ('_transient_".PREFIX."_%')
			OR `option_name`
				LIKE ('_transient_timeout_".PREFIX."_%')
		");
   }



   /**
    * Displays clear_cache tool
    *
    * @return string
    */
   protected function clear_cache__template(){

      ?>
      <tr>
         <td>
            <b><?php _e('Clear cache', '{text_domain}');?></b>
            <p class="description"><?php _e('This tool will clear all the caching data.', '{text_domain}');?></p>
         </td>
         <td style="text-align: right;">
            <a href="<?php echo SETTINGS_URL . '&section='.$this->section.'&' . PREFIX . '_clear_cache=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' );?>" class="button"><?php _e('Clear', '{text_domain}');?></a>
         </td>
      </tr>
      <?php
   }

}