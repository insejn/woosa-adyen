<?php
/**
 * Credit Card payment method
 *
 * This class creates Credit Card payment method.
 *
 * @package Woosa-Adyen/WooCommerce/Payment
 * @author Woosa Team
 * @since 1.0.0
 */

namespace Woosa\Adyen;


//prevent direct access data leaks
defined( 'ABSPATH' ) || exit;


class Credit_Card extends Abstract_Gateway{


   /**
    * Constructor of this class.
    *
    * @since 1.0.0
    */
   public function __construct(){

      parent::__construct();

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


      add_action('woocommerce_pay_order_after_submit', [$this, 'display_payment_action']);
      add_action('woocommerce_after_checkout_form', [$this, 'display_payment_action']);

   }



   /**
    * Gets default payment method title.
    *
    * @since 1.0.0
    * @return string
    */
   public function get_default_title(){
      return __('Adyen - Credit Card', 'woosa-adyen');
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
      return 'scheme';
   }



   /**
    * Returns the payment method to be used for recurring payments
    *
    * @since 1.0.0
    * @return string
    */
   public function recurring_payment_method(){
      return $this->payment_method_type();
   }



   /**
    * Adds extra fields.
    *
    * @since 1.0.0
    * @return void
    */
    public function payment_fields() {

      parent::payment_fields();

      echo $this->generate_extra_fields_html();

   }



   /**
    * Generates extra fields HTML.
    *
    * @since 1.1.1 - add the method type in field names
    * @since 1.0.3 - added installments field
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
         <input type="hidden" id="<?php echo PREFIX . "-{$type}-is-stored-card";?>" name="<?php echo PREFIX . "-{$type}-is-stored-card";?>" value="yes" />

         <?php echo $this->generate_installments_field( WC()->customer->get_shipping_country(), $this->get_option('installments', '6') );?>
      </div>
      <?php
   }



   /**
    * Renders the form for card fields.
    *
    * @since 1.1.1 - improve custom attributes and made the logic more clean
    * @since 1.0.6 - remove duplicated card-form element
    * @since 1.0.3
    * @return string
    */
   public function render_card_form(){

      $type = $this->payment_method_type();
      $cards = API::get_ec_stored_cards();

      ?>

      <div class="<?php echo PREFIX;?>-stored-cards">

         <?php foreach($cards as $index => $item):

            //exclude BCMC from Credit Cards and Credit Cards from BCMC
            if( ('bcmc' === $this->payment_method_type() && 'bcmc' !== $item['brand']) ||
            ('scheme' === $this->payment_method_type() && 'bcmc' === $item['brand']) ){
               continue;
            }
            ?>

            <div class="<?php echo PREFIX;?>-stored-card">
               <div class="<?php echo PREFIX;?>-stored-card__details" data-<?php echo PREFIX;?>-stored-card="<?php echo PREFIX . "-{$type}-card-{$index}";?>" data-<?php echo PREFIX;?>-stored-card-type="<?php echo $type;?>">
                  <img src="https://checkoutshopper-test.adyen.com/checkoutshopper/images/logos/<?php echo $item['brand'];?>.svg" alt="">
                  <div>******<?php echo $item['lastFour'];?></div>
               </div>
               <div class="<?php echo PREFIX;?>-stored-card__fields" style="display: none;" id="<?php echo PREFIX . "-{$type}-card-{$index}";?>"></div>
            </div>
         <?php endforeach; ?>

         <div class="<?php echo PREFIX;?>-stored-card">
            <div class="<?php echo PREFIX;?>-stored-card__details" data-<?php echo PREFIX;?>-stored-card="<?php echo PREFIX . "-{$type}-card-new";?>" data-<?php echo PREFIX;?>-stored-card-type="<?php echo $type;?>">
               <span class="dashicons dashicons-plus"></span>
               <div><?php _e('Use a new card', 'woosa-adyen');?></div>
            </div>
            <div class="<?php echo PREFIX;?>-stored-card__fields" id="<?php echo PREFIX . "-{$type}-card-new";?>" style="display: none;">
               <div id="<?php echo PREFIX;?>-card-form"></div>
            </div>
         </div>

      </div>

      <?php

   }



