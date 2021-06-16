=== Woosa - Adyen for WooCommerce ===
Contributors: woosa
Tags: online payments, credit card, iDeal, giropay, googlepay, SEPA
Requires at least: 5.0
Tested up to: 5.4
Stable tag: trunk
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


== Description ==


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woosa-adyen` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Bol.com admin menu to configure the plugin


== Screenshots ==

== Changelog ==

= 1.1.4 - 2021-05-27 =

* [FIX] - Fix Javascript conflict on License section

= 1.1.3 - 2021-05-13 =

* [FIX] - Sending payment details fails in some cases due to cookie
* [FIX] - Undefined function in Paypal method
* [FIX] - Change Google pay icon
* [IMPROVEMENT] - Upgrade Adyen PHP client library from v5.0.0 to v10.1.0
* [IMPROVEMENT] - Upgrade Adyen JS component from v3.12.1 to v4.4.0
* [TWEAK] - Small changes to Tools section

= 1.1.2 - 2021-04-12 =

* [IMPROVEMENT] - Rebuilt license management and the logic of receving updates

= 1.1.1 - 2020-10.03 =

* [FIX] - Fixed missing icon on "Use new card" button in checkout page
* [FIX] - Stored cards are not removed properly from the general cache and this gives conflicts for guest users
* [FIX] - Fixed wrong variable name "$s_address"
* [FIX] - Integration of Giropay payment method is changed to "API-only" mode because it does not work properly via JS component
* [FIX] - Mount the JS component of credit card form only once to avoid multiple unnecessary calls
* [FIX] - Inform the admin by a warning message when the domain key must be manually re-generated

= 1.1.0 - 2020-09.09 =

* [FIX] - Added a new setting option to define a "Reference Prefix" to avoid conflicts in processing orders on multisite installation
* [FEATURE] - Added PayPal payment method (no recurring payments supported yet)
* [FEATURE] - Added Klarna payment method (no recurring payments supported yet)
* [FEATURE] - Added a new option to allow customers to remove their payment personal data according to GDPR
* [FEATURE] - Added recurring payments support for Google Pay payment method
* [TWEAK] - Added more data in the API requests to avoid fraud detection
* [TWEAK] - Display supported countries and currencies on each payment method
* [TWEAK] - Added a new settings section called "Tools"

= 1.0.10 - 2020-08.13 =

* [FIX] - Added support for Bancontact payment method to be used with subscrptions as well
* [FIX] - Exclude stored credit cards from the general cache
* [FIX] - Fixed broken design of credit cards form in the checkout page
* [FIX] - Fixed JS scripts issue in Wordpress 5.5

= 1.0.9 - 2020-07.23 =

* [FIX] - Added support for variable subscription products to avoid the "pending payment" order status
* [FIX] - Made house number optional when paying with credit card
* [FIX] - Fixed the empty user reference when paying with a saved credit card
* [FIX] - Encapsulated the entire code to avoid conflicts with other plugins which use the same dependency libraries

= 1.0.8 - 2020-06-29 =

* [FIX] - The origin key was not regenerated on saving settings action

= 1.0.7 - 2020-06-25 =

* [FIX] - Fixed wrong reference for recurring orders
* [FIX] - Cache the available payment methods to increase the speed time
* [FIX] - Fixed conflicts for generating the origin keys
* [IMPROVEMENT] - Rearranged the settings page and made it accessible even if the license is inactive

= 1.0.6 - 2020-05-01 =

* [FIX] - Fixed the problem of removing stored cards from "My account" page
* [FIX] - Fixed wrong payment url for subscription products
* [FIX] - Fixed credit cards conflict for guest users
* [TWEAK] - Do not regenerate the credit card form when the checkout contents reload
* [TWEAK] - Add extra info (billing address) in the payment request

= 1.0.5 - 2020-02-19 =

* [FIX] - Credit card form is not loading due to `origin keys` is not generated
* [FIX] - Credit card form multiple click events conflict
* [FEATURE] - New option to set whether or not to remove plugin data on uninstall
* [TWEAK] - Rearrange settings sub-tabs
* [TWEAK] - Add falback for JS file dependencies

= 1.0.4 - 2020-01-09 =

* [FEATURE] - Added Google Pay payment method
* [FEATURE] - Added Wechat Pay payment method
* [TWEAK] - Show authentication  status
* [TWEAK] - Add caching for some API requests and a button to clear this cache

= 1.0.3 - 2019-11-26 =

* [FEATURE] - Boleto payment method added
* [FEATURE] - Alipay payment method added
* [FEATURE] - Card installments support added
* [FEATURE] - New option to capture payments manually
* [FEATURE] - New option to save credit cards for future payments
* [FEATURE] - New section in My Account page to display the saved credit cards

= 1.0.2 - 2019-09-25 =

* [FIX] - Fixed missing function for getting subscriptions

= 1.0.1 - 2019-09-20 =

* [FIX] - Fixed activation plugin issue

= 1.0.0 - 2019-09-05 =

* This is the first release, yeey!