<?php
/**
 * Abstract Assets
 *
 * This is responsible for registering and enqueuing JS/CSS files.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Assets{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * List of admin style files
    *
    * @var array
    */
   protected $admin_styles = [];


   /**
    * List of admin script files.
    *
    * @var array
    */
   protected $admin_scripts = [];


   /**
    * List of frontend style files.
    *
    * @var array
    */
   protected $frontend_styles = [];


   /**
    * List of frontend script files.
    *
    * @var array
    */
   protected $frontend_scripts = [];



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Assets::$instance ) {
			Assets::$instance = new Assets;
		}

		return Assets::$instance;
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('admin_enqueue_scripts', [$this , 'process_admin_files'], 999);
      add_action('wp_enqueue_scripts', [$this , 'process_frontend_files'], 999);

      add_action('init', [$this, 'process_view_files']);
   }



   /**
    * Process files for admin view.
    *
    * @return void
    */
   public function process_admin_files(){

      $this->process_styles( $this->admin_styles);
      $this->process_scripts( $this->admin_scripts );
   }



   /**
    * Process files for frontend view.
    *
    * @return void
    */
   public function process_frontend_files(){

      $this->process_styles( $this->frontend_styles );
      $this->process_scripts( $this->frontend_scripts );
   }



   /**
    * Prepare files to be processes based on the view (admin/frontend).
    *
    * @return void
    */
   public function process_view_files(){

      foreach($this->get_files() as $view => $file_type){

         if('admin' === $view){
            $this->admin_styles = array_merge($this->admin_styles, $file_type['styles']);
            $this->admin_scripts = array_merge($this->admin_scripts, $file_type['scripts']);
         }
         if('frontend' === $view){
            $this->frontend_styles = array_merge($this->frontend_styles, $file_type['styles']);
            $this->frontend_scripts = array_merge($this->frontend_scripts, $file_type['scripts']);
         }
      }
   }



   /**
    * Registers and enqueues style files.
    *
    * @param array $files
    * @return void
    */
   protected function process_styles($files){

      foreach($files as $file){

         if( (array_key_exists('register', $file) && $file['register'] === true) || ! array_key_exists('register', $file) ){
            wp_register_style(
               $file['handle'],
               $file['src'],
               $file['deps'],
               $file['version']
            );
         }

         if( (array_key_exists('enqueue', $file) && $file['enqueue'] === true) || ! array_key_exists('enqueue', $file) ){
            wp_enqueue_style($file['handle']);
         }
      }

   }



   /**
    * Registers and enqueues script files.
    *
    * @param array $files
    * @return void
    */
   protected function process_scripts($files){

      foreach($files as $file){

         if( (array_key_exists('register', $file) && $file['register'] === true) || ! array_key_exists('register', $file) ){
            wp_register_script(
               $file['handle'],
               $file['src'],
               $file['deps'],
               $file['version'],
               Utility::rgar($file, 'in_footer', true)
            );
         }

         if( array_key_exists('localize_script', $file) ){
            if( isset($file['localize_script']['handle']) ){

               wp_localize_script(
                  $file['localize_script']['handle'],
                  $file['localize_script']['object_name'],
                  $file['localize_script']['data']
               );

            }
         }

         if( (array_key_exists('enqueue', $file) && $file['enqueue'] === true) || ! array_key_exists('enqueue', $file) ){
            wp_enqueue_script($file['handle']);
         }
      }

   }



   /**
    * The list of style and script files.
    *
    * @return array
    */
   public function get_files(){
      return apply_filters(PREFIX . '\assets\files', $this->get_default_files());
   }



   /**
    * The default list of style and script files.
    *
    * @return array
    */
	protected function get_default_files(){

		return [
			'admin' => [
            'styles' => [
               [
                  'handle' => PREFIX . '_admin_css',
                  'src' => PLUGIN_URL . '/assets/css/admin.css',
                  'deps' => [],
                  'version' => PLUGIN_VERSION,
                  'enqueue' => true
               ]
            ],
            'scripts' => [
               [
                  'handle' => PREFIX . '_admin_js',
                  'src' => PLUGIN_URL . '/assets/js/admin.js',
                  'deps' => ['jquery'],
                  'version' => PLUGIN_VERSION,
                  'in_footer' => true,
                  'localize_script' => [
                     'handle' => PREFIX . '_admin_js',
                     'object_name' => 'woosa_' . PREFIX,
                     'data' => [
                        'ajax' => array(
                           'url' => admin_url( 'admin-ajax.php' ),
                           'nonce' => wp_create_nonce( 'wsa-nonce' )
                        ),
                        'prefix' => PREFIX,
                        'translation' => [
                           'processing' => __('Processing...', '{text_domain}'),
                        ],
                     ]
                  ],
                  'enqueue' => true
               ]
            ]
         ],
         'frontend' => [
            'styles' => [
               [
                  'handle' => PREFIX . '_frontend_css',
                  'src' => PLUGIN_URL . '/assets/css/frontend.css',
                  'deps' => [],
                  'version' => PLUGIN_VERSION,
                  'enqueue' => true
               ]
            ],
            'scripts' => [
               [
                  'handle' => PREFIX . '_frontend_js',
                  'src' => PLUGIN_URL . '/assets/js/frontend.js',
                  'deps' => ['jquery'],
                  'version' => PLUGIN_VERSION,
                  'in_footer' => true,
                  'localize_script' => [],
                  'enqueue' => true
               ]
            ]
         ]
		];
   }

}