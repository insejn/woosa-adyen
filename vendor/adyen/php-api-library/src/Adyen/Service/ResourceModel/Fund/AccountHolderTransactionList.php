<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Fund;

class AccountHolderTransactionList extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * AccountHolderTransactionList constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointFund') . '/' . $service->getClient()->getApiFundVersion() . '/accountHolderTransactionList';
        parent::__construct($service, $this->endpoint);
    }
}
