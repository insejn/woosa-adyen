<?php

namespace Woosa\Adyen\Adyen\Service;

class Notification extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Notification\CreateNotificationConfiguration
     */
    protected $createNotificationConfiguration;
    /**
     * @var ResourceModel\Notification\UpdateNotificationConfiguration
     */
    protected $updateNotificationConfiguration;
    /**
     * @var ResourceModel\Notification\GetNotificationConfiguration
     */
    protected $getNotificationConfiguration;
    /**
     * @var ResourceModel\Notification\DeleteNotificationConfigurations
     */
    protected $deleteNotificationConfigurations;
    /**
     * @var ResourceModel\Notification\GetNotificationConfigurationList
     */
    protected $getNotificationConfigurationList;
    /**
     * @var ResourceModel\Notification\TestNotificationConfiguration
     */
    protected $testNotificationConfiguration;
    /**
     * Notification constructor.
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->createNotificationConfiguration = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\CreateNotificationConfiguration($this);
        $this->updateNotificationConfiguration = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\UpdateNotificationConfiguration($this);
        $this->getNotificationConfiguration = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\GetNotificationConfiguration($this);
        $this->deleteNotificationConfigurations = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\DeleteNotificationConfigurations($this);
        $this->getNotificationConfigurationList = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\GetNotificationConfigurationList($this);
        $this->testNotificationConfiguration = new \Woosa\Adyen\Adyen\Service\ResourceModel\Notification\TestNotificationConfiguration($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function createNotificationConfiguration($params)
    {
        return $this->createNotificationConfiguration->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function updateNotificationConfiguration($params)
    {
        return $this->updateNotificationConfiguration->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function getNotificationConfiguration($params)
    {
        return $this->getNotificationConfiguration->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function deleteNotificationConfigurations($params)
    {
        return $this->deleteNotificationConfigurations->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function getNotificationConfigurationList($params)
    {
        return $this->getNotificationConfigurationList->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function testNotificationConfiguration($params)
    {
        return $this->testNotificationConfiguration->request($params);
    }
}
