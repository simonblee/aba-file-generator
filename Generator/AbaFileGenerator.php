<?php

namespace AbaFileGenerator\Generator;

use AbaFileGenerator\Model\TransactionInterface;
use AbaFileGenerator\Model\TransactionCode;
use DateTime;
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
     * @var integer
     */
    private $numberRecords = 0;

    /**
     * @var string
     */
    private $bsb;

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var string
     */
    private $bankName;

    /**
     * The name of the user supplying the aba file. Some banks must match
     * account holder or be specified as "SURNAME Firstname".
     *
     * @var string
     */
    private $userName;

    /**
     * Appears on recipient's statement as origin of transaction.
     *
     * @var string
     */
    private $remitter;

    /**
     * @var string
     */
    private $directEntryUserId;

    /**
     * @var string
     */
    private $description;

    /**
     * The date transactions are released to all Financial Institutions.
     * 
     * Defaults to today.
     * 
     * @var int|string|DateTime
     */
    private $processingDate;

    /**
     * Validates that the BSB is 6 digits with a dash in the middle: 123-456
     */
    private $bsbRegex = '/^[\d]{3}-[\d]{3}$/';

    public function __construct($bsb, $accountNumber, $bankName, $userName, $remitter, $directEntryUserId, $description)
    {
        $this->bsb = $bsb;
        $this->accountNumber = $accountNumber;
        $this->bankName = $bankName;
        $this->userName = $userName;
        $this->remitter = $remitter;
        $this->directEntryUserId = $directEntryUserId;
        $this->description = $description;
        $this->processingDate = time();
    }

    /**
     * Set the processing date.
     * 
     * @param int|string|DateTime $date
     */
    public function setProcessingDate($date)
    {
        $this->processingDate = $date;

        return $this;
    }

    /**
     * @param array|TransactionInterface
     */
    public function generate($transactions)
    {
        if (! is_array($transactions)) {
            $transactions = array($transactions);
        }

        $this->validateDescriptiveRecord();
        $this->addDescriptiveRecord();

        foreach ($transactions as $transaction) {
            $this->validateDetailRecord($transaction);
            $this->addDetailRecord($transaction);

            if ($transaction->getTransactionCode() === TransactionCode::EXTERNALLY_INITIATED_DEBIT) {
                $this->debitTotal += $transaction->getAmount();
            } else {
                $this->creditTotal += $transaction->getAmount();
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
        $line = self::DESCRIPTIVE_TYPE;

        // Intentionally left blank
       $line .= str_repeat(' ', 17);

        // Sequence Number
        $line .= '01';

        // Bank Name
        $line .= $this->bankName;

        // Reserved - must be seven blank spaces
        $line .= str_repeat(' ', 7);

        // User Name
        $line .= str_pad($this->userName, 26, ' ', STR_PAD_RIGHT);

        // User ID
        $line .= $this->directEntryUserId;

        // File Description
        $line .= str_pad($this->description, 12, ' ', STR_PAD_RIGHT);

        // Processing Date
        $line .= date('dmy', is_numeric($this->processingDate) ? $this->processingDate : strtotime($this->processingDate));

        // Reserved - 40 blank spaces
        $line .= str_repeat(' ', 40);

        $this->addLine($line);
    }

    /**
     * Add a detail record for each transaction.
     */
    private function addDetailRecord(TransactionInterface $transaction)
    {
        // Record Type
        $line = self::DETAIL_TYPE;

        // BSB
        $line .= $transaction->getBsb();

        // Account Number
        $line .= str_pad($transaction->getAccountNumber(), 9, 0, STR_PAD_LEFT);

        // Indicator
        $line .= $transaction->getIndicator() ?: ' ';

        // Transaction Code
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
        $line .= str_pad($this->accountNumber, 9, '0', STR_PAD_LEFT);

        // Remitter Name - already validated
        $remitter = $transaction->getRemitter() ?: $this->remitter;
        $line .= str_pad($remitter, 16, ' ', STR_PAD_RIGHT);

        // Withholding amount
        $line .= str_pad($transaction->getTaxWithholding(), 8, '0', STR_PAD_LEFT);

        $this->addLine($line);
    }

    private function addBatchControlRecord()
    {
        $line = self::BATCH_TYPE;

        // BSB
        $line .= '999-999';

        // Reserved - must be twelve blank spaces
        $line .= str_repeat(' ', 12);

        // Batch Net Total
        $line .= str_pad(abs($this->creditTotal - $this->debitTotal), 10, '0', STR_PAD_LEFT);

        // Batch Credits Total
        $line .= str_pad($this->creditTotal, 10, '0', STR_PAD_LEFT);

        // Batch Debits Total
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

    /**
     * Validate the parts of the descriptive record.
     */
    private function validateDescriptiveRecord()
    {
        if (! preg_match('/^[A-Z]{3}$/', $this->bankName)) {
            throw new Exception('Descriptive record bank name is invalid. Must be capital letter abbreviation of length 3.');
        }

        if (! preg_match('/^[A-Za-z\s+]{0,26}$/', $this->userName)) {
            throw new Exception('Descriptive record user name is invalid. Must be letters only and up to 26 characters long.');
        }

        if (! preg_match('/^[\d]{6}$/', $this->directEntryUserId)) {
            throw new Exception('Descriptive record direct entry user ID is invalid. Must be 6 digits long.');
        }

        if (! preg_match('/^[A-Za-z\s]{0,12}$/', $this->description)) {
            throw new Exception('Descriptive record description is invalid. Must be letters only and up to 12 characters long.');
        }
    }

    /**
     * Validate the parts of the transaction.
     */
    private function validateDetailRecord($transaction)
    {
        if (! $transaction instanceof TransactionInterface) {
            throw new Exception('Transactions must implement TransactionInterface.');
        }

        if (! preg_match($this->bsbRegex, $transaction->getBsb())) {
            throw new Exception('Detail record bsb is invalid: '.$transaction->getBsb().'. Required format is 000-000.');
        }

        if (! preg_match('/^[\d]{0,9}$/', $transaction->getAccountNumber())) {
            throw new Exception('Detail record account number is invalid. Must be up to 9 digits only.');
        }

        if ($transaction->getIndicator() && ! preg_match('/^W|X|Y| /', $transaction->getIndicator())) {
            throw new Exception('Detail record transaction indicator is invalid. Must be one of W, X, Y or null.');
        }

        if (! preg_match('/^[A-Za-z0-9\s+\-]{0,18}$/', $transaction->getReference())) {
            throw new Exception('Detail record reference is invalid: "'.$transaction->getReference().'". Must respect [A-Za-z0-9\s+\-] and up to 18 characters long.');
        }

        if ($transaction->getRemitter() && ! preg_match('/^[A-Za-z\s+]{0,16}$/', $transaction->getRemitter())) {
            throw new Exception('Detail record remitter is invalid. Must be letters only and up to 16 characters long.');
        }

        if (! $this->validateTransactionCode($transaction->getTransactionCode())) {
            throw new Exception('Detail record transaction code invalid. Must be a constant from AbaFileGenerator\Model\TransactionCode.');
        }
    }

    private function validateTransactionCode($transactionCode)
    {
        return in_array($transactionCode, array(
            TransactionCode::EXTERNALLY_INITIATED_DEBIT,
            TransactionCode::EXTERNALLY_INITIATED_CREDIT,
            TransactionCode::AUSTRALIAN_GOVERNMENT_SECURITY_INTEREST,
            TransactionCode::FAMILY_ALLOWANCE,
            TransactionCode::PAYROLL_PAYMENT,
            TransactionCode::PENSION_PAYMENT,
            TransactionCode::ALLOTMENT,
            TransactionCode::DIVIDEND,
            TransactionCode::DEBENTURE_OR_NOTE_INTEREST
        ));
    }
}
