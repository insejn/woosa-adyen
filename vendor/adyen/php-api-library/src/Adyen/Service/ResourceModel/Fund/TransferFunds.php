<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Fund;

class TransferFunds extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * TransferFunds constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointFund') . '/' . $service->getClient()->getApiFundVersion() . '/transferFunds';
        parent::__construct($service, $this->endpoint);
    }
}
