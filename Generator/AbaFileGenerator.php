<?php

namespace AbaFileGenerator\Generator;

use AbaFileGenerator\Exception\InvalidTransactionException;
use AbaFileGenerator\Model\TransactionInterface;
use \Exception;

class AbaFileGenerator
{
    const DESCRIPTIVE_TYPE = '0';
    const DETAIL_TYPE = '1';
    const BATCH_TYPE = '7';

    /**
     * @var string - aba file string
     */
    private $abaString = '';

    /**
     * @var integer - running total of credits in file
     */
    private $creditTotal = 0;

    /**
     * @var integer - running total of debit in file
     */
    private $debitTotal = 0;

    /**
     * Validates that the BSB is 6 digits with a dash in the middle: 123-456
     */
    private $bsbRegex = '/^[\d]{3}-[\d]{3}$/';

    public function __construct()
    {

    }

    /**
     * @param array|TransactionInterface
     */
    public function generate($transactions)
    {
        if (! is_array($transactions)) {
            $transactions = array($transactions);
        }

        $this->addDescriptiveRecord();

        foreach ($transactions as $transaction) {
            if (! $transaction instanceof TransactionInterface) {
                throw new Exception('Transactions must implement TransactionInterface.');
            }

            $this->addDetailRecord($transaction);

            if ($transaction->getTransactionCode() === TransactionInterface::EXTERNALLY_INITIATED_DEBIT) {
                $this->debitTotal += $this->getAmount();
            } else {
                $this->creditTotal += $this->getAmount();
            }
        }

        $this->numberRecords = count($transactions);
        $this->addBatchControlRecord();

        return $this->abaString;
    }

    /**
     * Create the descriptive record line of the file.
     */
    private function addDescriptiveRecord()
    {
        // Record Type
        $line = DESCRIPTIVE_TYPE;

        // BSB
        if (! preg_match($this->bsbRegex, $this->bsb)) {
            throw new Exception('Descriptive record bsb is invalid. Required format is 000-000.');
        }

        $line .= $this->bsb;

        // Account Number
        if (! preg_match('/^[\d]{0,9}$/', $this->accountNumber)) {
            throw new Exception('Descriptive record account number is invalid. Must be up to 9 digits only.');
        }

        $line .= str_pad($this->accountNumber, 9, ' ', STR_PAD_LEFT);

        // Reserved - must be a single blank space
        $line .= ' ';

        // Sequence Number
        $line .= '01';

        // Bank Name
        if (! preg_match('/^[A-Z]{3}$/', $this->bankName)) {
            throw new Exception('Descriptive record bank name is invalid. Must be capital letter abbreviation of length 3.');
        }

        $line .= $this->bankName;

        // Reserved - must be seven blank spaces
        $line .= str_repeat(' ', 7);

        // User Name
        $line .= str_pad($this->accountName, 26, ' ', STR_PAD_RIGHT);

        // User ID
        if (! preg_match('/^[\d]{6}$/', $this->directEntryUserId)) {
            throw new Exception('Descriptive record direct entiry user ID is invalid. Must be 6 digits long.');
        }

        $line .= $this->directEntryUserId;

        // File Description
        $line .= str_pad($this->description, 12, ' ', STR_PAD_RIGHT);

        // Processing Date
        $line .= date('dmy');

        // Processing Time
        $line .= str_repeat(' ', 4);

        // Reserved - 36 blank spaces
        $line .= str_repeat(' ', 36);

        $this->addLine($line);
    }

    /**
     * Add a detail record for each transaction.
     */
    private function addDetailRecord(TransactionInterface $transaction)
    {
        // Record Type
        $line = DETAIL_TYPE;

        // BSB
        if (! preg_match($this->bsbRegex, $transaction->getBsb())) {
            throw new InvalidBsbException('Detail record bsb is invalid: '.$transaction->getBsb().'. Required format is 000-000.');
        }

        $line .= $transaction->getBsb();

        // Account Number
        if (! preg_match('/^[\d]{0,9}$/', $transaction->getAccountNumber())) {
            throw new Exception('Detail record account number is invalid. Must be up to 9 digits only.');
        }

        $line .= str_pad($transaction->getAccountNumber(), 9, ' ', STR_PAD_LEFT);

        // Indicator
        if ($transaction->getAccountNumber() && ! preg_match('/^W|X|Y| /', $transaction->getAccountNumber())) {
            throw new Exception('Detail transaction indicator is invalid. Must be one of W, X, Y or null.');
        }

        $line .= $transaction->getIndicator() ?: ' ';

        // Transaction Code
        if (! $this->validateTransactionCode($transaction->getTransactionCode())) {
            throw new Exception('Detail record transaction code invalid.');
        }

        $line .= $transaction->getTransactionCode();

        // Transaction Amount
        $line .= str_pad($transaction->getAmount(), 10, '0', STR_PAD_LEFT);

        // Account Name
        $line .= str_pad($transaction->getAccountName(), 32, ' ', STR_PAD_RIGHT);

        // Lodgement Reference
        $line .= str_pad($transaction->getReference(), 18, ' ', STR_PAD_RIGHT);

        // Trace BSB - already validated
        $line .= $this->bsb;

        // Trace Account Number - already validated
        $line .= str_pad($this->accountNumber, 9, ' ', STR_PAD_RIGHT);

        // Remitter Name - already validated
        $line .= str_pad($this->accountName, 16, ' ', STR_PAD_RIGHT);

        // Withholding amount
        $line .= str_pad($transaction->getTaxWithholding(), 8, '0', STR_PAD_LEFT);

        $this->addLine($line);
    }

    private function addBatchControlRecord()
    {
        $line = BATCH_TYPE;

        // BSB
        $line .= '999-999';

        // Reserved - must be twelve blank spaces
        $line .= str_repeat(' ', 12);

        // Batch Net Total - 10
        $line .= str_pad(($this->creditTotal - $this->debitTotal), 10, '0', STR_PAD_LEFT);

        // Batch Credits Total - 10
        $line .= str_pad($this->creditTotal, 10, '0', STR_PAD_LEFT);

        // Batch Debits Total - 10
        $line .= str_pad($this->debitTotal, 10, '0', STR_PAD_LEFT);

        // Reserved - must be 24 blank spaces
        $line .= str_repeat(' ', 24);

        // Number of records
        $line .= str_pad($this->numberRecords, 6, '0', STR_PAD_LEFT);

        // Reserved - must be 40 blank spaces
        $line .= str_repeat(' ', 40);

        $this->addLine($line, false);
    }

    private function addLine($line, $crlf = true)
    {
        $this->abaString .= $line.($crlf ? "\r\n" : "");
    }

    private function validateTransactionCode($transactionCode)
    {
        return in_array($transactionCode, array(
            TransactionInterface::EXTERNALLY_INITIATED_DEBIT,
            TransactionInterface::EXTERNALLY_INITIATED_CREDIT,
            TransactionInterface::AUSTRALIAN_GOVERNMENT_SECURITY_INTEREST,
            TransactionInterface::FAMILY_ALLOWANCE,
            TransactionInterface::PAYROLL_PAYMENT,
            TransactionInterface::PENSION_PAYMENT,
            TransactionInterface::ALLOTMENT,
            TransactionInterface::DIVIDEND,
            TransactionInterface::DEBENTURE_OR_NOTE_INTEREST
        ));
    }
}
