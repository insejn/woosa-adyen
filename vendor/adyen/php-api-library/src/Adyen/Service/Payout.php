<?php

namespace Woosa\Adyen\Adyen\Service;

use Woosa\Adyen\Adyen\AdyenException;
use Woosa\Adyen\Adyen\Client;
use Woosa\Adyen\Adyen\Service;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Confirm;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Decline;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\StoreDetailsAndSubmit;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Submit;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\ConfirmThirdParty;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\DeclineThirdParty;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\StoreDetail;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\StoreDetailsAndSubmitThirdParty;
use Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\SubmitThirdParty;
class Payout extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Payout\Confirm
     */
    protected $confirm;
    /**
     * @var ResourceModel\Payout\Decline
     */
    protected $decline;
    /**
     * @var ResourceModel\Payout\StoreDetailsAndSubmit
     */
    protected $storeDetailsAndSubmit;
    /**
     * @var ResourceModel\Payout\Submit
     */
    protected $submit;
    /**
     * @var ResourceModel\Payout\ThirdParty\ConfirmThirdParty
     */
    protected $confirmThirdParty;
    /**
     * @var ResourceModel\Payout\ThirdParty\DeclineThirdParty
     */
    protected $declineThirdParty;
    /**
     * @var ResourceModel\Payout\ThirdParty\StoreDetailsAndSubmitThirdParty
     */
    protected $storeDetailsAndSubmitThirdParty;
    /**
     * @var ResourceModel\Payout\ThirdParty\SubmitThirdParty
     */
    protected $submitThirdParty;
    /**
     * @var ResourceModel\Payout\ThirdParty\StoreDetail
     */
    protected $storeDetail;
    /**
     * Payout constructor.
     *
     * @param Client $client
     * @throws AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->confirm = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Confirm($this);
        $this->decline = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Decline($this);
        $this->storeDetailsAndSubmit = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\StoreDetailsAndSubmit($this);
        $this->submit = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\Submit($this);
        $this->confirmThirdParty = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\ConfirmThirdParty($this);
        $this->declineThirdParty = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\DeclineThirdParty($this);
        $this->storeDetailsAndSubmitThirdParty = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\StoreDetailsAndSubmitThirdParty($this);
        $this->submitThirdParty = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\SubmitThirdParty($this);
        $this->storeDetail = new \Woosa\Adyen\Adyen\Service\ResourceModel\Payout\ThirdParty\StoreDetail($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function confirm($params)
    {
        $result = $this->confirm->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function decline($params)
    {
        $result = $this->decline->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function storeDetailsAndSubmit($params)
    {
        $result = $this->storeDetailsAndSubmit->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function submit($params)
    {
        $result = $this->submit->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function confirmThirdParty($params)
    {
        $result = $this->confirmThirdParty->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function declineThirdParty($params)
    {
        $result = $this->declineThirdParty->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function storeDetailsAndSubmitThirdParty($params)
    {
        $result = $this->storeDetailsAndSubmitThirdParty->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function submitThirdParty($params)
    {
        $result = $this->submitThirdParty->request($params);
        return $result;
    }
    /**
     * @param $params
     * @return mixed
     * @throws AdyenException
     */
    public function storeDetail($params)
    {
        $result = $this->storeDetail->request($params);
        return $result;
    }
}
