<?php

namespace AbaFileGenerator\Model;

interface TransactionInterface
{
    const EXTERNALLY_INITIATED_DEBIT = '13';
    const EXTERNALLY_INITIATED_CREDIT = '50';
    const AUSTRALIAN_GOVERNMENT_SECURITY_INTEREST = '51';
    const FAMILY_ALLOWANCE = '52';
    const PAYROLL_PAYMENT = '53';
    const PENSION_PAYMENT = '54';
    const ALLOTMENT = '55';
    const DIVIDEND = '56';
    const DEBENTURE_OR_NOTE_INTEREST = '57';

    /**
     * Bank account name for this transaction.
     *
     * @return string
     */
    public function getAccountName();

    /**
     * Return the account number as a string. Must be 9 digits or less.
     *
     * @return string
     */
    public function getAccountNumber();

    /**
     * Return the bank's BSB for this account. Format is xxx-xxx
     *
     * @return string
     */
    public function getBsb();

    /**
     * Return the transaction amount in cents.
     *
     * @return integer
     */
    public function getAmount();

    /**
     * Return null for a normal transaction or if withholding tax:
     * "W" – dividend paid to a resident of a country where a double tax agreement is in force.
     * "X" – dividend paid to a resident of any other country.
     * "Y" – interest paid to all non-residents.
     *
     * @return mixed
     */
    public function getIndicator();

    /**
     * Return null for a normal transaction or if withholding tax:
     * "W" – dividend paid to a resident of a country where a double tax agreement is in force.
     * "X" – dividend paid to a resident of any other country.
     * "Y" – interest paid to all non-residents.
     *
     * @return string
     */
    public function getTransactionCode();

    /**
     * Description of transaction to appear on recipients bank statement.
     *
     * @return string
     */
    public function getReference();

    /**
     * Name of originator of entry.
     *
     * @return null|string
     */
    public function getRemitter();

    /**
     * Amount of tax withholding. Return zero if not withholding any amount.
     *
     * @return integer
     */
    public function getTaxWithholding();
}
