<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Checkout;

class PaymentsResult extends \Woosa\Adyen\Adyen\Service\AbstractCheckoutResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * PaymentsResult constructor.
     *
     * @param \Adyen\Service $service
     * @throws \Adyen\AdyenException
     */
    public function __construct($service)
    {
        $this->endpoint = $this->getCheckoutEndpoint($service) . '/' . $service->getClient()->getApiCheckoutVersion() . '/payments/result';
        parent::__construct($service, $this->endpoint);
    }
}
