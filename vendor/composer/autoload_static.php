<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita5756d139f83a7795cb2fd3665d0e630
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Woosa\\Adyen\\VIISON\\AddressSplitter\\' => 35,
            'Woosa\\Adyen\\Psr\\Log\\' => 20,
            'Woosa\\Adyen\\Monolog\\' => 20,
            'Woosa\\Adyen\\Adyen\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Woosa\\Adyen\\VIISON\\AddressSplitter\\' => 
        array (
            0 => __DIR__ . '/..' . '/viison/address-splitter/src',
        ),
        'Woosa\\Adyen\\Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Woosa\\Adyen\\Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Woosa\\Adyen\\Adyen\\' => 
        array (
            0 => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Woosa\\Adyen\\AJAX' => __DIR__ . '/../..' . '/includes/class-ajax.php',
        'Woosa\\Adyen\\API' => __DIR__ . '/../..' . '/includes/class-api.php',
        'Woosa\\Adyen\\Abstract_API' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-api.php',
        'Woosa\\Adyen\\Abstract_Action_Checker' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-action-checker.php',
        'Woosa\\Adyen\\Abstract_Action_Scheduler' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-action-scheduler.php',
        'Woosa\\Adyen\\Abstract_Assets' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-assets.php',
        'Woosa\\Adyen\\Abstract_Bulk_Action' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-bulk-action.php',
        'Woosa\\Adyen\\Abstract_Core' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-core.php',
        'Woosa\\Adyen\\Abstract_Dependency' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-dependency.php',
        'Woosa\\Adyen\\Abstract_Gateway' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-abstract-gateway.php',
        'Woosa\\Adyen\\Abstract_Logger' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-logger.php',
        'Woosa\\Adyen\\Abstract_Settings' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-settings.php',
        'Woosa\\Adyen\\Abstract_Third_Party' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-third-party.php',
        'Woosa\\Adyen\\Abstract_Tools' => __DIR__ . '/../..' . '/includes/abstracts/class-abstract-tools.php',
        'Woosa\\Adyen\\Action_Checker' => __DIR__ . '/../..' . '/includes/class-action-checker.php',
        'Woosa\\Adyen\\Action_Scheduler' => __DIR__ . '/../..' . '/includes/class-action-scheduler.php',
        'Woosa\\Adyen\\Alipay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-alipay.php',
        'Woosa\\Adyen\\Applepay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-applepay.php',
        'Woosa\\Adyen\\Assets' => __DIR__ . '/../..' . '/includes/class-assets.php',
        'Woosa\\Adyen\\Bancontact' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-bancontact.php',
        'Woosa\\Adyen\\Boleto' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-boleto.php',
        'Woosa\\Adyen\\Checkout' => __DIR__ . '/../..' . '/includes/woocommerce/class-checkout.php',
        'Woosa\\Adyen\\Core' => __DIR__ . '/../..' . '/includes/class-core.php',
        'Woosa\\Adyen\\Credit_Card' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-credit-card.php',
        'Woosa\\Adyen\\Dependency' => __DIR__ . '/../..' . '/includes/class-dependency.php',
        'Woosa\\Adyen\\Errors' => __DIR__ . '/../..' . '/includes/class-errors.php',
        'Woosa\\Adyen\\Giropay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-giropay.php',
        'Woosa\\Adyen\\Googlepay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-googlepay.php',
        'Woosa\\Adyen\\Ideal' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-ideal.php',
        'Woosa\\Adyen\\Klarna' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-klarna.php',
        'Woosa\\Adyen\\Klarna_Account' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-klarna-account.php',
        'Woosa\\Adyen\\Klarna_PayNow' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-klarna-paynow.php',
        'Woosa\\Adyen\\License' => __DIR__ . '/../..' . '/includes/class-license.php',
        'Woosa\\Adyen\\Logger' => __DIR__ . '/../..' . '/includes/class-logger.php',
        'Woosa\\Adyen\\My_Account' => __DIR__ . '/../..' . '/includes/woocommerce/class-my-account.php',
        'Woosa\\Adyen\\Order' => __DIR__ . '/../..' . '/includes/woocommerce/class-order.php',
        'Woosa\\Adyen\\Paypal' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-paypal.php',
        'Woosa\\Adyen\\Routes' => __DIR__ . '/../..' . '/includes/class-routes.php',
        'Woosa\\Adyen\\Sepa_Direct_Debit' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-sepa-direct-debit.php',
        'Woosa\\Adyen\\Settings' => __DIR__ . '/../..' . '/includes/woocommerce/class-settings.php',
        'Woosa\\Adyen\\Sofort' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-sofort.php',
        'Woosa\\Adyen\\Third_Party' => __DIR__ . '/../..' . '/includes/class-third-party.php',
        'Woosa\\Adyen\\Tools' => __DIR__ . '/../..' . '/includes/class-tools.php',
        'Woosa\\Adyen\\Utility' => __DIR__ . '/../..' . '/includes/class-utility.php',
        'Woosa\\Adyen\\Wechatpay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-wechatpay.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita5756d139f83a7795cb2fd3665d0e630::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita5756d139f83a7795cb2fd3665d0e630::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita5756d139f83a7795cb2fd3665d0e630::$classMap;

        }, null, ClassLoader::class);
    }
}
