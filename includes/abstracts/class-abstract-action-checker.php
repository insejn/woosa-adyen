<?php
/**
 * Action Checker
 *
 * Abstract class for checking in progress actions.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


abstract class Abstract_Action_Checker{

   /**
    * The current message.
    *
    * @var string
    */
   protected $message = '';


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
		if ( null == Action_Checker::$instance ) {
			Action_Checker::$instance = new Action_Checker;
		}

		return Action_Checker::$instance;
   }



   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action(PREFIX . '\on_deactivation', [$this, 'reset_flags']);

   }



   /**
    * The list of flags and their messages.
    *
    * @return array
    */
   protected function flags(){
      return [];
   }



   /**
    * Checks whether or not there is an action in progress by checking the flags.
    *
    * @return bool
    */
   public function in_progress(){

      foreach($this->flags() as $item){

         $flag = get_option( Utility::prefix($item['flag']) );

         if('in_progress' === $flag || true === $flag){
            $this->message = $item['message'];
            return true;
         }

      }

      return false;
   }



   /**
    * Retrieves the current info message set by a specific flag.
    *
    * @return string
    */
   public function get_info_message(){
      return $this->message;
   }



   /**
    * Retrieves the default message.
    *
    * @return string
    */
   public function get_default_message(){
      return __('Sorry, no action is allowed until the current one is finished!', '{text_domain}');
   }



   /**
    * Removes the flags to reset the action message.
    *
    * @return void
    */
   public function reset_flags(){

      foreach($this->flags() as $item){
         delete_option( Utility::prefix($item['flag']) );
      }
   }

}