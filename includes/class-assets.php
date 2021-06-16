<?php
/**
 * Assets
 *
 * This is responsible for registering and enqueuing JS/CSS files.
 *
 * @version 1.0.0
 * @author Woosa Team
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Assets extends Abstract_Assets{


   /**
    * Initiates hooks.
    *
    * @return void
    */
   public function init_hooks(){

      add_action('admin_enqueue_scripts', [$this , 'process_admin_files'], 999);
      add_action('wp_enqueue_scripts', [$this , 'process_frontend_files'], 999);

      add_action('admin_init', [$this, 'process_view_files']);
      add_action('wp', [$this, 'process_view_files']);
   }



   /**
    * The default list of style and script files.
    *
    * @since 1.1.3 - update Web component to v4.4.0
    * @since 1.0.0
    * @return array
    */
	protected function get_default_files(){

      $files = parent::get_default_files();
      $adyen_env = API::account()->test_mode === 'yes' ? 'test' : 'live';
      $l_handle = is_checkout() ? 'adyen_js_component' : 'woosa_'.PREFIX . '_frontend';
      $payment_methods = API::get_response_payment_methods();
      $payment_methods['storedPaymentMethods'] = API::get_ec_stored_cards();
      $store_card = Checkout::has_subscription() ? false : true;
      $store_card = is_user_logged_in() ? $store_card : false;

      $localize_script = [
         'handle'      => $l_handle,
         'object_name' => 'woosa_' . PREFIX,
         'data'        => [
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
               'country' => is_checkout() ? WC()->customer->get_shipping_country() : '',
               'total' => is_checkout() ? WC()->cart->cart_contents_total : '',
            ],
            'translation' => [
               'remove_card' => __('Are you sure you want to remove this card?', 'woosa-adyen'),
               'remove_gdpr' => __('Are you sure you want to remove your personal information attached to this order payment?', 'woosa-adyen'),
               'processing' => __('Processing...', 'woosa-adyen'),
            ]
         ]
      ];

      $localize_adyen_js_component = is_checkout() ? $localize_script : [];
      $localize_frontend = is_account_page() ? $localize_script : [];

      $files['admin']['scripts'] = [
         [
            'handle'    => PREFIX . '_admin',
            'src'       => PLUGIN_URL . '/assets/js/admin.js',
            'deps'      => ['jquery'],
            'version'   => PLUGIN_VERSION,
            'in_footer' => true,
            'localize_script' => [
               'handle'      => PREFIX . '_admin',
               'object_name' => 'woosa_' . PREFIX,
               'data'        => [
                  'ajax' => array(
                     'url'   => admin_url( 'admin-ajax.php' ),
                     'nonce' => wp_create_nonce( 'wsa-nonce' )
                  ),
                  'prefix' => PREFIX,
                  'translation' => [
                     'processing'     => __('Processing...', '{text_domain}'),
                     'perform_action' => __('Are you sure you want to perform this action?', 'woosa-adyen'),
                  ],
               ]
            ],
         ],
      ];

		$files['frontend']['styles'] = [
         [
            'handle'   => 'dashicons',
            'register' => false,
            'enqueue'  => is_checkout(),
         ],
         [
            'handle'  => 'adyen_css_component',
            'src'     => PLUGIN_URL .'/assets/css/adyen.min.css',
            'deps'    => [],
            'version' => '4.4.0',
            'enqueue' => is_checkout(),
         ],
         [
            'handle'  => 'woosa_'.PREFIX . '_checkout',
            'src'     => PLUGIN_URL .'/assets/css/checkout.css',
            'deps'    => [],
            'version' => PLUGIN_VERSION,
            'enqueue' => is_checkout(),
         ],
         [
            'handle'  => 'woosa_'.PREFIX . '_frontend',
            'src'     => PLUGIN_URL .'/assets/css/frontend.css',
            'deps'    => [],
            'version' => PLUGIN_VERSION,
            'enqueue' => is_account_page(),
         ],
      ];

		$files['frontend']['scripts'] = [
         [
            'handle'   => 'jquery-ui-datepicker',
            'register' => false,
            'enqueue'  => is_checkout(),
         ],
         [
            'handle'          => 'adyen_js_component',
            'src'             => PLUGIN_URL .'/assets/js/adyen-'.$adyen_env.'.min.js',
            'deps'            => ['jquery'],
            'version'         => '4.4.0',
            'enqueue'         => is_checkout(),
            'localize_script' => $localize_adyen_js_component,
            'in_footer'       => true
         ],
         [
            'handle'    => 'woosa_'.PREFIX . '_checkout',
            'src'       => PLUGIN_URL .'/assets/js/checkout.js',
            'deps'      => ['adyen_js_component'],
            'version'   => PLUGIN_VERSION,
            'enqueue'   => is_checkout(),
            'in_footer' => true
         ],
         [
            'handle'          => 'woosa_'.PREFIX . '_frontend',
            'src'             => PLUGIN_URL .'/assets/js/frontend.js',
            'deps'            => ['jquery'],
            'version'         => PLUGIN_VERSION,
            'enqueue'         => is_account_page(),
            'localize_script' => $localize_frontend,
            'in_footer'       => true
         ],
      ];

		return $files;
	}

}
