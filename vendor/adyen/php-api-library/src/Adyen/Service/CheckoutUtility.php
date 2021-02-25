<?php

namespace Woosa\Adyen\Adyen\Service;

class CheckoutUtility extends \Woosa\Adyen\Adyen\ApiKeyAuthenticatedService
{
    /**
     * @var ResourceModel\CheckoutUtility\OriginKeys
     */
    protected $originKeys;
    /**
     * CheckoutUtility constructor.
     *
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->originKeys = new \Woosa\Adyen\Adyen\Service\ResourceModel\CheckoutUtility\OriginKeys($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function originKeys($params)
    {
        $result = $this->originKeys->request($params);
        return $result;
    }
}
