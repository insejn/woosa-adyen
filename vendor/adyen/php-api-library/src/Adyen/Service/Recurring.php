<?php

namespace Woosa\Adyen\Adyen\Service;

class Recurring extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Recurring\ListRecurringDetails
     */
    protected $listRecurringDetails;
    /**
     * @var ResourceModel\Recurring\Disable
     */
    protected $disable;
    /**
     * Recurring constructor.
     *
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->listRecurringDetails = new \Woosa\Adyen\Adyen\Service\ResourceModel\Recurring\ListRecurringDetails($this);
        $this->disable = new \Woosa\Adyen\Adyen\Service\ResourceModel\Recurring\Disable($this, $this->getClient()->getConfig()->get('endpoint') . '/disable', array('merchantAccount', 'shopperReference'));
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function listRecurringDetails($params)
    {
        $result = $this->listRecurringDetails->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function disable($params)
    {
        $result = $this->disable->request($params);
        return $result;
    }
}
