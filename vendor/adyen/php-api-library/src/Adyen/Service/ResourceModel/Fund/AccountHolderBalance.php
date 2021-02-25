<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Fund;

class AccountHolderBalance extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * AccountHolderBalance constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointFund') . '/' . $service->getClient()->getApiFundVersion() . '/accountHolderBalance';
        parent::__construct($service, $this->endpoint);
    }
}
