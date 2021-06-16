<?php

namespace Woosa\Adyen\Adyen\Service;

class Hop extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Hop\GetOnboardingUrl
     */
    protected $getOnboardingUrl;
    /**
     * @var ResourceModel\Hop\GetPciQuestionnaireUrl
     */
    protected $getPciQuestionnaireUrl;
    /**
     * Hop constructor.
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->getOnboardingUrl = new \Woosa\Adyen\Adyen\Service\ResourceModel\Hop\GetOnboardingUrl($this);
        $this->getPciQuestionnaireUrl = new \Woosa\Adyen\Adyen\Service\ResourceModel\Hop\GetPciQuestionnaireUrl($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function getOnboardingUrl($params)
    {
        return $this->getOnboardingUrl->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function getPciQuestionnaireUrl($params)
    {
        return $this->getPciQuestionnaireUrl->request($params);
    }
}
