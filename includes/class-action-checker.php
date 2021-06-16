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


class Action_Checker extends Abstract_Action_Checker{

   /**
    * The list of flags and their messages.
    *
    * @since 1.0.0
    * @return array
    */
   public function flags(){
      return [
         [
            'flag' => 'my_flag_name',
            'message' => __('The message goes here.', '{text_domain}'),
         ],
      ];
   }

}