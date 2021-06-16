<?php
/**
 * Abstract Bulk Action
 *
 * This extends and processes bulk action field.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Bulk_Action{


   /**
    * On which post type(s) to insert the bulk action.
    *
    * @var array
    */
   protected $post_types = [];


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
		if ( null == static::$instance ) {
			static::$instance = new static;
		}

		return static::$instance;
   }



   /**
    * Init hook actions.
    *
    * @since 1.0.0
    * @return void
    */
   public function init_hooks(){

      if(is_array($this->post_types)){

         foreach($this->post_types as $post_type){

            add_filter('bulk_actions-edit-'.$post_type, [$this, 'bulk_actions']);
            add_action('handle_bulk_actions-edit-'.$post_type, [$this, 'handle_bulk_actions'], 10, 3);
         }

      }

   }



   /**
    * Adds new actions in bulk actions list
    *
    * @since 1.0.0
    * @param array $items
    * @return $items
    */
   public function bulk_actions($items) {

      if(API::instance()->is_authorized()){

         foreach($this->actions() as $item){
            $items[$item['name']] = $item['label'];
         }
      }

      return $items;
   }



   /**
    * Handles the new added bulk actions.
    *
    * @since 1.0.0
    * @param string $redirect_to
    * @param string $doaction
    * @param array $post_ids
    * @return string
    */
   public function handle_bulk_actions($redirect_to, $doaction, $post_ids){

      if( $this->has_action($doaction) ){
         $this->perform($post_ids, $doaction);
      }

      return $redirect_to;

   }



   /**
    * List of available actions.
    *
    * @since 1.0.0
    * @return array
    * [
    *    [
    *       'name' => PREFIX . '_custom_option',
    *       'schedulable' => true,
    *       'callback' => [],
    *       'validate_args' => [],
    *       'label' => 'Custom option',
    *    ]
    * ]
    */
   public function actions(){
      return [];
   }



   /**
    * Gets an action.
    *
    * @since 1.0.0
    * @param string $name
    * @return false|array
    */
   public function get_action($name){

      $list = $this->actions();
      $search = array_search($name, array_column($list, 'name'));

      if($search !== false){
         return $list[$search];
      }

      return false;
   }



   /**
    * Checks whether or not the action is available.
    *
    * @since 1.0.0
    * @param string $name
    * @return boolean
    */
   public function has_action($name){
      return $this->get_action($name);
   }



   /**
    * Performs a given action.
    *
    * @since 1.0.0
    * @param int|array $items
    * @param string $action_name
    * @return void
    */
   public function perform($items, $action_name){

      $action = $this->get_action($action_name);
      $items = array_filter((array) $items);

      if($action){

         $args = [
            'items' => $items,
         ];

         $args = isset($action['validate_args']) ? call_user_func_array($action['validate_args'], $args) : $args;

         if( isset($action['callback']) && ! empty($args['items']) ){

            if($action['schedulable']){

                  $as = new Action_Scheduler('bulk-action');
                  $as->set_single_action(
                     [
                        'name' => $action['name'],
                        'callback' => $action['callback']
                     ],
                     $args
                  );

            }else{
               call_user_func_array($action['callback'], $args);
            }
         }

      }else{

         $logger = new Logger;

         $logger->set_error("wrong_action_supplied", [
            'view_detail' => [
               'data' => [
                  'TITLE' => '====== WRONG ACTION ======',
                  'DESCRIPTION' => 'The supplied action is not valid.',
                  'DATA' => [
                     'action_name' => $action_name
                  ],
               ]
            ]
         ], __FILE__, __LINE__);
      }
   }


}