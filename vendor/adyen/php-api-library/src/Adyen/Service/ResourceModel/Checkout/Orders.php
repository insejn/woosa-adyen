<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Checkout;

class Orders extends \Woosa\Adyen\Adyen\Service\AbstractCheckoutResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * Orders constructor.
     *
     * @param \Adyen\Service $service
     * @throws \Adyen\AdyenException
     */
    public function __construct($service)
    {
        $this->endpoint = $this->getCheckoutEndpoint($service) . '/' . $service->getClient()->getApiCheckoutVersion() . '/orders';
        parent::__construct($service, $this->endpoint);
    }
}
