<?php

namespace AbaFileGenerator\Tests\Generator;

use AbaFileGenerator\Generator\AbaFileGenerator;
use AbaFileGenerator\Model\TransactionCode;
use AbaFileGenerator\Tests\Fixtures\TransactionFixtures;
use PHPUnit_Framework_TestCase;

class AbaFileGeneratorTest extends PHPUnit_Framework_TestCase
{
    private $fixtureTransactions;
    private $abaLines;

    protected function setUp()
    {
        parent::setUp();
        $fixtures                  = new TransactionFixtures();
        $this->fixtureTransactions = $fixtures->getTransactions();
        $abaString                 = $this->generate();
        $this->abaLines            = explode("\r\n", $abaString);
    }

    public function testDescriptiveRecordEntry()
    {
        $record         = $this->abaLines[0];
        $accountDetails = $this->getAccountDetails();

        $this->assertSame(120, strlen($record));
        $this->assertSame('0', substr($record, 0, 1));
        $this->assertSame(str_repeat(' ', 17), substr($record, 1, 17));
        $this->assertSame('01', substr($record, 18, 2));
        $this->assertSame($accountDetails['bankName'], substr($record, 20, 3));
        $this->assertSame(str_repeat(' ', 7), substr($record, 23, 7));
        $this->assertSame(
            $accountDetails['userName'] . str_repeat(' ', 26 - strlen($accountDetails['userName'])),
            substr($record, 30, 26)
        );
        $this->assertSame($accountDetails['directEntryUserId'], substr($record, 56, 6));
        $this->assertSame(
            $accountDetails['description'] . str_repeat(' ', 12 - strlen($accountDetails['description'])),
            substr($record, 62, 12)
        );
        $this->assertSame(date('dmy'), substr($record, 74, 6));
        $this->assertSame(str_repeat(' ', 40), substr($record, 80, 40));
    }

    public function testDetailRecordEntry()
    {
        $accountDetails   = $this->getAccountDetails();
        $transactionCount = count($this->fixtureTransactions);
        for ($i = 0; $i < $transactionCount; $i++) {
            $record      = $this->abaLines[$i + 1]; // first line is reserved for descriptive record
            $transaction = $this->fixtureTransactions[$i];
            $this->assertSame(120, strlen($record));
            $this->assertSame('1', substr($record, 0, 1));
            $this->assertSame($transaction->getBsb(), substr($record, 1, 7));
            if (strpos($transaction->getBsb(), '08') === 0) { // NAB accounts must be numeric, zero filled.
                $this->assertSame(
                    str_repeat('0', 9 - strlen($transaction->getAccountNumber())) . $transaction->getAccountNumber(),
                    substr($record, 8, 9)
                );
            } else { // Non-Nab accounts can be alphanumeric, right justified, blank filled.
                $this->assertSame(
                    str_repeat(' ', 9 - strlen($transaction->getAccountNumber())) . $transaction->getAccountNumber(),
                    substr($record, 8, 9)
                );
            }
            $this->assertSame(' ', substr($record, 17, 1));
            $this->assertSame($transaction->getTransactionCode(), substr($record, 18, 2));
            $this->assertSame(
                str_repeat('0', 10 - strlen($transaction->getAmount())) . $transaction->getAmount(),
                substr($record, 20, 10)
            );
            $this->assertSame(
                $transaction->getAccountName() . str_repeat(' ', 32 - strlen($transaction->getAccountName())),
                substr($record, 30, 32)
            );
            $this->assertSame(
                $transaction->getReference() . str_repeat(' ', 18 - strlen($transaction->getReference())),
                substr($record, 62, 18)
            );
            $this->assertSame($accountDetails['bsb'], substr($record, 80, 7));
            $this->assertSame(
                str_repeat('0', 9 - strlen($accountDetails['accountNumber'])) . $accountDetails['accountNumber'],
                substr($record, 87, 9)
            );
            $this->assertSame(
                $accountDetails['remitterName'] . str_repeat(' ', 16 - strlen($accountDetails['remitterName'])),
                substr($record, 96, 16)
            );
            $this->assertSame(str_repeat('0', 8), substr($record, 112, 8));
        }
    }

    public function testBatchControlRecord()
    {
        $record = end($this->abaLines);

        $totalCredits = $totalDebits = 0;
        foreach ($this->fixtureTransactions as $fixtureTransaction) {
            if ($fixtureTransaction->getTransactionCode() === TransactionCode::EXTERNALLY_INITIATED_DEBIT) {
                $totalDebits += $fixtureTransaction->getAmount();
            } else {
                $totalCredits += $fixtureTransaction->getAmount();
            }
        }

        $this->assertSame(120, strlen($record));
        $this->assertSame('7', substr($record, 0, 1));
        $this->assertSame('999-999', substr($record, 1, 7));
        $this->assertSame(str_repeat(' ', 12), substr($record, 8, 12));
        $net = abs($totalCredits - $totalDebits);
        $this->assertSame(str_repeat('0', 10 - strlen($net)) . $net, substr($record, 20, 10));
        $this->assertSame(str_repeat('0', 10 - strlen($totalCredits)) . $totalCredits, substr($record, 30, 10));
        $this->assertSame(str_repeat('0', 10 - strlen($totalDebits)) . $totalDebits, substr($record, 40, 10));
        $this->assertSame(str_repeat(' ', 24), substr($record, 50, 24));
        $transactionCount = count($this->fixtureTransactions);
        $this->assertSame(str_repeat('0', 6 - strlen($transactionCount)) . $transactionCount, substr($record, 74, 6));
        $this->assertSame(str_repeat(' ', 40), substr($record, 80, 40));
    }

    private function generate()
    {
        $ad        = $this->getAccountDetails();
        $generator = new AbaFileGenerator(
            $ad['bsb'], $ad['accountNumber'], $ad['bankName'], $ad['userName'],
            $ad['remitterName'], $ad['directEntryUserId'], $ad['description']
        );

        return $generator->generate($this->fixtureTransactions);
    }

    private function getAccountDetails()
    {
        return [
            'bsb'               => '081-123',
            'accountNumber'     => '12345678',
            'bankName'          => 'CBA',
            'userName'          => 'Some name',
            'remitterName'      => 'From some guy',
            'directEntryUserId' => '999999',
            'description'       => 'Payroll'
        ];
    }
}
