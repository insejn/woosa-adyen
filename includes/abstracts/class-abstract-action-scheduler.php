<?php
/**
 * Action Scheduler
 *
 * Abstract class for working with Action Scheduler
 *
 * @version 1.0.0
 * @author Woosa Team
 * @link https://actionscheduler.org/
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Action_Scheduler{


   /**
    * Delay interval in seconds
    *
    * @var integer
    */
   protected $delay = 60;


   /**
    * Group name
    *
    * @var string
    */
   protected $group = PREFIX . '-action';


   /**
    * Whether or not to check if the action already exists by args and group as well.
    *
    * @var bool
    */
   protected $full_check = false;


   /**
    * Construct of this class.
    *
    */
   public function __construct($group = '', $delay = '', $full_check = false){

      $this->group      = empty($group) ? $this->group : Utility::prefix($group, true);
      $this->delay      = $delay === '' ? $this->delay : $delay;
      $this->full_check = $full_check;

   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public static function init_hooks(){

      add_action(PREFIX . '\on_activation', [ Action_Scheduler::class, 'on_activation']);
      add_action(PREFIX . '\on_deactivation', [ Action_Scheduler::class, 'on_deactivation']);

      foreach(self::get_actions() as $name => $callback){
         if ( class_exists( $callback[0] ) && method_exists( $callback[0], $callback[1] ) ) {
            $class = new $callback[0];//init the class
            $function = $callback[1];
            add_action($name, [$class, $function], 10, 2);
         }
      }
   }



   /**
    * Runs at plugin activation
    *
    * @return void
    */
   public static function on_activation() {
   }



   /**
    * Run the deactivation script
    *
    * @return void
    */
   public static function on_deactivation() {
      self::unschedule_all_actions();
   }



   /**
    * Gets the list of actions.
    *
    * @return array
    */
   public static function get_actions(){
      return array_filter((array) get_option( Utility::prefix('action_scheduler') ));
   }



   /**
    * Removes the list of actions.
    *
    * @return array
    */
   public static function clear_actions(){
      delete_option( Utility::prefix('action_scheduler'));
   }



   /**
    * Saves the action in a list.
    *
    * @param array $action
    * [
    *    'name' => 'action_name',
    *    'callback' => ['class', 'function']
    * ]
    * @return void
    */
   protected function save_action(array $action){

      if(array_key_exists('name', $action) && array_key_exists('callback', $action)){

         //we force to have only the name of the class (which should be a string) but not the instance of it
         if(is_string($action['callback'][0])){

            $actions = self::get_actions();

            if( ! isset( $actions[Utility::prefix($action['name'])] ) ){

               $actions[Utility::prefix($action['name'])] = $action['callback'];

               update_option( Utility::prefix('action_scheduler'), $actions, false );
            }

            return true;

         }else{

            throw new \InvalidArgumentException('The $action["callback"][0] must be the name of the class but not the instance');
         }
      }

      return false;
   }



   /**
    * Schedules an action to run one time at some defined point in the future.
    *
    * @param array $action
    * @param array $args
    * @return void
    */
   public function set_single_action(array $action, array $args){

      if( ! $this->is_scheduled($action, $args) ){

         $saved = $this->save_action($action);

         if($saved){
            as_schedule_single_action( time() + $this->delay, Utility::prefix($action['name']), $args, $this->group );
         }

      }
   }



   /**
    * Schedules an action to run one time, as soon as possible.
    *
    * @param array $action
    * @param array $args
    * @return void
    */
   public function set_async_action(array $action, array $args){

      if( ! $this->is_scheduled($action, $args) ){

         $saved = $this->save_action($action);

         if($saved){
            as_enqueue_async_action( Utility::prefix($action['name']), $args, $this->group );
         }

      }
   }



   /**
    * Schedules an action to run repeatedly with a specified interval in seconds.
    *
    * @param array $action
    * @param array $args
    * @param integer $interval
    * @return void
    */
   public function set_recurring_action(array $action, array $args, int $interval){

      if( ! $this->is_scheduled($action, $args) ){

         $saved = $this->save_action($action);

         if($saved){
            as_schedule_recurring_action( time() + $this->delay, $interval, Utility::prefix($action['name']), $args, $this->group );
         }
      }
   }



   /**
    * Checks whether or not an action is already scheduled
    *
    * @param array $action
    * @param array $args
    * @return boolean
    */
   public function is_scheduled(array $action, array $args){
      return $this->full_check ? as_next_scheduled_action( Utility::prefix($action['name']), $args, $this->group ) : as_next_scheduled_action( Utility::prefix($action['name']) );
   }



   /**
    * Unschedule all saved actions
    *
    * @return void
    */
   public static function unschedule_all_actions() {
      foreach ( self::get_actions() as $name => $callback ) {
         self::unschedule_actions( '', $name );
      }
      self::clear_actions();
   }



   /**
    * Unschedule actions by group, hook or args
    *
    * @param string $group
    * @param string $hook
    * @param array $args
    * @return void
    */
   public static function unschedule_actions( $group, $hook = '', $args = [] ) {

      if ( ! empty( $hook ) ) {
         $hook = Utility::prefix( $hook );
      }

      if ( ! empty( $group ) ) {
         $group = Utility::prefix( $group, true );
      }

      as_unschedule_all_actions( $hook, $args, $group );
   }



   /**
    * Get action ids by date and/or status
    *
    * @param integer $limit
    * @param DateTime $date
    * @param string $status
    * @return void
    */
   public function get_action_ids( $limit = 100, DateTime $date = null, $status = 'pending' ) {

      $actions = [];

      // Ensure the group exists before continuing.
      if ( term_exists( $this->group, 'action-group' ) ) {

         $args = [
            'status' => $status,
            'per_page' => $limit,
         ];

         if ( $date !== null ) {
            $args[ 'date' ] = $date->format( 'Y-m-d H:i' );
            $args[ 'date_compare' ] = '>=';
         }

         $actions = as_get_scheduled_actions( $args, 'ids' );
      }

      return $actions;

   }

}