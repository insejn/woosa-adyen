<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Account;

class UpdateAccountHolderState extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * UpdateAccountHolderState constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointAccount') . '/' . $service->getClient()->getApiAccountVersion() . '/updateAccountHolderState';
        parent::__construct($service, $this->endpoint);
    }
}
