<?php
/**
 * Assets class
 *
 * This is responsible for enqueuing JS and CSS files.
 *
 * @package Woosa-Adyen
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Assets{


   /**
    * Hooks enqueuing JS/CSS.
    *
    * @since 1.0.0
    * @return void
    */
   public static function enqueue(){

      //--- CSS
      add_action('admin_enqueue_scripts', [__CLASS__ , 'admin_styles'], 9999);
      add_action('wp_enqueue_scripts', [__CLASS__ , 'frontend_styles'], 9999);

      //--- JS
      add_action('admin_enqueue_scripts', [__CLASS__ , 'admin_scripts'], 9999);
      add_action('wp_enqueue_scripts', [__CLASS__ , 'frontend_scripts'], 9999);

   }



   /**
    * Enqueues styles in admin.
    *
    * @since 1.0.0
    * @return void
    */
   public static function admin_styles(){

      wp_enqueue_style(
         'woosa_'.PREFIX . '_admin',
         PLUGIN_URL .'/assets/css/admin.css',
         array(),
         PLUGIN_VERSION
      );

   }



   /**
    * Enqueues scripts in admin.
    *
    * @since 1.0.10 - register scripts first and then localize and enqueue
    * @since 1.0.0
    * @return void
    */
    public static function admin_scripts(){

      self::register_admin_scripts();


      wp_localize_script('woosa_'.PREFIX . '_admin', 'woosa_'.PREFIX, array(
         'ajax' => array(
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wsa-nonce' )
         ),
         'prefix' => PREFIX,
         'translation' => [
            'processing' => __('Processing...', 'woosa-adyen'),
            'perform_action' => __('Are you sure you want to perform this action?', 'woosa-adyen'),
         ]
      ));

      wp_enqueue_script('woosa_'.PREFIX . '_admin');
   }



   /**
    * Enqueues styles in frontend.
    *
    * @since 1.1.1 - update Adyen style to 3.12.1
    * @since 1.0.6 - enqueue styles only where it's necessary
    * @since 1.0.0
    * @return void
    */
   public static function frontend_styles(){

      if(is_checkout()){

         wp_enqueue_style( 'dashicons' );

         wp_enqueue_style(
            'adyen_css_component',
            PLUGIN_URL .'/assets/css/adyen.min.css',
            array(),
            '3.12.1'
         );

         wp_enqueue_style(
            'woosa_'.PREFIX . '_checkout',
            PLUGIN_URL .'/assets/css/checkout.css',
            array(),
            PLUGIN_VERSION
         );

      }


      if(is_account_page()){

         wp_enqueue_style(
            'woosa_'.PREFIX . '_frontend',
            PLUGIN_URL .'/assets/css/frontend.css',
            array(),
            PLUGIN_VERSION
         );
      }

   }



   /**
    * Enqueues scripts in frontend.
    *
    * @since 1.1.0  - remove `init_googlepay` script
    * @since 1.0.10 - limit `wp_localize_script()` only on needed pages
    *               - register scripts first and then enqueue - to fix WP 5.5 issue
    * @since 1.0.6  - enqueue scripts only where it's necessary
    *               - do not allow storing cards for guest users
    * @since 1.0.5  - insert script only on checkout page
    *               - add a falback for the main dependency script
    * @since 1.0.0
    * @return void
    */
   public static function frontend_scripts(){

      self::register_frontend_scripts();


      if(is_checkout() || is_account_page()){

         $handle = is_checkout() ? 'adyen_js_component' : 'woosa_'.PREFIX . '_frontend';

         $payment_methods = API::get_response_payment_methods();
         $payment_methods['storedPaymentMethods'] = API::get_ec_stored_cards();
         $store_card = Checkout::has_subscription() ? false : true;
         $store_card = is_user_logged_in() ? $store_card : false;

         wp_localize_script( $handle, 'woosa_'.PREFIX, array(
            'ajax' => array(
               'url' => admin_url( 'admin-ajax.php' ),
               'nonce' => wp_create_nonce( 'wsa-nonce' ),
            ),
            'prefix' => PREFIX,
            'debug' => DEBUG,
            'checkout_url' => wc_get_checkout_url(),
            'locale' => get_locale(),
            'api' => [
               'origin_key' => API::get_origin_key(),
               'test_mode' => API::account()->test_mode,
               'card_types' => API::get_card_types(),
               'response_payment_methods' => $payment_methods,
               'store_card' => $store_card,
               'adyen_merchant' => API::account()->merchant,
            ],
            'site_name' => get_bloginfo('name'),
            'currency' => get_woocommerce_currency(),
            'cart' => [
               'country' => WC()->customer->get_shipping_country(),
               'total' => WC()->cart->cart_contents_total,
            ],
            'translation' => [
               'remove_card' => __('Are you sure you want to remove this card?', 'woosa-adyen'),
               'remove_gdpr' => __('Are you sure you want to remove your personal information attached to this order payment?', 'woosa-adyen'),
               'processing' => __('Processing...', 'woosa-adyen'),
            ]
         ));

      }

      if(is_checkout()){

         wp_enqueue_script('jquery-ui-datepicker');

         wp_enqueue_script('lib_googlepay');

         wp_enqueue_script('adyen_js_component');

         wp_enqueue_script('woosa_'.PREFIX . '_checkout');
      }

      if(is_account_page()){

         wp_enqueue_script('woosa_'.PREFIX . '_frontend');
      }

   }



   /**
    * Register scripts which will be enqueued in frontend.
    *
    * @since 1.1.1 - update Adyen lib to 3.12.1
    * @since 1.1.0 - remove `init_googlepay` script
    * @since 1.0.10
    * @return void
    */
   public static function register_frontend_scripts(){

      $adyen_env = API::account()->test_mode === 'yes' ? 'test' : 'live';

      wp_register_script(
         'adyen_js_component',
         PLUGIN_URL .'/assets/js/adyen-'.$adyen_env.'.min.js',
         array('jquery', 'lib_googlepay'),
         '3.12.1',
         true
      );

      wp_register_script(
         'lib_googlepay',
         PLUGIN_URL .'/assets/js/googlepay.min.js',
         array('jquery'),
         PLUGIN_VERSION,
         true
      );

      wp_register_script(
         'woosa_'.PREFIX . '_checkout',
         PLUGIN_URL .'/assets/js/checkout.js',
         array('adyen_js_component'),
         PLUGIN_VERSION,
         true
      );

      wp_register_script(
         'woosa_'.PREFIX . '_frontend',
         PLUGIN_URL .'/assets/js/frontend.js',
         array('jquery'),
         PLUGIN_VERSION,
         true
      );

   }



   /**
    * Registers scripts which will be enqueued in admin.
    *
    * @since 1.0.10
    * @return void
    */
   public static function register_admin_scripts(){

      wp_register_script(
         'woosa_'.PREFIX . '_admin',
         PLUGIN_URL .'/assets/js/admin.js',
         array('jquery'),
         PLUGIN_VERSION,
         true
      );
   }

}
