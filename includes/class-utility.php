<?php
/**
 * Utility class
 *
 * This is an utility class with useful methods.
 *
 * @package Woosa-Adyen/Utility
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Utility{


	/**
	 * Gets content of a given file.
	 *
	 * @since 1.0.0
	 * @param string $file
	 * @param mixed $vars
	 * @return mixed
	 */
	public static function get_tpl($file, $vars = array()){

      extract($vars);

		$content = '';

		if(file_exists($file)){

			ob_start();

			include $file;

			$content = ob_get_clean();
		}

		return $content;
	}



   /**
    * Prints array/obj in a readable format.
    *
    * @since 1.0.0
    * @param array|object $data
    * @param boolean $exit
    * @return void
    */
   public static function pr($data, $exit = false){

      echo '<pre>'.print_r($data, 1).'</pre>';

      if($exit) exit;
   }



	/**
	 * Get a specific property of an array without needing to check if that property exists.
	 *
	 * Provide a default value if you want to return a specific value if the property is not set.
	 *
	 * @since  1.0.0
	 * @param array  $array   Array from which the property's value should be retrieved.
	 * @param string $prop    Name of the property to be retrieved.
	 * @param string $default Optional. Value that should be returned if the property is not set or empty. Defaults to null.
	 * @param string $sanitize Optional. the sanitize method
	 *
	 * @return null|string|mixed The value
	 */
	public static function rgar( $array, $prop, $default = null, $sanitize = 'text' ) {

      $default = self::sanitize($default, $sanitize);

		if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof ArrayAccess ) ) {
         return $default;
		}

		if ( isset( $array[$prop] ) ) {
			$value = is_string($array[$prop]) ? self::sanitize($array[$prop], $sanitize) : $array[$prop];
		} else {
			$value = '';
		}

		return empty( $value ) && $default !== null ? $default : $value;
	}



	/**
	 * Gets a specific property within a multidimensional array.
	 *
	 * @since  1.0.1
	 * @access public
	 *
	 * @param array  $array   The array to search in.
	 * @param string $name    The name of the property to find.
	 * @param string $default Optional. Value that should be returned if the property is not set or empty. Defaults to null.
	 *
	 * @return null|string|mixed The value
	 */
	public static function rgars( $array, $name, $default = null, $sanitize = 'text' ) {

      $default = self::sanitize($default, $sanitize);

		if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof ArrayAccess ) ) {
			return $default;
		}

		$names = explode( '/', $name );
		$val   = $array;
		foreach ( $names as $current_name ) {
			$val = self::rgar( $val, $current_name, $default, $sanitize );
		}

		return $val;
   }



   /**
    * Sanitizes given string.
    *
    * @since 1.0.0
    * @param string $string
    * @param string $method
    * @return string
    */
   public static function sanitize($string, $method = 'text'){

      switch($method){

         case 'email': return sanitize_email($string); break;

         case 'key': return sanitize_key($string); break;

         case 'url': return esc_url_raw($string); break;

         case 'html_class': return sanitize_html_class($string); break;
      }

      return sanitize_text_field( $string );
   }



   /**
    * Displays admin network notice.
    *
    * @since 1.0.0
    * @param string $msg
    * @param string $type
    * @return string
    */
    public static function show_network_notice($msg, $type = 'error', $html = false){

      add_action('network_admin_notices', function() use ($msg, $type, $html){
         if($html){
            echo '<div class="wsa-notice notice notice-'.$type.'"><b>'.PLUGIN_NAME.':</b> '.$msg.'</div>';
         }else{
            echo '<div class="wsa-notice notice notice-'.$type.'"><p><b>'.PLUGIN_NAME.':</b> '.$msg.'</p></div>';
         }
      });
   }



   /**
    * Displays admin notice.
    *
    * @since 1.0.0
    * @param string $msg
    * @param string $type
    * @return string
    */
    public static function show_notice($msg, $type = 'error', $html = false){

      add_action('admin_notices', function() use ($msg, $type, $html){
         if($html){
            echo '<div class="wsa-notice notice notice-'.$type.'"><b>'.PLUGIN_NAME.':</b> '.$msg.'</div>';
         }else{
            echo '<div class="wsa-notice notice notice-'.$type.'"><p><b>'.PLUGIN_NAME.':</b> '.$msg.'</p></div>';
         }
      });
   }



   /**
    * Logs errors in a error.log file in the root of the plugin folder.
    *
    * @since 1.0.0
    * @param mixed $message
    * @param string $file
    * @param string $line
    * @return void
    */
    public static function error_log($message, $file = '', $line = ''){

      if(!is_string($message)){
         $message = print_r( $message, true );
      }
      if(!empty($file) && !empty($line)){
         $message = "{$message} thrown in {$file}:{$line}";
      }

      error_log('['.date('Y-m-d h:i:s').'] '.$message.PHP_EOL, 3, ERROR_PATH);
   }



   /**
	 * Logging method for Woocommerce.
	 *
    * @since 1.0.0
    * @param string $message
    * @param string $level Optional. Default 'info'. Possible values:
	 *                      emergency|alert|critical|error|warning|notice|info|debug.
    * @param string $file
    * @param string $line
    * @param string $source
    * @return void
    */
	public static function wc_log( $message, $level = 'info', $file = '', $line = '', $source = PLUGIN_FOLDER ) {

      if(function_exists('wc_get_logger')){

         $message = !is_string($message) ? print_r( $message, true ) : $message;

         if(!empty($file) && !empty($line)){
            $message = "{$message} thrown in {$file}:{$line}";
         }

         $log = wc_get_logger();
         $log->log( $level, $message, array( 'source' => $source ) );

      }else{

         self::error_log($message, $file, $line);
      }
	}



   /**
    * Logs errors in Woocommerce logs.
    *
    * @since 1.0.0
    * @param mixed $message
    * @param string $file
    * @param string $line
    * @return void
    */
   public static function wc_error_log($message, $file = '', $line = ''){

      if(function_exists('wc_get_logger')){

         $message = !is_string($message) ? print_r( $message, true ) : $message;

         self::wc_log($message, 'error', $file, $line);

      }else{

         self::error_log($message, $file, $line);
      }
   }



   /**
    * Logs debug info in Woocommerce logs.
    *
    * @since 1.0.0
    * @param mixed $message
    * @param string $file
    * @param string $line
    * @return void
    */
   public static function wc_debug_log($message, $file = '', $line = ''){

      if(function_exists('wc_get_logger')){

         $message = !is_string($message) ? print_r( $message, true ) : $message;

         self::wc_log($message, 'debug', $file, $line);

      }else{

         self::error_log($message, $code);
      }
   }



   /**
    * Converts an object to an array.
    *
    * @since 1.0.0
    * @param object $obj
    * @return void
    */
   public static function obj_to_arr($obj){
      return json_decode(json_encode($obj), true);
   }



   /**
    * Calculates sum of two numbers.
    *
    * @param string $x
    * @param string $y
    * @return void
    */
   public static function sum($x, $y){

      if(!is_numeric($x) || !is_numeric($y)){
         throw new \InvalidArgumentException;
      }

      return $x + $y;
   }



   /**
    * Gets the client IP
    *
    * @since 1.0.0
    * @return string
    */
   public static function get_client_ip(){

      $ip = '';

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
         $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
         $ip = $_SERVER['REMOTE_ADDR'];
      }

      return $ip;//'2.56.212.0'
   }



   /**
    * Gets local language.
    *
    * @return void
    */
   public static function get_locale(){

      if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
         $locale = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

         return $locale[0];
      }

      return str_replace('_', '-', get_locale());//get WP locale
   }



   /**
    * Checks whether or not our debug option is enabled.
    *
    * @since 1.0.6
    * @return boolean
    */
   public static function is_debug_enabled(){

      $debug = get_option(PREFIX .'_debug');

      if('yes' === $debug){
         return true;
      }

      return false;
   }


}