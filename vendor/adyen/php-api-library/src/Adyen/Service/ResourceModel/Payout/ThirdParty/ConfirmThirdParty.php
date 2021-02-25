<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty;

class ConfirmThirdParty extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * ConfirmThirdParty constructor.
     *
     * @param \Adyen\Service $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpoint') . '/pal/servlet/Payout/' . $service->getClient()->getApiPayoutVersion() . '/confirmThirdParty';
        parent::__construct($service, $this->endpoint);
    }
}
