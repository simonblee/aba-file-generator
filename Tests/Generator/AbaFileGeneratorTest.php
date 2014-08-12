<?php

namespace AbaFileGenerator\Tests\Generator;

use \PHPUnit_Framework_TestCase;
use AbaFileGenerator\Generator\AbaFileGenerator;
use AbaFileGenerator\Tests\Fixtures\TransactionFixtures;

class AbaFileGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testRecordTypes()
    {
        $abaString = $this->generate();
        $abaLines = explode("\r\n", $abaString);

        $this->assertCount(6, $abaLines);
        $this->assertEquals('0', $abaLines[0][0]);
        $this->assertEquals('1', $abaLines[1][0]);
        $this->assertEquals('1', $abaLines[4][0]);
        $this->assertEquals('7', $abaLines[5][0]);

        return $abaLines;
    }

    /**
     * FORMAT OF DESCRIPTIVE RECORD - https://github.com/mjec/aba/blob/master/sample-with-comments.aba
     * S.P  E.P  LEN T A F NAME                DESCRIPTION
     *  1    1    1 F - - Record Type         Must be 0 for descriptive record.
     *  2    8    7 A L S BSB                 BSB of funds account (formatted 000-000) OR blank (ignored by WPC).
     *  9   17    9 A R S Account Number      Account number of fund account (inc leading zeros) OR blank (ignored by WPC).
     * 18   18    1 F - S Reserved            Must be a single blank space.
     * 19   20    2 N R Z Sequence Number     Generally 01. Sequence number of file in batch (starting from 01). Batches to
     *                                        be used where number of detail records exceeds maximum per file of 500.
     * 21   23    3 A - - Bank Name           Three letter APCA abbreviation for bank (CBA, NAB, ANZ, WPC).
     * 24   30    7 F - S Reserved            Must be seven blank spaces.
     * 31   56   26 A L S User Name           The name of the user supplying the file. Some banks must match account holder
     *                                        or be specified as "SURNAME Firstname". Must not be blank.
     * 57   62    6 N R Z DE User ID          Direct Entry user ID where allocated. Required for direct debits. For internet
     *                                        banking use CBA: 301500, WPC: 037819, ignored by NAB and ANZ.
     * 63   74   12 A L S File Description    A description of the contents of the file. Ignored by CBA.
     * 75   80    6 N - - Processing Date     Date to process transactions as DDMMYY.
     * 81   84    4 A L S Processing Time     Time to process transactions as 24 hr HHmm OR all spaces.
     * 85  120   36 F - S Reserved            Must be thirty six blank spaces.
     *
     * @depends testRecordTypes
     */
    public function testDescriptiveRecordEntry($abaLines)
    {
        $record = $abaLines[0];
        $ad = $this->getAccountDetails();

        $this->assertEquals(120, strlen($record));
        $this->assertEquals($ad['bsb'], substr($record, 1, 7));
        $this->assertEquals($ad['accountNumber'], substr($record, 8, 9));
        $this->assertEquals($ad['bankName'], substr($record, 20, 3));
        $this->assertStringStartsWith($ad['userName'], substr($record, 30, 26));
        $this->assertEquals($ad['directEntryUserId'], substr($record, 56, 6));
        $this->assertStringStartsWith($ad['description'], substr($record, 62, 12));
    }

    /**
     * FORMAT OF DETAIL RECORD - https://github.com/mjec/aba/blob/master/sample-with-comments.aba
     *
     * S.P  E.P  LEN T A F NAME                DESCRIPTION
     *   1    1    1 F - - Record Type         Must be 1 for detail record.
     *   2    8    7 A L S BSB                 BSB of target account (formatted 000-000).
     *   9   17    9 A R S Account Number      Account number of target account (inc leading zeros if part of account number).
     *  18   18    1 A L S Indicator           Must be one of: blank space (nothing indicated), N (this record changes details
     *                                         of payee as they occured before?), W (this is a dividend payment to a resident
     *                                         of a country with a double tax agreement), X (this is a dividend payment to a
     *                                         resident of any other country), Y (this is an interest payment to a non-resident
     *                                         of Australia). W, X and Y require that a withholding tax amount be specified.
     *  19   20    2 N R Z Transaction Code    Must be one of: 13 (externally initiated debit), 50 (externally initiated
     *                                         credit - normally what is required), 51 (Australian Government Security
     *                                         interest), 52 (Family Allowance), 53 (Payroll payment), 54 (Pension payment),
     *                                         55 (Allotment), 56 (Dividend), 57 (Debenture or note interest).
     *  21   30   10 N R Z Transaction Amount  Total amount of this transaction as zero-padded number of cents.
     *  31   62   32 A L S Account Name        Name target account is held in, normally as "SURNAME First Second Names".
     *  63   80   18 A L S Lodgement Reference Reference (narration) that appears on target's bank statement.
     *  81   87    7 A L S Trace BSB           BSB of fund (source) account (formatted 000-000).
     *  88   96    9 A L S Trace Account Num   Account number of fund (source) account (inc leading zeros if part of number).
     *  97  112   16 A L S Remitter Name       Name of remitter (appears on target's bank statement; must not be blank but
     *                                         some banks will replace with name fund account is held in).
     * 113  120    8 N R Z Withholding amount  Amount of withholding tax (if Indicator is W, X or Y) or all zeros. If not zero
     *                                         then will cause Indicator field to be ignored and tax to be withheld.
     *
     * @depends testDescriptiveRecordEntry
     */
    public function testDetailRecordEntry($abaLines)
    {
        $record = $abaLines[0];
        $ad = $this->getAccountDetails();

        // $this->assertEquals(120, strlen($record));
        // $this->assertEquals($ad['bsb'], substr($record, 1, 7));
        // $this->assertEquals($ad['accountNumber'], substr($record, 8, 9));
        // $this->assertEquals($ad['bankName'], substr($record, 20, 3));
        // $this->assertStringStartsWith($ad['userName'], substr($record, 30, 26));
        // $this->assertEquals($ad['directEntryUserId'], substr($record, 56, 6));
        // $this->assertStringStartsWith($ad['description'], substr($record, 62, 12));

        return $abaLines;
    }

    /**
     * FORMAT OF BAtCH CONTROL RECORD - https://github.com/mjec/aba/blob/master/sample-with-comments.aba
     *
     * S.P  E.P  LEN T A F NAME                DESCRIPTION
     *   1    1    1 F - - Record Type         Must be 7 for batch control record.
     *   2    8    7 F - - BSB                 Must be "999-999".
     *   9   20   12 F - S Reserved            Must be twelve blank spaces.
     *  21   30   10 N R Z Batch Net Total     Total of credits minus total of debits in batch as zero-padded number of cents.
     *  31   40   10 N R Z Batch Credits Total Total of credits in batch as zero-padded number of cents. Some banks permit
     *                                         this to be ignored by placing all zeros or all spaces.
     *  41   50   10 N R Z Batch Debits Total  Total of debits in batch as zero-padded number of cents. Some banks permit
     *                                         this to be ignored by placing all zeros or all spaces.
     *  51   74   24 F - S Reserved            Must be twenty four blank spaces.
     *  75   80    6 N R Z Number of records   Must be the total number of detail records in the batch, zero-padded.
     *  81  120   40 F - S Reserved            Must be forty blank spaces.
     *
     * @depends testDetailRecordEntry
     */
    public function testBatchControlRecord($abaLines)
    {
        $abaString = $this->generate();
        $abaLines = explode("\r\n", $abaString);
        $record = $abaLines[0];
        $ad = $this->getAccountDetails();

        // $this->assertEquals(120, strlen($record));
        // $this->assertEquals($ad['bsb'], substr($record, 1, 7));
        // $this->assertEquals($ad['accountNumber'], substr($record, 8, 9));
        // $this->assertEquals($ad['bankName'], substr($record, 20, 3));
        // $this->assertStringStartsWith($ad['userName'], substr($record, 30, 26));
        // $this->assertEquals($ad['directEntryUserId'], substr($record, 56, 6));
        // $this->assertStringStartsWith($ad['description'], substr($record, 62, 12));

        return $abaLines;
    }

    private function generate()
    {
        $ad = $this->getAccountDetails();
        $generator = new AbaFileGenerator($ad['bsb'], $ad['accountNumber'], $ad['bankName'], $ad['userName'], $ad['remitterName'], $ad['directEntryUserId'], $ad['description']);
        $fixtures = new TransactionFixtures();

        return $generator->generate($fixtures->getTransactions());
    }

    private function getAccountDetails()
    {
        return array(
            'bsb' => '123-123',
            'accountNumber' => '12345678',
            'bankName' => 'CBA',
            'userName' => 'Some name',
            'remitterName' => 'From some guy',
            'directEntryUserId' => '999999',
            'description' => 'Payroll'
        );
    }
}
