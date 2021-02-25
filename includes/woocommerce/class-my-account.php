<?php
/**
 * My account
 *
 * This class extends WooCommerce my account page.
 *
 * @package Woosa-Adyen/WooCommerce
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class My_Account{

   /**
    * The instance of this class.
    *
    * @since 1.0.0
    * @var null|object
    */
   protected static $instance = null;


	/**
	 * Returns an instance of this class.
	 *
	 * @since 1.0.0
	 * @return object A single instance of this class.
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
   }



   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    */
   public function __construct(){

      add_filter('woocommerce_account_menu_items', [__CLASS__, 'menu_items'], 10, 1);
      add_action('init', [__CLASS__, 'endpoints']);
      add_action('woocommerce_account_stored-cards_endpoint', [__CLASS__, 'stored_cards_content']);
   }



   /**
    * Adds new menu item
    *
    * @since 1.0.3
    * @param array $items
    * @return array
    */
   public static function menu_items( $items ) {

      $new_items = [];

      foreach ($items as $key => $value) {
         $new_items[$key] = $value;
         if( 'edit-account' === $key){
            $new_items['stored-cards'] = __( 'Stored Cards', 'adyen' );
         }
      }

      return $new_items;

   }



   /**
    * Adds new page endpoint.
    *
    * @since 1.0.3
    * @return void
    */
   public static function endpoints() {

      add_rewrite_endpoint( 'stored-cards', \EP_PAGES );

   }



   /**
    * Renders the content of stored cards.
    *
    * @since 1.0.3
    * @return void
    */
   public static function stored_cards_content() {

      $cards = API::get_ec_stored_cards();

      ?>

      <h3><?php _e('Stored Creditcards', 'woosa-adyen');?></h3>

      <?php if( empty($cards) ):?>

         <p><?php _e('There are not stored cards yet.', 'woosa-adyen');?></p>

      <?php else:?>

         <div class="<?php echo PREFIX;?>-list-cards">

            <?php foreach($cards as $item):?>
               <div class="<?php echo PREFIX;?>-list-cards__item">
                  <div class="<?php echo PREFIX;?>-card-details">
                     <div><img src="https://checkoutshopper-test.adyen.com/checkoutshopper/images/logos/<?php echo $item['brand'];?>.svg" title="<?php echo $item['name'];?>" alt="<?php echo $item['name'];?>"></div>
                     <div class="<?php echo PREFIX;?>-card-details__number">************<?php echo $item['lastFour'];?></div>
                     <div><?php printf(__('Expires: %s', 'adyen'), "{$item['expiryMonth']}/{$item['expiryYear']}");?></div>
                     <div><?php echo $item['holderName'];?></div>
                     <div class="<?php echo PREFIX;?>-card-details__remove" data-remove-sci="<?php echo $item['id'];?>" title="<?php _e('Remove this card', 'woosa-adyen');?>"><span class="dashicons dashicons-no-alt"></span></div>
                  </div>
               </div>
            <?php endforeach;?>

         </div>

      <?php endif;?>

      <?php
   }


}