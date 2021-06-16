<?php
/**
 * Abstract Core
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Core{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Core::$instance ) {
			Core::$instance = new Core;
		}

		return Core::$instance;
   }



   /**
    * Constructor of this class.
    *
    */
   public function __construct(){

      $this->dependency = Dependency::instance();

   }



   /**
    * Initialization.
    *
    * @return void
    */
   public function init(){

      try {
         $this->dependency->checker();
      } catch(\Exception $e){
         Utility::show_notice($e->getMessage(), 'error');
         return;
      }

      //--- hooks
      $this->init_default_hooks();
      $this->init_hooks();

      //---modules
      $this->init_default_modules();
      $this->init_modules();
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   protected function init_hooks(){
   }



   /**
    * Initiates modules.
    *
    * @return void
    */
   protected function init_modules(){
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   protected function init_default_hooks(){

      add_action('admin_init', [$this, 'init_plugin_action_links']);
      add_filter('is_protected_meta', [$this, 'hide_metadata_entries'], 10, 3);
   }



   /**
    * Initiates default modules.
    *
    * @return void
    */
   protected function init_default_modules(){

      API::instance()->init_hooks();
      License::instance()->init_hooks();
      Assets::instance()->init_hooks();
      AJAX::instance();
      // Event::instance()->init_hooks();
      Third_Party::instance()->init_hooks();
      // Logger::instance()->init_hooks();
      Tools::instance()->init_hooks();
      Action_Scheduler::init_hooks();
      Action_Checker::instance()->init_hooks();
   }



   /**
    * Hides our metadata entries but shows them if debug mode is enabled
    *
    * @param bool $protected
    * @param string $meta_key
    * @param string $meta_type
    * @return bool
    */
   public function hide_metadata_entries($protected, $meta_key, $meta_type){

      if(strpos($meta_key, PREFIX.'_') !== false && DEBUG === false){
         $protected = true;
      }

      if(strpos($meta_key, '_' . PREFIX.'_') !== false && DEBUG === true){
         $protected = false;
      }

      return $protected;
   }



   /**
    * Registers the activation hook.
    *
    * @return void
    */
   public function register_activation_hook(){
      register_activation_hook( dirname(PLUGIN_DIR).'/'.PLUGIN_BASENAME, [$this, 'on_activation'] );
   }



   /**
    * Registers the deactivation hook.
    *
    * @return void
    */
   public function register_deactivation_hook(){
      register_deactivation_hook( dirname(PLUGIN_DIR).'/'.PLUGIN_BASENAME, [$this, 'on_deactivation'] );
   }



   /**
    * Registers the uninstall hook.
    *
    * @return void
    */
   public function register_uninstall_hook(){
      register_uninstall_hook( dirname(PLUGIN_DIR).'/'.PLUGIN_BASENAME, [__CLASS__, 'on_uninstall'] );
   }



   /**
    * Registers the upgrade hook.
    *
    * @return void
    */
   public function register_upgrade_hook(){
      add_action('upgrader_process_complete', [$this, '_on_upgrade'], 10, 2);
   }



   /**
    * Runs when plugin is activated.
    *
    * @return void
    */
   public function on_activation(){

      try{
         $this->dependency->checker();
      } catch(\Exception $e){
         $msg = $e->getMessage();
         $msg = $msg . ' ' . sprintf(
               __('%sGo back%s', '{text_domain}'),
               '<a href="' . admin_url('plugins.php') . '">',
               '</a>'
            );
         wp_die($msg);
      }

      do_action(PREFIX . '\on_activation');
   }



   /**
    * Runs when plugin is deactivated.
    *
    * @return void
    */
   public function on_deactivation(){

      do_action(PREFIX . '\on_deactivation');
   }



   /**
    * Runs when plugin is updated.
    *
    * @return void
    */
   protected function on_upgrade(){
   }



   /**
    * Runs when plugin is updated. Internal use only!
    *
    * @param object $upgrader_object
    * @param array $options
    * @return void
    */
   public function _on_upgrade( $upgrader_object, $options ) {

      if($options['action'] == 'update' && $options['type'] == 'plugin' ){

         foreach($options['plugins'] as $plugin){

            if($plugin == PLUGIN_BASENAME){

               $this->on_upgrade();

               do_action(PREFIX . '\on_upgrade');
            }
         }
      }
   }



   /**
    * Runs when plugin is deleted.
    *
    * @return void
    */
   public static function on_uninstall(){

      do_action(PREFIX . '\on_uninstall');
   }



   /**
    * Displays plugin action links in plugins list page.
    *
    * @return void
    */
   public function init_plugin_action_links(){

      $this->add_plugin_action_links([
         'actions' => [
            SETTINGS_URL => __('Settings', '{text_domain}'),
            LOGS_URL => __('Logs', '{text_domain}'),
         ],
         'meta' => [
            // '#1' => __('Docs', '{text_domain}'),
            // '#2' => __('Visit website', '{text_domain}')
         ],
      ]);
   }



   /**
    * Adds plugin action and meta links.
    *
    * @param array $sections
    * @return void
    */
   protected function add_plugin_action_links($sections = array()) {

      //actions
      if(isset($sections['actions'])){

         $actions = $sections['actions'];
         $links_hook = 'plugin_action_links_';

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
    * Gets uploads folder path including the given endpoint
    *
    * @param string $endpoint (folder_x/file_y.jpg)
    * @return string
    */
   public function get_uploads_path($endpoint = ''){

      $upload     = wp_upload_dir();
      $upload_dir = $upload['basedir'];
      $dir        = $upload_dir."/".PLUGIN_FOLDER."_uploads";

      if ( ! is_dir($dir)) {
         mkdir($dir);
      }
      if( ! file_exists("{$dir}/index.html")){
         file_put_contents("{$dir}/index.html", "");
      }

      $path = $dir.'/'.trim($endpoint, '/');

      return $path;
   }



   /**
    * Gets uploads folder URL including the given endpoint
    *
    * @param string $endpoint (folder_x/file_y.jpg)
    * @return string
    */
   public function get_uploads_url($endpoint = ''){

      $upload     = wp_upload_dir();
      $upload_dir = $upload['baseurl'];
      $dir        = $upload_dir."/".PLUGIN_FOLDER."_uploads";
      $path       = $dir.'/'.trim($endpoint, '/');

      return $path;
   }

}