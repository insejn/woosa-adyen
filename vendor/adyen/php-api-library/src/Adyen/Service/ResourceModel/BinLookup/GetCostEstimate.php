<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\BinLookup;

class GetCostEstimate extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * Include applicationInfo key in the request parameters
     *
     * @var bool
     */
    protected $allowApplicationInfo = \false;
    /**
     * GetCostEstimate constructor.
     *
     * @param \Adyen\Service $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpoint') . '/pal/servlet/BinLookup/' . $service->getClient()->getApiBinLookupVersion() . '/getCostEstimate';
        parent::__construct($service, $this->endpoint, $this->allowApplicationInfo);
    }
}
