<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Account;

class UpdateAccountHolder extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * UpdateAccountHolder constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointAccount') . '/' . $service->getClient()->getApiAccountVersion() . '/updateAccountHolder';
        parent::__construct($service, $this->endpoint);
    }
}
