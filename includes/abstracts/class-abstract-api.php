<?php
/**
 * API
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_API{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * The environment (live, test, etc).
    * Based on this the authorization status and the actions connect/disconnect will be processed separately.
    *
    * @var string
    */
   public $environment = 'live';



	/**
	 * Returns an instance of this class based on the given environment.
	 *
	 * @param string $env
	 * @return object An instance of this class.
	 */
	public static function instance($env = '') {

		$env = empty($env) ? API::get_active_environment() : $env;

		// If the single instance hasn't been set, set it now.
		if ( null == API::$instance ) {

			API::$instance = new API($env);

		}elseif(API::$instance->environment != $env){

			API::$instance = new API($env);
		}

		return API::$instance;
	}



   /**
    * Constructor of this class.
    *
    */
   public function __construct($environment = 'live'){

      $this->environment = $environment;
      $this->logger = Logger::instance();
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('woocommerce_get_sections_' . SETTINGS_TAB_ID, [$this, 'add_section'], 90);
      add_action('woocommerce_get_settings_' . SETTINGS_TAB_ID, [$this, 'add_section_fields'], 90, 2);

   }



   /**
    * Adds the section in setting sections.
    *
    * @param array $sections
    * @return array
    */
   public function add_section($sections){

      $sections['authorization'] = __('Authorization', '{text_domain}');

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

      if( 'authorization' === $section ){
         $fields = [
            [
               'name' => __('Authorization', '{text_domain}'),
               'desc' => $this->render_status(),
               'type' => 'title',
            ],
            [
               'name' => __('API Token', '{text_domain}'),
               'id'   => PREFIX .'_access_token',
               'option_key'  => PREFIX .'_access_token',
               'type' => 'password',
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
    * Checks whether or not the plugin is authorized.
    *
    * @var bool
    */
   public function is_authorized(){
      return get_option( $this->add_env_suffix(PREFIX.'_is_authorized') ) === false ? false : true;
   }



   /**
    * Sets the authorized flag.
    *
    * @return void
    */
   public function set_as_authorized(){

      update_option( $this->add_env_suffix(PREFIX.'_is_authorized'), 'yes', false );

      $this->logger->remove_log('not_authorized');

   }



   /**
    * Remove authorized flag.
    *
    * @return void
    */
   public function set_as_unauthorized(){

      delete_option( $this->add_env_suffix(PREFIX.'_is_authorized') );

      $this->logger->set_warning('not_authorized', [], __FILE__, __LINE__);

   }



   /**
    * Gets the authorization status.
    *
    * @return string
    */
   public function get_status(){

      if( self::is_authorized() ){
         return __('Authorized', '{text_domain}');
      }

      return __('Unauthorized', '{text_domain}');
   }



   /**
    * Displays the authorization status
    *
    * @return string
    */
   public function render_status(){

      $extra = json_encode([
         'environment' => $this->environment,
      ]);

      $extra_data = "data-" . PREFIX . "-extra='{$extra}'";

      $color = $this->is_authorized() ? 'green' : '#cc0000';
      $status = '<b>'.__('Status:', '{text_domain}').'</b> <span style="color: '.$color.';">'.$this->get_status().'</span>';
      $action = $this->is_authorized() ? ' ('. sprintf(__( '%sClick to revoke%s', '{text_domain}' ), '<a href="#" data-'.PREFIX.'-action="revoke_authorization" '.$extra_data.'>', '</a>') . ')' : '';

      $html = $status.$action;

      return $html;
   }



	/**
	 * Retrieves the active environment.
	 *
	 * @return string
	 */
	protected static function get_active_environment(){

		$test_mode = wc_string_to_bool( get_option(PREFIX . '_test_mode') );

		if( $test_mode ) {
			return 'test';
		}

		return 'live';
	}



   /**
    * Adds the environment as suffix to the given string.
    *
    * @param string $string
    * @return string
    */
   public function add_env_suffix($string){

      $string = empty($this->environment) ? $string : "{$string}_{$this->environment}";

      return $string;
   }


}