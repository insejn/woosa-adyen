<?php
/**
 * Errors class
 *
 * This is handling errors displayed to admin.
 *
 * @package Woosa-Adyen/Utility
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Errors{


   /**
    * Saves error that will be displayed to admin.
    *
    * @since 1.0.0
    * @param string $error
    * @return void
    */
    public static function save($error, $key){

      $list = get_option(PREFIX.'_errors', array());

      $list[$key] = $error;

      $list = array_filter(array_unique($list));

      update_option(PREFIX.'_errors', $list);

   }



   /**
    * Deletes a given error.
    *
    * @since 1.0.0
    * @param string $error
    * @return void
    */
   public static function delete($key){

      $list = get_option(PREFIX.'_errors', array());

      unset($list[$key]);

      update_option(PREFIX.'_errors', $list);

   }



   /**
    * Displays errors.
    *
    * @since 1.0.0
    * @return void
    */
   public static function display(){

      $list = get_option(PREFIX.'_errors', array());

      if(count($list) > 0){
         $li = '';
         foreach($list as $item){
            $li .= '<br/>&bull; '.$item;
         }

         Utility::show_notice(sprintf(__('We found some errors: %s', 'woosa-adyen'), $li));
      }
   }
}