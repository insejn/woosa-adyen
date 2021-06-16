<?php

namespace Woosa\Adyen\Adyen;

class Service
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var bool
     */
    protected $requiresApiKey = \false;
    /**
     * Service constructor.
     *
     * @param Client $client
     * @throws AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        $msg = null;
        // validate if client has all the configuration we need
        if (!$client->getConfig()->get('environment')) {
            // throw exception
            $msg = 'The Client does not have a correct environment, use ' . \Woosa\Adyen\Adyen\Environment::TEST . ' or ' . \Woosa\Adyen\Adyen\Environment::LIVE;
            throw new \Woosa\Adyen\Adyen\AdyenException($msg);
        }
        $this->client = $client;
    }
    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
    /**
     * @return bool
     */
    public function requiresApiKey()
    {
        return $this->requiresApiKey;
    }
}
