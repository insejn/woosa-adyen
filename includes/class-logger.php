<?php
/**
 * Logger
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Logger extends Abstract_Logger{


	/**
    * Sets/unsets admin logs based on certain conditions.
    *
    * @since 1.0.0
    * @return void
    */
	public function process_admin_logs(){
		parent::process_admin_logs();

        if(empty(API::get_origin_key())){
            $this->set_warning('no_client_key', [], __FILE__, __LINE__);
        }else{
            $this->remove_log('no_client_key');
        }
	}



   /**
    * The default list of messages.
    *
    * @since 1.0.0
    * @return array
    */
	protected function get_default_messages(){

        $messages = parent::get_default_messages();

        $messages['no_client_key'] = sprintf(
            __('The client key for %s is missing, please %sgo to this page%s to generate one.', 'woosa-adyen'),
            '<code>'.API::get_origin_domain().'</code>',
            '<a href="'.SETTINGS_URL .'&section=tools">',
            '</a>'
        );

      return $messages;
   }



   /**
    * The default list of options which are connected with the logger.
    *
    * @since 1.0.0
    * @return array
    */
	protected function get_default_connected_options(){

		$options = parent::get_default_connected_options();

		return $options;
	}


}