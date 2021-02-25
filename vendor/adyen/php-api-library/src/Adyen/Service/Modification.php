<?php

namespace Woosa\Adyen\Adyen\Service;

class Modification extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Modification\Cancel
     */
    protected $cancel;
    /**
     * @var ResourceModel\Modification\CancelOrRefund
     */
    protected $cancelOrRefund;
    /**
     * @var ResourceModel\Modification\Capture
     */
    protected $capture;
    /**
     * @var ResourceModel\Modification\Refund
     */
    protected $refund;
    /**
     * @var ResourceModel\Modification\RefundWithData
     */
    protected $refundWithData;
    /**
     * @var ResourceModel\Modification\AdjustAuthorisation
     */
    protected $adjustAuthorisation;
    /**
     * Modification constructor.
     *
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->cancel = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\Cancel($this);
        $this->cancelOrRefund = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\CancelOrRefund($this);
        $this->capture = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\Capture($this);
        $this->refund = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\Refund($this);
        $this->refundWithData = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\RefundWithData($this);
        $this->adjustAuthorisation = new \Woosa\Adyen\Adyen\Service\ResourceModel\Modification\AdjustAuthorisation($this);
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function cancel($params, $requestOptions = null)
    {
        $result = $this->cancel->request($params, $requestOptions);
        return $result;
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function cancelOrRefund($params, $requestOptions = null)
    {
        $result = $this->cancelOrRefund->request($params, $requestOptions);
        return $result;
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function capture($params, $requestOptions = null)
    {
        $result = $this->capture->request($params, $requestOptions);
        return $result;
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function refund($params, $requestOptions = null)
    {
        $result = $this->refund->request($params, $requestOptions);
        return $result;
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function refundWithData($params, $requestOptions = null)
    {
        $result = $this->refundWithData->request($params, $requestOptions);
        return $result;
    }
    /**
     * @param $params
     * @param null $requestOptions
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function adjustAuthorisation($params, $requestOptions = null)
    {
        $result = $this->adjustAuthorisation->request($params, $requestOptions);
        return $result;
    }
}
