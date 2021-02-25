<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Account;

class CreateAccount extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * CreateAccount constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointAccount') . '/' . $service->getClient()->getApiAccountVersion() . '/createAccount';
        parent::__construct($service, $this->endpoint);
    }
}
