<?php

namespace Woosa\Adyen\Adyen\Service;

class BinLookup extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\BinLookup\Get3dsAvailability
     */
    protected $get3dsAvailability;
    /**
     * BinLookup constructor.
     *
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->get3dsAvailability = new \Woosa\Adyen\Adyen\Service\ResourceModel\BinLookup\Get3dsAvailability($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function get3dsAvailability($params)
    {
        $result = $this->get3dsAvailability->request($params);
        return $result;
    }
}
