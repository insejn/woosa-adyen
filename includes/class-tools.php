<?php
/**
 * Tools
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Tools extends Abstract_Tools{


   /**
    * The default list of tools.
    *
    * @since 1.1.3
    * @return array
    */
	protected function default_tools(){

      $tools = parent::default_tools();

      $tools['generate_client_key'] = [ $this, 'process_generate_client_key' ];
      $tools['clear_admin_errors'] = [ $this, 'process_clear_admin_errors' ];

      return $tools;
   }



   /**
    * Process the clear cache tool. This is triggered when the clear cache tool is running.
    *
    * @return void
    */
   public function process_clear_cache(){
      Settings::clear_cached_payment_methods();
   }



   /**
    * Generates a client key for the current domain.
    *
    * @since 1.1.3
    * @return void
    */
   public function process_generate_client_key(){

      if( 'yes' === Utility::rgar($_GET, 'generated_client_key') ){
         Utility::show_notice(
            sprintf(
               __('The client key for %s has been generated!', 'woosa-adyen' ),
               '<code>'.API::get_origin_domain().'</code>'
            )
         , 'success');
      }

      if( 'yes' === Utility::rgar($_GET, 'generate_client_key') &&
         'adyen' === Utility::rgar($_GET, 'tab') &&
         wp_verify_nonce( Utility::rgar($_GET, '_wpnonce'), 'wsa-nonce' )
      ){

         API::generate_origin_keys();

         wp_redirect( SETTINGS_URL . '&section=tools&generated_client_key=yes' );
      }
   }



   /**
    * Displays 'generate_client_ke' section.
    *
    * @since 1.1.3
    * @return string
    */
   public function generate_client_key__template(){
      ?>
      <tr>
         <td>
            <b><?php _e('Generate client key', 'woosa-adyen');?></b>
            <p class="description"><?php _e('This will generate a client key for the current domain.', 'woosa-adyen');?></p>
         </td>
         <td style="text-align: right;">
            <a href="<?php echo SETTINGS_URL . '&section='.$this->section.'&generate_client_key=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' );?>" class="button"><?php _e('Generate', 'woosa-adyen');?></a>
         </td>
      </tr>
      <?php
   }



   /**
    * Displays 'clear_admin_errors' section.
    *
    * @since 1.1.3
    * @return string
    */
   public function process_clear_admin_errors(){

      if( 'yes' === Utility::rgar($_GET, 'admin_errors_cleared') ){
         Utility::show_notice(__('The admin errors have been removed!', 'woosa-adyen' ), 'success');
      }

      if(
         'yes' === Utility::rgar($_GET, 'clear_admin_errors') &&
         'adyen' === Utility::rgar($_GET, 'tab') &&
         wp_verify_nonce( Utility::rgar($_GET, '_wpnonce'), 'wsa-nonce' )
      ){
         delete_option(PREFIX.'_errors');

         wp_redirect( SETTINGS_URL . '&section=tools&admin_errors_cleared=yes' );
      }
   }



   /**
    * Displays 'clear_admin_errors' section.
    *
    * @since 1.1.3
    * @return string
    */
   public function clear_admin_errors__template(){
      ?>
      <tr>
         <td>
            <b><?php _e('Clear Adyen errors', 'woosa-adyen');?></b>
            <p class="description"><?php _e('This will clear all Adyen API errors displayed in admin area.', 'woosa-adyen');?></p>
         </td>
         <td style="text-align: right;">
            <a href="<?php echo SETTINGS_URL . '&section='.$this->section.'&clear_admin_errors=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' );?>" class="button"><?php _e('Clear', 'woosa-adyen');?></a>
         </td>
      </tr>
      <?php
   }


}