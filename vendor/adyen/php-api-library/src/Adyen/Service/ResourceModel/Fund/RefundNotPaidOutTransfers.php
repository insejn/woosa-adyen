<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Fund;

class RefundNotPaidOutTransfers extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * RefundNotPaidOutTransfers constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointFund') . '/' . $service->getClient()->getApiFundVersion() . '/refundNotPaidOutTransfers';
        parent::__construct($service, $this->endpoint);
    }
}
