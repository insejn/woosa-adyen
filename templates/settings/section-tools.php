<?php
/**
 *
 * @since 1.1.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;

?>

<tr>
   <td style="padding: 0;">
      <div>
         <h3><?php _e('Generate client key', 'woosa-adyen');?></h3>
         <p><em><?php _e('This will generate a client key for the current domain.', 'woosa-adyen');?></em></p>
         <p><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=adyen&section=tools&generate_client_key=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' ));?>" class="button"><?php _e('Click to generate', 'woosa-adyen');?></a></p>
         <hr/>
      </div>
      <div>
         <h3><?php _e('Clear cache', 'woosa-adyen');?></h3>
         <p><em><?php _e('This will clear all caching data.', 'woosa-adyen');?></em></p>
         <p><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=adyen&section=tools&clear_cache=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' ));?>" class="button"><?php _e('Click to clear', 'woosa-adyen');?></a></p>
         <hr/>
      </div>
      <div>
         <h3><?php _e('Clear Adyen errors', 'woosa-adyen');?></h3>
         <p><em><?php _e('This will clear all Adyen API errors displayed in admin area.', 'woosa-adyen');?></em></p>
         <p><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=adyen&section=tools&clear_admin_errors=yes&_wpnonce='.wp_create_nonce( 'wsa-nonce' ));?>" class="button"><?php _e('Click to clear', 'woosa-adyen');?></a></p>
         <hr/>
      </div>

      <style>
      p.submit{
         display: none;
      }
      </style>
   </td>
</tr>