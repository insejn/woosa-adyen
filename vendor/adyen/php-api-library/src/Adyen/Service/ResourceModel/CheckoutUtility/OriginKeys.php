<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\CheckoutUtility;

class OriginKeys extends \Woosa\Adyen\Adyen\Service\AbstractCheckoutResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * OriginKeys constructor.
     *
     * @param \Adyen\Service $service
     * @throws \Adyen\AdyenException
     */
    public function __construct($service)
    {
        $this->endpoint = $this->getCheckoutEndpoint($service) . '/' . $service->getClient()->getApiCheckoutUtilityVersion() . '/originKeys';
        parent::__construct($service, $this->endpoint);
    }
}
