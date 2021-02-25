<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Notification;

class DeleteNotificationConfigurations extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * DeleteNotificationConfigurations constructor.
     * @param $service
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointNotification') . '/' . $service->getClient()->getApiNotificationVersion() . '/deleteNotificationConfigurations';
        parent::__construct($service, $this->endpoint);
    }
}
