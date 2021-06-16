<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService;

use Woosa\Adyen\Adyen\Service\AbstractResource;
class SupplyDefenseDocument extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * SupplyDefenseDocument constructor.
     *
     * @param \Adyen\Service\DisputeService $service
     */
    public function __construct($service)
    {
        parent::__construct($service, $service->getResourceURL('supplyDefenseDocument'));
    }
}