   /**
    * Validates extra fields.
    *
    * @since 1.1.1 - use always the method type in field names
    * @since 1.0.3 - add support for installments field
    * @since 1.0.0
    * @return bool
    */
   public function validate_fields() {

      $is_valid = parent::validate_fields();
      $type = $this->payment_method_type();

      $card_number    = Utility::rgar($_POST, PREFIX."-{$type}-card-number");
      $card_exp_month = Utility::rgar($_POST, PREFIX."-{$type}-card-exp-month");
      $card_exp_year  = Utility::rgar($_POST, PREFIX."-{$type}-card-exp-year");
      $card_cvc       = Utility::rgar($_POST, PREFIX."-{$type}-card-cvc");
      $card_holder    = Utility::rgar($_POST, PREFIX."-{$type}-card-holder");

      $is_stored_card = Utility::rgar($_POST, PREFIX."-{$type}-is-stored-card", 'no');
      $stored_card_id = Utility::rgar($_POST, PREFIX."-{$type}-sci");

      $installments = (int) Utility::rgar($_POST, PREFIX."-card-installments");
      $country      = Utility::rgar($_POST, 'billing_country');


      if( ! API::is_valid_installment($installments, $country, $this->get_option('installments', '6')) ){
         wc_add_notice(__('Sorry, the number of installments seems invalid, please try again', 'woosa-adyen'), 'error');
         $is_valid = false;
      }

      if( 'yes' === $is_stored_card ){

         if(empty($stored_card_id)){
            $is_valid = false;
            wc_add_notice(__('Please provide the CVC/CVV of the card.', 'woosa-adyen'), 'error');
         }

      }else{

         if(empty($card_number) && empty($card_exp_month) && empty($card_exp_year) && empty($card_holder)){
            wc_add_notice(__('Please make sure you provided all card details', 'woosa-adyen'), 'error');
            $is_valid = false;
         }

         if(empty($card_number)){
            $is_valid = false;
            wc_add_notice(__('Please provide the card number.', 'woosa-adyen'), 'error');
         }

         if(empty($card_exp_month)){
            $is_valid = false;
            wc_add_notice(__('Please provide the card expiration month.', 'woosa-adyen'), 'error');
         }

         if(empty($card_exp_year)){
            $is_valid = false;
            wc_add_notice(__('Please provide the card expiration year.', 'woosa-adyen'), 'error');
         }

         // if(empty($card_cvc)){
         //    $is_valid = false;
         //    wc_add_notice(__('Please provide your card security number (CVC).', 'woosa-adyen'), 'error');
         // }
      }

      return $is_valid;
   }



   /**
    * Builds the required payment payload
    *
    * @since 1.1.1 - use always the method type in field names
    * @since 1.1.0 - use parent function to get common data
    * @since 1.0.9 - add fallback for splitting address
    * @since 1.0.7 - use the shopper reference from the metadata
    * @since 1.0.6 - do not allow storing cards for guest users
    *              - add billing address
    * @since 1.0.4 - save stored card id as `recurringDetailReference`
    * @since 1.0.3 - add support for installments field
    * @since 1.0.0
    * @param \WC_Order $order
    * @param string $reference
    * @return array
    */
   protected function build_payment_payload(\WC_Order $order, $reference){

      $type = $this->payment_method_type();

      $card_number    = Utility::rgar($_POST, PREFIX."-{$type}-card-number");
      $card_exp_month = Utility::rgar($_POST, PREFIX."-{$type}-card-exp-month");
      $card_exp_year  = Utility::rgar($_POST, PREFIX."-{$type}-card-exp-year");
      $card_cvc       = Utility::rgar($_POST, PREFIX."-{$type}-card-cvc");
      $card_holder    = Utility::rgar($_POST, PREFIX."-{$type}-card-holder");

      $is_stored_card = Utility::rgar($_POST, PREFIX."-{$type}-is-stored-card", 'no');
      $stored_card_id = Utility::rgar($_POST, PREFIX."-{$type}-sci");

      $installments = (int) Utility::rgar($_POST, PREFIX."-card-installments");
      $store_card   = (bool) Utility::rgar($_POST, PREFIX."-{$type}-store-card");
      $store_card   = is_user_logged_in() ? $store_card : false;

      $payload = array_merge( parent::build_payment_payload($order, $reference), [
         'additionalData' => [
            'allow3DS2' => true,
         ],
         'storePaymentMethod' => $store_card
      ]);


      if( 'yes' === $is_stored_card ){
         $payload['paymentMethod']['storedPaymentMethodId'] = $stored_card_id;
      }else{
         $payload['paymentMethod']['encryptedCardNumber'] = $card_number;
         $payload['paymentMethod']['encryptedExpiryMonth'] = $card_exp_month;
         $payload['paymentMethod']['encryptedExpiryYear'] = $card_exp_year;
         $payload['paymentMethod']['holderName'] = $card_holder;
      }


      if( ! empty($card_cvc) ){
         $payload['paymentMethod']['encryptedSecurityCode'] = $card_cvc;
      }

      if( $installments > 0 ){
         $payload['installments']['value'] = $installments;
      }


      return $payload;
   }



   /**
    * Processes the payment.
    *
    * @since 1.1.0 - replace `_#subscription#_` with `-S`
    * @since 1.0.0
    * @param int $order_id
    * @return array
    */
   public function process_payment($order_id) {

      parent::process_payment($order_id);

      $order          = wc_get_order($order_id);
      $reference      = $order_id;
      $payload        = $this->build_payment_payload( $order, $reference );
      $type           = $this->payment_method_type() === 'bcmc' ? 'bcmc-' : '';
      $stored_card_id = Utility::rgar($_POST, PREFIX."-{$type}sci");

      $recurr_reference = [];
      $subscriptions    = $this->get_subscriptions_for_order( $order_id );
      $subscription_ids = [];


      //recurring payments
      if(count($subscriptions) > 0){

         foreach($subscriptions as $sub_id => $item){
            $subscription_ids[$sub_id] = $sub_id;
            $recurr_reference[] = $sub_id;

            update_post_meta($sub_id, '_' . PREFIX . '_recurringDetailReference', $stored_card_id);
         }

         $reference = \implode('-S', $recurr_reference);
         $reference = $order_id.'-S'.$reference;
         $payload = $this->build_payment_payload( $order, $reference );

         //for tokenizing must be `true`
         $payload['storePaymentMethod'] = true;

         //create a list with unpaid subscriptions
         update_post_meta($order->get_id(), '_'.PREFIX.'_unpaid_subscriptions', $subscription_ids);

      }


      $request = API::send_payment($payload);

      if(is_string($request)){

         wc_add_notice($request, 'error');

      }else{

         return $this->process_payment_result( $request, $order );
      }


      return array('result' => 'failure');

   }




