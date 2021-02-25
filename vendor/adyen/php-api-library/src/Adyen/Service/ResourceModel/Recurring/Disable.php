<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Recurring;

class Disable extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * Disable constructor.
     *
     * @param \Adyen\Service $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpoint') . '/pal/servlet/Recurring/' . $service->getClient()->getApiRecurringVersion() . '/disable';
        parent::__construct($service, $this->endpoint);
    }
}
