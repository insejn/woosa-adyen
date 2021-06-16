<?php

namespace Woosa\Adyen\Adyen\Service;

use Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\DefendDispute;
use Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\DeleteDisputeDefenseDocument;
use Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\RetrieveApplicableDefenseReasons;
use Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\SupplyDefenseDocument;
class DisputeService extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var \Adyen\Service\ResourceModel\DisputeService\RetrieveApplicableDefenseReasons
     */
    protected $retrieveApplicableDefenseReasons;
    /**
     * @var \Adyen\Service\ResourceModel\DisputeService\DeleteDisputeDefenseDocument
     */
    protected $deleteDisputeDefenseDocument;
    /**
     * @var \Adyen\Service\ResourceModel\DisputeService\DefendDispute
     */
    protected $defendDispute;
    /**
     * @var \Adyen\Service\ResourceModel\DisputeService\SupplyDefenseDocument
     */
    protected $supplyDefenseDocument;
    /**
     * @inheritDoc
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->retrieveApplicableDefenseReasons = new \Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\RetrieveApplicableDefenseReasons($this);
        $this->deleteDisputeDefenseDocument = new \Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\DeleteDisputeDefenseDocument($this);
        $this->defendDispute = new \Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\DefendDispute($this);
        $this->supplyDefenseDocument = new \Woosa\Adyen\Adyen\Service\ResourceModel\DisputeService\SupplyDefenseDocument($this);
    }
    /**
     * Handler for /retrieveApplicableDefenseReasons endpoint
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function retrieveApplicableDefenseReasons($params)
    {
        return $this->retrieveApplicableDefenseReasons->request($params);
    }
    /**
     * Handler for /supplyDefenseDocument endpoint
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function supplyDefenseDocument($params)
    {
        return $this->supplyDefenseDocument->request($params);
    }
    /**
     * Handler for /deleteDisputeDefenseDocument endpoint
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function deleteDisputeDefenseDocument($params)
    {
        return $this->deleteDisputeDefenseDocument->request($params);
    }
    /**
     * Handler for /defendDispute endpoint
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function defendDispute($params)
    {
        return $this->defendDispute->request($params);
    }
    /**
     * Get the service resource endpoint URL
     *
     * @param string $endpoint
     *
     * @return string
     */
    public function getResourceURL($endpoint)
    {
        return $this->getClient()->getConfig()->get('endpointDisputeService') . '/' . $this->getClient()->getDisputeServiceVersion() . '/' . $endpoint;
    }
}
