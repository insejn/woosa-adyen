<?php
/**
 * Bancontact payment method
 *
 * This class creates Bancontact payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Bancontact extends Credit_Card{


   /**
    * Constructor of this class.
    *
    * @since 1.0.10 - add support for subscriptions
    * @since 1.0.0
    */
    public function __construct(){

      Abstract_Gateway::__construct();

      $this->has_fields = true;

      $this->supports = array_merge($this->supports, [
         'subscriptions',
         'subscription_cancellation',
         'subscription_suspension',
         'subscription_reactivation',
         'subscription_amount_changes',
         'subscription_date_changes',
         // 'subscription_payment_method_change',
         // 'subscription_payment_method_change_customer',
         // 'subscription_payment_method_change_admin',
         'multiple_subscriptions'
      ]);

   }



   /**
    * List of countries where is available.
    *
    * @since 1.1.0
    * @return array
    */
   public function available_countries(){

      return [
         'BE' => [
            'currencies' => ['EUR'],
         ],
      ];
   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Bancontact', 'woosa-adyen');
   }



   /**
    * Gets default payment method description.
    *
    * @since 1.1.0 - display supported countries
    * @since 1.0.0
    * @return string
    */
   public function get_default_description(){
      return $this->show_supported_country();
   }



   /**
    * Gets default description set in settings.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_settings_description(){}



   /**
    * Type of the payment method (e.g ideal, scheme. bcmc).
    *
    * @since 1.0.0
    * @return string
    */
   public function payment_method_type(){
      return 'bcmc';
   }



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.0
    * @return string
    */
   public function recurring_payment_method(){
      return 'sepadirectdebit';
   }



   /**
    * Adds extra fields.
    *
    * @since 1.0.0
    * @return void
    */
    public function payment_fields() {

      Abstract_Gateway::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
    * Generates extra fields HTML.
    *
    * @since 1.1.1 - use the real method type in field names
    * @since 1.0.0
    * @return string
    */
   public function generate_extra_fields_html(){

      $type = $this->payment_method_type();
      ?>
      <div class="<?php echo PREFIX;?>-wrap-form">

         <?php $this->render_card_form();?>

         <input type="hidden" id="<?php echo PREFIX . "-{$type}-card-number";?>" name="<?php echo PREFIX . "-{$type}-card-number";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-card-exp-month";?>" name="<?php echo PREFIX . "-{$type}-card-exp-month";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-card-exp-year";?>" name="<?php echo PREFIX . "-{$type}-card-exp-year";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-card-cvc";?>" name="<?php echo PREFIX . "-{$type}-card-cvc";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-card-holder";?>" name="<?php echo PREFIX . "-{$type}-card-holder";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-sci";?>" name="<?php echo PREFIX . "-{$type}-sci";?>" />
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-store-card";?>" name="<?php echo PREFIX . "-{$type}-store-card";?>" />
      </div>
      <?php
   }


}