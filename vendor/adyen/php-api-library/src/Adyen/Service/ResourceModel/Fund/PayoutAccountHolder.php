<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Fund;

class PayoutAccountHolder extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * PayoutAccountHolder constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointFund') . '/' . $service->getClient()->getApiFundVersion() . '/payoutAccountHolder';
        parent::__construct($service, $this->endpoint);
    }
}
