<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Payout;

class Submit extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * Submit constructor.
     *
     * @param \Adyen\Service $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpoint') . '/pal/servlet/Payout/' . $service->getClient()->getApiPayoutVersion() . '/submit';
        parent::__construct($service, $this->endpoint);
    }
}
