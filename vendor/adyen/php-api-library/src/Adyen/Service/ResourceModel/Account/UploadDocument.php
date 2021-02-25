<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Account;

class UploadDocument extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * UploadDocument constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointAccount') . '/' . $service->getClient()->getApiAccountVersion() . '/uploadDocument';
        parent::__construct($service, $this->endpoint);
    }
}
