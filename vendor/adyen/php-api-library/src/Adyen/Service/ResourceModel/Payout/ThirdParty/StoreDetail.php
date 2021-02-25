<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty;

class StoreDetail extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * StoreDetail constructor.
     *
     * @param \Adyen\Service $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpoint') . '/pal/servlet/Payout/' . $service->getClient()->getApiPayoutVersion() . '/storeDetail';
        parent::__construct($service, $this->endpoint);
    }
}
