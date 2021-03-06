<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Account;

class GetUploadedDocuments extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * GetUploadedDocuments constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointAccount') . '/' . $service->getClient()->getApiAccountVersion() . '/getUploadedDocuments';
        parent::__construct($service, $this->endpoint);
    }
}
