<?php
/**
 * Abstract Logger
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Logger{

   /**
    * The instance of this class.
    *
    * @var null|object
    */
   protected static $instance = null;


   /**
    * List of logs.
    *
    * @var array
    */
   protected $logs = [];


   /**
    * Number of logs per page.
    *
    * @var integer
    */
   protected $per_page = 10;


   /**
    * Max execution time required.
    *
    * @var integer
    */
   protected $max_exec_time = 0;


   /**
    * Available actions.
    *
    * @var array
    */
   protected $actions = ['view_detail', 'toggle_visibility', 'remove'];



	/**
	 * Returns an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == Logger::$instance ) {
			Logger::$instance = new Logger;
		}

		return Logger::$instance;
	}



   /**
    * Construct of this class.
    *
    */
   public function __construct(){

      $this->logs = get_option(PREFIX . '_logs', []);

   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('woocommerce_get_sections_' . SETTINGS_TAB_ID, [$this, 'add_section'], 100);
      add_action('woocommerce_get_settings_' . SETTINGS_TAB_ID, [$this, 'add_section_fields'], 100, 2);
      add_action('woocommerce_admin_field_' . PREFIX . '_logs', [$this, 'output_section']);

      add_filter(PREFIX . '\assets\files', [$this, 'enqueue_assets']);

      add_action('wp_ajax_' . PREFIX . '_log_action', [$this, 'process_log_action']);

      add_action('admin_print_footer_scripts', [$this, 'render_notification']);

      add_action('admin_init', [$this, 'check_connected_options']);

		add_action('admin_init', [$this, 'process_admin_logs']);

      add_action('updated_option', [$this, 'process_connected_options'], 30);
      add_action('added_option', [$this, 'process_connected_options'], 30);
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

      $sections['logs'] = __('Logs', '{text_domain}');

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

      if( 'logs' === $section ){
         $fields = [
            [
               'name' => sprintf(__('Logs (%s)', '{text_domain}'), count($this->logs)),
               'type' => 'title',
            ],
            [
               'id'   => PREFIX .'_logs',
               'option_key'  => PREFIX .'_logs',
               'type' => PREFIX .'_logs',
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
            <div class="<?php echo PREFIX;?>-logs">
               <?php $this->render_logs();?>
            </div>
         </td>
      </tr>
      <?php
   }




   /*
   |--------------------------------------------------------------------------
   | GETTERS
   |
   | Functions which retrive the data
   |--------------------------------------------------------------------------
   */


   /**
    * Retrieves the list of saved logs.
    *
    * @return array
    */
   public function get_logs(){

      $logs = $this->logs;

      uasort($logs, function( $a, $b ) {
         return $b["date"] <=> $a["date"];
      });

      return $logs;
   }



   /**
    * Retrieves a log by its code.
    *
    * @param string $code
    * @return array|null
    */
   public function get_log($code){

      $logs = $this->get_logs();
      $code = $this->sanitize_key($code);

      if(isset($logs[$code])){

         $actions = [];

         //get the actions
         foreach($this->actions as $key){
            $actions[$key] = get_option(PREFIX . "_log_{$code}_{$key}");
         }

         $logs[$code]['actions'] = array_filter($actions);

         return $logs[$code];
      }

   }



   /**
    * The default list of messages.
    *
    * @return array
    */
   protected function get_default_messages(){

      return [
         'e100' => __('An error has occurred while processing the license.', '{text_domain}'),
         'create_wc_api_keys' => __('An error has occurred while trying to generare WooCommerce API keys.', '{text_domain}'),
         'no_wc_api_keys' => __('The WooCommerce REST API Keys required are missing, please contact us to solve this.', '{text_domain}'),
         'not_authorized' => sprintf(__('You have to authorize the plugin. Please go to %sthis page%s.', '{text_domain}'), '<a href="'.SETTINGS_URL.'&section=authorization">', '</a>'),
         'max_exec_time' => sprintf(__('Your server %s should be at least %s seconds! Please get in tough with your hosting provider to increase that otherwise the plugin might not work properly.', '{text_domain}'), '<code>max_execution_time</code>', $this->max_exec_time),
      ];
   }



   /**
    * The list of messages.
    *
    * @return array
    */
   public function get_messages(){
      return apply_filters(PREFIX .'\logger\messages', $this->get_default_messages());
   }



   /**
    * Gets a message by code.
    *
    * @param string $code
    * @return string
    */
   public function get_message_by_code($code){

      $message = __('An unknown log has been created.', '{text_domain}');
      $code = $this->sanitize_key($code);

      if(isset($this->get_messages()[$code])){
         $message = $this->get_messages()[$code];
      }

      return $message;
   }



   /**
    * The default list of options which are connected with the logger.
    *
    * @return array
    */
   protected function get_default_connected_options(){

      return [
         PREFIX.'_license_error' => [
            'type' => 'error',
            'code' => 'e100',
            'actions' => [
               'view_detail' => [
                  'data' => get_option(PREFIX.'_license_error'),
               ]
            ]
         ],
      ];
   }



   /**
    * Let the connected options to be filtered by third-party plugins.
    *
    * @return array
    */
   public function get_connected_options(){
      return apply_filters(PREFIX .'\logger\options', $this->get_default_connected_options());
   }



   /**
    * Gets the message for "No results".
    *
    * @return string
    */
   public function get_no_results_text(){
      return __('There are no logs for the moment.', '{text_domain}');
   }



   /**
    * Retrieves the log template.
    *
    * @param array $log
    * @return string
    */
   protected function get_template($log){

      $active_class = $log['read'] ? '' : PREFIX . '-logs__item--active';

      ob_start();

      ?>
      <div class="<?php echo PREFIX;?>-logs__item <?php echo PREFIX;?>-logs__item--<?php echo $log['type'];?> <?php echo $active_class;?>" data-<?php echo PREFIX;?>-log-code="<?php echo $log['code'];?>">
         <div class="<?php echo PREFIX;?>-log-meta">
            <div class="<?php echo PREFIX;?>-log-meta__left">
                  <div class="<?php echo PREFIX;?>-log-type">
                     <label>
                        <input type="checkbox" data-<?php echo PREFIX;?>-log-checkbox name="<?php echo PREFIX;?>-log-selected[]" value="<?php echo $log['code'];?>">
                        <span><?php echo strtoupper($log['type']);?></span>
                     </label>
                  </div>
            </div>
            <div class="<?php echo PREFIX;?>-log-meta__right">
               <div class="<?php echo PREFIX;?>-log-date"><?php echo __('Date:', '{text_domain}') .' '. date('Y/m/d', $log['date']) . ' '.__('at', '{text_domain}').' ' . date('h:i:s a', $log['date']);?></div>
            </div>
         </div>
         <div class="<?php echo PREFIX;?>-log-message"><?php echo $this->get_message_by_code($log['code']);?></div>

         <div class="<?php echo PREFIX;?>-log-meta">
            <?php if(isset($log['path']) && ! empty($log['path']) ):?>
               <div class="<?php echo PREFIX;?>-log-path"><?php printf(__('Thrown in: %s', '{text_domain}'), $log['path']);?></div>
            <?php endif;?>

            <div class="<?php echo PREFIX;?>-log-meta__left">
               <div class="<?php echo PREFIX;?>-log-action">
                  <button type="button" class="button button-small" data-<?php echo PREFIX;?>-log-action="view_detail"><?php _e('View Detail', '{text_domain}');?></button>
               </div>
            </div>

            <div class="<?php echo PREFIX;?>-log-meta__right">
               <div class="<?php echo PREFIX;?>-log-code"><?php echo __('Code:', '{text_domain}') . ' '. $log['code'];?></div>
            </div>
         </div>
      </div>
      <?php

      return ob_get_clean();
   }




   /*
   |--------------------------------------------------------------------------
   | SETTERS
   |
   | Functions which set/update the data
   |--------------------------------------------------------------------------
   */


   /**
    * Sets the log.
    *
    * @param string $type
    * @param string $code
    * @param array $actions
    *   [
    *      'view_detail' => [
    *         'data' => [],
    *         'callback' => []
    *      ],
    *      'toggle_visibility' => [
    *         'data' => [],
    *         'callback' => []
    *      ],
    *      'remove' => [
    *         'data' => [],
    *         'callback' => []
    *      ],
    *   ]
    * @param string $path
    * @param string $line
    * @return void
    */
   protected function set_log($type, $code, $actions = [], $path = '', $line = ''){

      $logs = $this->get_logs();
      $location = $path;

      $code = $this->sanitize_key($code);

      if( ! empty($type) && ! empty($code) ){

         if( ! empty($path) && ! empty($line)  ){
            $location = $path.':'.$line;
         }

         //save the actions
         foreach($actions as $key => $value){
            if(in_array($key, $this->actions)){
               update_option(PREFIX . "_log_{$code}_{$key}", $value, false);
            }
         }

         $logs[$code] = [
            'type' => $type,
            'code' => $code,
            'read' => false,
            'path' => $location,
            'date' => time(),
         ];

         update_option(PREFIX . '_logs', $logs, false);

         $this->logs = $logs;
      }
   }



   /**
    * Sets error log.
    *
    * @param string $code
    * @param array $actions
    * @param string $path
    * @param string $line
    * @return void
    */
   public function set_error($code, $actions = [], $path = '', $line = ''){
      $this->set_log('error', $code, $actions, $path, $line, $line);
   }



   /**
    * Sets info log.
    *
    * @param string $code
    * @param array $actions
    * @param string $path
    * @param string $line
    * @return void
    */
   public function set_info($code, $actions = [], $path = '', $line = ''){
      $this->set_log('info', $code, $actions, $path, $line);
   }



   /**
    * Sets warning log.
    *
    * @param string $code
    * @param array $actions
    * @param string $path
    * @param string $line
    * @return void
    */
   public function set_warning($code, $actions = [], $path = '', $line = ''){
      $this->set_log('warning', $code, $actions, $path, $line);
   }



   /**
    * Sets debug log.
    *
    * @param string $code
    * @param array $actions
    * @param string $path
    * @param string $line
    * @return void
    */
   public function set_debug($code, $actions = [], $path = '', $line = ''){
      $this->set_log('debug', $code, $actions, $path, $line);
   }



   /**
    * Sets a log from a given connected option.
    *
    * @param string $option_key
    * @param bool $update
    * @return void
    */
   protected function set_log_from_option(string $option_key, $update = false){

      $options = $this->get_connected_options();

      if(array_key_exists($option_key, $options)){

         $option = $options[$option_key];
         $value = get_option($option_key);
         $value = Utility::is_json($value) ? json_decode($value) : $value;
         $log = $this->get_log($option['code']);
         $show_only = ( isset( $option['show_only'] ) ) ? $option['show_only'] : [];

         if( empty($value) ){

            if(isset($log['code'])){
               $this->remove_log($log['code']);
            }

         }else{

            if ( empty( $show_only ) || in_array( $value, $show_only ) ) {
               if( ($update && isset($log['code'])) || ! isset($log['code']) ){
                  $this->set_log($option['type'], $option['code'], $option['actions']);
               }
            }

         }
      }
   }



   /**
    * Updates an existing log.
    *
    * @param array $log
    * @return void
    */
   protected function update_log($log){

      if( isset($log['code']) ){

         $logs = $this->get_logs();

         if( isset( $logs[$log['code']] ) ){

            //remove the actions
            if(isset($log['actions'])){
               unset($log['actions']);
            }

            $logs[$log['code']] = $log;

            update_option(PREFIX . '_logs', $logs, false);

            $this->logs = $logs;
         }
      }
   }



   /**
    * Removes an exising log.
    *
    * @param string $code
    * @return void
    */
   public function remove_log($code){

      $code = $this->sanitize_key($code);
      $logs = $this->get_logs();

      if( isset( $logs[$code] ) ){

         $log = $logs[$code];
         $remove_action = Utility::rgars($log, 'actions/remove');

         if( isset($remove_action['callback']) ){

            $data = Utility::rgar($remove_action, 'data');

            call_user_func_array($remove_action['callback'], [$data]);
         }

         //remove the option if the codes match
         foreach($this->get_connected_options() as $option_key => $option){
            if($code === $option['code']){
               delete_option($option_key);
            }
         }

         //remove the actions
         foreach($this->actions as $key){
            delete_option(PREFIX . "_log_{$code}_{$key}");
         }

         unset($logs[$code]);

         update_option(PREFIX . '_logs', $logs, false);

         $this->logs = $logs;
      }
   }




   /*
   |--------------------------------------------------------------------------
   | RENDERS
   |
   | Functions whose HTML output is displayed directly
   |--------------------------------------------------------------------------
   */


   /**
    * Displays the logs.
    *
    * @return string
    */
   protected function render_logs(){

      $logs = $this->get_logs();

      $paged = isset($_GET['log_page']) ? (int) $_GET['log_page'] : 0;
      $offset = $paged > 0 ? $this->per_page * ($paged - 1) : 0;

      $logs = array_slice($logs, $offset, $this->per_page);

      if( empty($logs) ){

         echo '<div>'.$this->get_no_results_text(),'</div>';

      }else{

         $this->render_actions();

         foreach($logs as $log){
            echo $this->get_template($log);
         }

         $this->render_pagination();

      }

   }



   /**
    * Displays the pagination.
    *
    * @return string
    */
   protected function render_pagination(){

      $total = count($this->logs);
      $pages = ceil($total / $this->per_page);

      if($pages > 1):
      ?>
      <ul class="<?php echo PREFIX;?>-pagination">
         <?php for($page = 1; $page <= $pages; $page++):
            $current = Utility::rgar($_GET, 'log_page')
            ?>
            <li>
               <?php if( (empty($current) && $page == 1) || $current == $page):?>
                  <span class="button button-small disabled"><?php echo $page;?></span>
               <?php else:?>
                  <a class="button button-small" href="<?php echo SETTINGS_URL .'&section=logs&log_page='.$page;?>"><?php echo $page;?></a>
               <?php endif;?>
            </li>
         <?php endfor;?>
      </ul>
      <?php
      endif;
   }



   /**
    * Displayes the action buttons.
    *
    * @return string
    */
   protected function render_actions(){

      ?>
      <div class="<?php echo PREFIX;?>-log-actions">
         <div class="<?php echo PREFIX;?>-log-actions__left">
            <button type="button" class="button button-small" data-<?php echo PREFIX;?>-log-action="select_all"><?php _e('Select / unselect all', '{text_domain}');?></button>
         </div>
         <div class="<?php echo PREFIX;?>-log-actions__right">
            <button type="button" class="button button-small" disabled="disabled" data-<?php echo PREFIX;?>-log-action="toggle_visibility"><?php _e('Mark as read / unread', '{text_domain}');?></button>
            <button type="button" class="button button-small" disabled="disabled" data-<?php echo PREFIX;?>-log-action="remove"><?php _e('Remove', '{text_domain}');?></button>
         </div>
      </div>
      <?php
   }



   /**
    * Displays the notification box.
    *
    * @return string
    */
   public function render_notification(){

      $active = 0;
      $logs = $this->get_logs();

      foreach($logs as $log){
         if( in_array($log['type'], ['error', 'warning', 'info']) && ! $log['read'] ){
            $active = $active + 1;
         }
      }

      if($active == 0 || 'logs' === Utility::rgar($_GET, 'section')){
         return;
      }

      ?>
      <div class="<?php echo PREFIX;?>-log-notification" style="display: none;">
         <div class="<?php echo PREFIX;?>-log-notification__name"><?php echo PLUGIN_NAME;?></div>
         <p><?php _e('There are new logs detected!', '{text_domain}');?><br/><a href="<?php echo SETTINGS_URL;?>&section=logs"><?php _e('Click to view', '{text_domain}');?></a></p>
         <div class="<?php echo PREFIX;?>-log-notification__close">
            <span class="dashicons dashicons-dismiss"></span>
         </div>
      </div>
      <?php
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
            'handle' => PREFIX . '_logger_js',
            'src' => PLUGIN_URL . '/assets/js/logger.js',
            'deps' => ['jquery'],
            'version' => PLUGIN_VERSION,
         ]
      ]);

      $files['admin']['styles'] = array_merge($files['admin']['styles'], [
         [
            'handle' => PREFIX . '_logger_css',
            'src' => PLUGIN_URL . '/assets/css/logger.css',
            'deps' => [],
            'version' => PLUGIN_VERSION,
         ]
      ]);

      return $files;
   }



   /**
    * Sets/unsets admin logs based on certain conditions.
    *
    * @return void
    */
   public function process_admin_logs(){

      $api = API::instance();

      if( $api->is_authorized() ){
         $this->remove_log('not_authorized');
      }else{
         $this->set_warning('not_authorized', [], __FILE__, __LINE__);
      }

      if($this->max_exec_time > 0){
         if(ini_get('max_execution_time') < $this->max_exec_time){
            $this->set_warning('max_exec_time', [], __FILE__, __LINE__);
         }else{
            $this->remove_log('max_exec_time');
         }
      }
   }



   /**
    * Processes the AJAX requested by a action button.
    *
    * @return string
    */
   public function process_log_action(){

      //check to make sure the request is from same server
      if(!check_ajax_referer( 'wsa-nonce', 'security', false )){
         return;
      }

      $action = Utility::rgar($_REQUEST, 'log_action');

      if('view_detail' === $action){

         $code = Utility::rgar($_REQUEST, 'log_code');
         $log = $this->get_log($code);

         if(isset($log['code']) && array_key_exists('view_detail', $log['actions'])){

            $view_detail = $log['actions']['view_detail'];
            $data = array_key_exists('data', $view_detail) ? $view_detail['data'] : '';

            if(isset($view_detail['callback'])){

               call_user_func_array($view_detail['callback'], [$data]);

            }else{

               $output = is_string($data) ? $data : '<pre>'.print_r($data, 1).'</pre>';

               echo $output;
            }

         }else{

            echo '<h3>'.__('No detail available :(', '{text_domain}').'</h3>';
         }

         exit;
      }


      if('toggle_visibility' === $action){

         $codes = Utility::rgar($_REQUEST, 'log_codes');

         foreach($codes as $code){
            $log = $this->get_log($code);

            if(isset($log['code'])){
               $log['read'] = !$log['read'];
               $this->update_log($log);
            }
         }
      }

      if('remove' === $action){

         $codes = Utility::rgar($_REQUEST, 'log_codes');

         foreach($codes as $code){
            $log = $this->get_log($code);
            $this->remove_log($log['code']);
         }
      }

      wp_send_json_success([
         'action' => $action,
      ]);
   }



   /**
    * Check if connected options are saved in the logs.
    *
    * @return void
    */
   public function check_connected_options(){

      foreach($this->get_connected_options() as $key => $option){
         $this->set_log_from_option($key);
      }
   }



   /**
    * Sets the log when a connected option is added/updated.
    *
    * @param string $option_name
    * @return void
    */
   public function process_connected_options( $option_name ) {
      $this->set_log_from_option($option_name, true);
   }



   /**
    * Replace all special charanters (few exceptions) with underscore.
    *
    * @param string $string
    * @return string
    */
   protected function sanitize_key($string){

      if(wc_is_valid_url($string)){
         $string = parse_url($string);
         $string = Utility::rgar($string, 'path').Utility::rgar($string, 'query');
         $string = trim($string, '/');
      }
      $string = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $string);
      $string = trim($string, '_');

      return $string;
   }



}