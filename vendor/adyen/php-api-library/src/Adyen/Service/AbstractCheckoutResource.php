<?php

namespace Woosa\Adyen\Adyen\Service;

class AbstractCheckoutResource extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * Return Checkout endpoint
     *
     * @param $service
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function getCheckoutEndpoint($service)
    {
        // check if endpoint is set
        if ($service->getClient()->getConfig()->get('endpointCheckout') == null) {
            $logger = $service->getClient()->getLogger();
            $msg = 'Please provide your unique live url prefix on the' . ' setEnvironment() call on the Client or provide endpointCheckout' . ' in your config object.';
            $logger->error($msg);
            throw new \Woosa\Adyen\Adyen\AdyenException($msg);
        }
        return $service->getClient()->getConfig()->get('endpointCheckout');
    }
}
