<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Hop;

class GetOnboardingUrl extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * GetOnboardingUrl constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointHop') . '/' . $service->getClient()->getApiHopVersion() . '/getOnboardingUrl';
        parent::__construct($service, $this->endpoint);
    }
}
