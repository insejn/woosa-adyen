<?php

namespace Woosa\Adyen\Adyen\Service;

class DirectoryLookup extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\DirectoryLookup\Directory
     */
    protected $directoryLookup;
    /**
     * DirectoryLookup constructor.
     *
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->directoryLookup = new \Woosa\Adyen\Adyen\Service\ResourceModel\DirectoryLookup\Directory($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function directoryLookup($params)
    {
        $result = $this->directoryLookup->requestPost($params);
        return $result;
    }
}
