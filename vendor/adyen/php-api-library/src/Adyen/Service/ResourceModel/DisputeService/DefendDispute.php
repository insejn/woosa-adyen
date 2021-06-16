<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService;

use Woosa\Adyen\Adyen\Service\AbstractResource;
class DefendDispute extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * DefendDispute constructor.
     *
     * @param \Adyen\Service\DisputeService $service
     */
    public function __construct($service)
    {
        parent::__construct($service, $service->getResourceURL('defendDispute'));
    }
}
