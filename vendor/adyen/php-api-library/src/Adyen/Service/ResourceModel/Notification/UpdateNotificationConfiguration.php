<?php

namespace Woosa\Adyen\Adyen\Service\ResourceModel\Notification;

class UpdateNotificationConfiguration extends \Woosa\Adyen\Adyen\Service\AbstractResource
{
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * UpdateNotificationConfiguration constructor.
     * @param $service\
     */
    public function __construct($service)
    {
        $this->endpoint = $service->getClient()->getConfig()->get('endpointNotification') . '/' . $service->getClient()->getApiNotificationVersion() . '/updateNotificationConfiguration';
        parent::__construct($service, $this->endpoint);
    }
}
