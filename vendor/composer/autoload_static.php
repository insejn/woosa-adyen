<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit344d6814bbe2700eafbbe06d6f58ae15
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
        'Woosa\\Adyen\\ACF' => __DIR__ . '/../..' . '/includes/plugins-support/class-acf.php',
        'Woosa\\Adyen\\AJAX' => __DIR__ . '/../..' . '/includes/class-ajax.php',
        'Woosa\\Adyen\\API' => __DIR__ . '/../..' . '/includes/class-api.php',
        'Woosa\\Adyen\\Abstract_Gateway' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-abstract-gateway.php',
        'Woosa\\Adyen\\Adyen\\Service\\AbstractCheckoutResource' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/AbstractCheckoutResource.php',
        'Woosa\\Adyen\\Adyen\\Service\\AbstractResource' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/AbstractResource.php',
        'Woosa\\Adyen\\Adyen\\Service\\Account' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Account.php',
        'Woosa\\Adyen\\Adyen\\Service\\BinLookup' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/BinLookup.php',
        'Woosa\\Adyen\\Adyen\\Service\\Checkout' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Checkout.php',
        'Woosa\\Adyen\\Adyen\\Service\\CheckoutUtility' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/CheckoutUtility.php',
        'Woosa\\Adyen\\Adyen\\Service\\DirectoryLookup' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/DirectoryLookup.php',
        'Woosa\\Adyen\\Adyen\\Service\\Fund' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Fund.php',
        'Woosa\\Adyen\\Adyen\\Service\\Modification' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Modification.php',
        'Woosa\\Adyen\\Adyen\\Service\\Notification' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Notification.php',
        'Woosa\\Adyen\\Adyen\\Service\\Payment' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Payment.php',
        'Woosa\\Adyen\\Adyen\\Service\\Payout' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Payout.php',
        'Woosa\\Adyen\\Adyen\\Service\\PosPayment' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/PosPayment.php',
        'Woosa\\Adyen\\Adyen\\Service\\Recurring' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/Recurring.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\CloseAccount' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/CloseAccount.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\CloseAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/CloseAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\CreateAccount' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/CreateAccount.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\CreateAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/CreateAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\DeleteBankAccounts' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/DeleteBankAccounts.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\DeleteShareholders' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/DeleteShareholders.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\GetAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/GetAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\GetUploadedDocuments' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/GetUploadedDocuments.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\SuspendAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/SuspendAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\UnSuspendAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/UnSuspendAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\UpdateAccount' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/UpdateAccount.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\UpdateAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/UpdateAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\UpdateAccountHolderState' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/UpdateAccountHolderState.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Account\\UploadDocument' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Account/UploadDocument.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\BinLookup\\Get3dsAvailability' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/BinLookup/Get3dsAvailability.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\CheckoutUtility\\OriginKeys' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/CheckoutUtility/OriginKeys.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Checkout\\PaymentMethods' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Checkout/PaymentMethods.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Checkout\\PaymentSession' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Checkout/PaymentSession.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Checkout\\Payments' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Checkout/Payments.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Checkout\\PaymentsDetails' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Checkout/PaymentsDetails.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Checkout\\PaymentsResult' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Checkout/PaymentsResult.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\DirectoryLookup\\Directory' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/DirectoryLookup/Directory.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\AccountHolderBalance' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/AccountHolderBalance.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\AccountHolderTransactionList' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/AccountHolderTransactionList.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\PayoutAccountHolder' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/PayoutAccountHolder.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\RefundNotPaidOutTransfers' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/RefundNotPaidOutTransfers.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\SetupBeneficiary' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/SetupBeneficiary.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Fund\\TransferFunds' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Fund/TransferFunds.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\AdjustAuthorisation' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/AdjustAuthorisation.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\Cancel' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/Cancel.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\CancelOrRefund' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/CancelOrRefund.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\Capture' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/Capture.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\Refund' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/Refund.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Modification\\RefundWithData' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Modification/RefundWithData.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\CreateNotificationConfiguration' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/CreateNotificationConfiguration.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\DeleteNotificationConfigurations' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/DeleteNotificationConfigurations.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\GetNotificationConfiguration' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/GetNotificationConfiguration.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\GetNotificationConfigurationList' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/GetNotificationConfigurationList.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\TestNotificationConfiguration' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/TestNotificationConfiguration.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Notification\\UpdateNotificationConfiguration' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Notification/UpdateNotificationConfiguration.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payment\\Authorise' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payment/Authorise.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payment\\Authorise3D' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payment/Authorise3D.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payment\\Authorise3DS2' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payment/Authorise3DS2.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payment\\ConnectedTerminals' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payment/ConnectedTerminals.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payment\\TerminalCloudAPI' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payment/TerminalCloudAPI.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\Confirm' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/Confirm.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\Decline' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/Decline.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\StoreDetailsAndSubmit' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/StoreDetailsAndSubmit.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\Submit' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/Submit.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\ThirdParty\\ConfirmThirdParty' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/ThirdParty/ConfirmThirdParty.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\ThirdParty\\DeclineThirdParty' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/ThirdParty/DeclineThirdParty.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\ThirdParty\\StoreDetail' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/ThirdParty/StoreDetail.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\ThirdParty\\StoreDetailsAndSubmitThirdParty' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/ThirdParty/StoreDetailsAndSubmitThirdParty.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Payout\\ThirdParty\\SubmitThirdParty' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Payout/ThirdParty/SubmitThirdParty.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Recurring\\Disable' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Recurring/Disable.php',
        'Woosa\\Adyen\\Adyen\\Service\\ResourceModel\\Recurring\\ListRecurringDetails' => __DIR__ . '/..' . '/adyen/php-api-library/src/Adyen/Service/ResourceModel/Recurring/ListRecurringDetails.php',
        'Woosa\\Adyen\\Alipay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-alipay.php',
        'Woosa\\Adyen\\Applepay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-applepay.php',
        'Woosa\\Adyen\\Assets' => __DIR__ . '/../..' . '/includes/class-assets.php',
        'Woosa\\Adyen\\Auto_Update' => __DIR__ . '/../..' . '/includes/class-auto-update.php',
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
        'Woosa\\Adyen\\My_Account' => __DIR__ . '/../..' . '/includes/woocommerce/class-my-account.php',
        'Woosa\\Adyen\\Order' => __DIR__ . '/../..' . '/includes/woocommerce/class-order.php',
        'Woosa\\Adyen\\Paypal' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-paypal.php',
        'Woosa\\Adyen\\Plugins_Support' => __DIR__ . '/../..' . '/includes/plugins-support/class-plugins-support.php',
        'Woosa\\Adyen\\Routes' => __DIR__ . '/../..' . '/includes/class-routes.php',
        'Woosa\\Adyen\\Sepa_Direct_Debit' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-sepa-direct-debit.php',
        'Woosa\\Adyen\\Settings' => __DIR__ . '/../..' . '/includes/woocommerce/class-settings.php',
        'Woosa\\Adyen\\Sofort' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-sofort.php',
        'Woosa\\Adyen\\Utility' => __DIR__ . '/../..' . '/includes/class-utility.php',
        'Woosa\\Adyen\\Wechatpay' => __DIR__ . '/../..' . '/includes/woocommerce/payment/class-wechatpay.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit344d6814bbe2700eafbbe06d6f58ae15::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit344d6814bbe2700eafbbe06d6f58ae15::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit344d6814bbe2700eafbbe06d6f58ae15::$classMap;

        }, null, ClassLoader::class);
    }
}