   /**
    * Processes the payment result.
    *
    * @since 1.0.6 - use default checkout url if there are subscriptions
    * @since 1.0.4 - combine all actions in one popup
    * @since 1.0.0
    * @param object $request
    * @param \WC_Order $order
    * @return array
    */
   protected function process_payment_result( $request, $order ){

      $result = ['result' => 'failure'];

      $result_code  = Utility::rgar($request, 'resultCode');
      $reference    = Utility::rgar($request, 'pspReference');
      $action       = Utility::rgar($request, 'action');

      $order->update_meta_data('_' . PREFIX . '_payment_pspReference', $reference);
      $order->update_meta_data('_' . PREFIX . '_payment_resultCode', $result_code);
      $order->update_meta_data('_' . PREFIX . '_payment_action', $action);
      $order->save();

      if($result_code == 'RedirectShopper' || $result_code == 'ChallengeShopper' || $result_code == 'IdentifyShopper'){

         $checkout_url = Checkout::has_subscription() ? wc_get_checkout_url() : $order->get_checkout_payment_url();

         $result = [
            'result'   => 'success',
            'redirect' => add_query_arg([
               PREFIX.'_card_action' => $order->get_id(),
            ],  $checkout_url)
         ];

      }else{

         $result = [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
         ];
      }

      return $result;
   }



   /**
    * Displays the payment action in a popup
    *
    * @since 1.0.4
    * @return string
    */
   public function display_payment_action(){

      if(isset($_GET[PREFIX.'_card_action'])){

         $order = wc_get_order(Utility::rgar($_GET, PREFIX.'_card_action'));

         if( $order instanceof \WC_Order){

            $action = json_encode( $order->get_meta('_'.PREFIX.'_payment_action', true) );

            ?>
               <div class="<?php echo PREFIX;?>-popup" style="display: none;">
                  <div>
                     <div id="<?php echo PREFIX;?>-card-action" class="<?php echo PREFIX;?>-component" data-payment_action='<?php echo $action;?>' data-order_id="<?php echo $order->get_id();?>">
                        <div class="<?php echo PREFIX;?>-component__text"><?php _e('Processing...', 'woosa-adyen');?></div>
                     </div>
                  </div>
               </div>
            <?php
         }

      }
   }



   /**
    * Adds an array of fields to be displayed on the gateway's settings screen.
    *
    * @since 1.0.3
    * @return void
    */
    public function init_form_fields() {

      parent::init_form_fields();

      //only for world wide cards
      if('scheme' === $this->payment_method_type()){
         $this->form_fields = array_merge($this->form_fields, array(
            'allow_installments'    => array(
               'title'       => __('Allow Installments', 'woosa-adyen'),
               'label'       => __('Yes', 'woosa-adyen'),
               'type'        => 'checkbox',
               'default'     => 'no',
               'desc_tip'    => __('Whether or not to allow installments. This is only for Brazil, Mexico and Turkey', 'woosa-adyen'),
            ),
            'installments'    => array(
               'title'       => __('Installments number', 'woosa-adyen'),
               'type'        => 'number',
               'default'     => '6',
               'desc_tip'    => __('The maximum number for installments (used for Brazil).', 'woosa-adyen'),
            ),
         ));
      }
   }



   /**
    * Generates installments field.
    *
    * @since 1.0.3
    * @param string $country
    * @param string $max_installments
    * @return string
    */
   public function generate_installments_field($country, $max_installments){

      $output = '';
      $allow_installments = $this->get_option('allow_installments', 'no');

      if( 'no' === $allow_installments) return;

      $value = API::get_installments_by_country($country, $max_installments);

      if(is_array($value)){

         $output = '<select class="adyen-checkout__input" name="'.PREFIX.'-card-installments">';
            foreach($value as $item){
               $output .= '<option value="'.$item.'">'.$item.'</option>';
            }
         $output .= '</select>';

      }elseif( (int) $value > 0 ){
         $output = '<input class="adyen-checkout__input" type="number" name="'.PREFIX.'-card-installments" value="" max="'.$value.'" placeholder="Max. '.$value.'" />';
      }

      if( empty($output) ) return;

      return '<div class="'.PREFIX.'-installments-field"><label class="adyen-checkout__label__tex">'.__('Number of installments', 'woosa-adyen').'</label>'.$output.'</div>';
   }


}