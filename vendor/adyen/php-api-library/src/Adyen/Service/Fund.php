<?php

namespace Woosa\Adyen\Adyen\Service;

class Fund extends \Woosa\Adyen\Adyen\Service
{
    /**
     * @var ResourceModel\Fund\PayoutAccountHolder
     */
    protected $payoutAccountHolder;
    /**
     * @var ResourceModel\Fund\AccountHolderBalance
     */
    protected $accountHolderBalance;
    /**
     * @var ResourceModel\Fund\AccountHolderTransactionList
     */
    protected $accountHolderTransactionList;
    /**
     * @var ResourceModel\Fund\RefundNotPaidOutTransfers
     */
    protected $refundNotPaidOutTransfers;
    /**
     * @var ResourceModel\Fund\SetupBeneficiary
     */
    protected $setupBeneficiary;
    /**
     * @var ResourceModel\Fund\TransferFunds
     */
    protected $transferFunds;
    /**
     * Fund constructor.
     * @param \Adyen\Client $client
     * @throws \Adyen\AdyenException
     */
    public function __construct(\Woosa\Adyen\Adyen\Client $client)
    {
        parent::__construct($client);
        $this->payoutAccountHolder = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\PayoutAccountHolder($this);
        $this->accountHolderBalance = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\AccountHolderBalance($this);
        $this->accountHolderTransactionList = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\AccountHolderTransactionList($this);
        $this->refundNotPaidOutTransfers = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\RefundNotPaidOutTransfers($this);
        $this->setupBeneficiary = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\SetupBeneficiary($this);
        $this->transferFunds = new \Woosa\Adyen\Adyen\Service\ResourceModel\Fund\TransferFunds($this);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function payoutAccountHolder($params)
    {
        return $this->payoutAccountHolder->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function accountHolderBalance($params)
    {
        return $this->accountHolderBalance->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function accountHolderTransactionList($params)
    {
        return $this->accountHolderTransactionList->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function refundNotPaidOutTransfers($params)
    {
        return $this->refundNotPaidOutTransfers->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function setupBeneficiary($params)
    {
        return $this->setupBeneficiary->request($params);
    }
    /**
     * @param $params
     * @return mixed
     * @throws \Adyen\AdyenException
     */
    public function transferFunds($params)
    {
        return $this->transferFunds->request($params);
    }
}
