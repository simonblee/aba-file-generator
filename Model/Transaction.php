<?php

namespace AbaFileGenerator\Model;

/**
 * Basic class implementing TransactionInterface. If this is too simple, extend
 * and override. If it breaks your inheritance chain, simply use your own class
 * and implement the TransactionInterface there.
 */
class Transaction implements TransactionInterface
{
    private $accountName;
    private $accountNumber;
    private $bsb;
    private $amount;
    private $indicator;
    private $transactionCode;
    private $reference;
    private $remitter;
    private $taxWithholding;

    public function getAccountName()
    {
        return $this->accountName;
    }

    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getBsb()
    {
        return $this->bsb;
    }

    public function setBsb($bsb)
    {
        $this->bsb = $bsb;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIndicator()
    {
        return $this->indicator ?: null;
    }

    public function setIndicator($indicator)
    {
        $this->indicator = $indicator;

        return $this;
    }

    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    public function setTransactionCode($transactionCode)
    {
        $this->transactionCode = $transactionCode;

        return $this;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    public function getRemitter()
    {
        return $this->remitter ?: null;
    }

    public function setRemitter($remitter)
    {
        $this->remitter = $remitter;

        return $this;
    }

    public function getTaxWithholding()
    {
        return $this->taxWithholding ?: 0;
    }

    public function setTaxWithholding($taxWithholding)
    {
        $this->taxWithholding = $taxWithholding;

        return $this;
    }
}
