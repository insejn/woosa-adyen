<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService;

use Woosa\Adyen\Adyen\Service\AbstractResource;
class RetrieveApplicableDefenseReasons extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * RetrieveApplicableDefenseReasons constructor.
     *
     * @param \Adyen\Service\DisputeService $service
     */
    public function __construct($service)
    {
        parent::__construct($service, $service->getResourceURL('retrieveApplicableDefenseReasons'));
    }
}
