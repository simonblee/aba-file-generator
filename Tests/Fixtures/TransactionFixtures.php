<?php

namespace AbaFileGenerator\Tests\Fixtures;

use AbaFileGenerator\Model\TransactionCode;
use AbaFileGenerator\Model\Transaction;

class TransactionFixtures
{
    public function getTransactions()
    {
        $t1 = new Transaction();
        $t1->setAccountName('John Smith');
        $t1->setAccountNumber('098765');
        $t1->setBsb('234-456');
        $t1->setAmount(345);
        $t1->setTransactionCode(TransactionCode::EXTERNALLY_INITIATED_DEBIT);
        $t1->setReference('A direct debit');

        $t2 = new Transaction();
        $t2->setAccountName('Mary Jane');
        $t2->setAccountNumber('67832');
        $t2->setBsb('123-456');
        $t2->setAmount(8765);
        $t2->setTransactionCode(TransactionCode::EXTERNALLY_INITIATED_CREDIT);
        $t2->setReference('For dinner');

        $t3 = new Transaction();
        $t3->setAccountName('Borris Becker');
        $t3->setAccountNumber('84736');
        $t3->setBsb('098-765');
        $t3->setAmount(7546);
        $t3->setTransactionCode(TransactionCode::PAYROLL_PAYMENT);
        $t3->setReference('Your salary');

        $t4 = new Transaction();
        $t4->setAccountName('Some Dude');
        $t4->setAccountNumber('123456789');
        $t4->setBsb('888-888');
        $t4->setAmount(123456);
        $t4->setTransactionCode(TransactionCode::PAYROLL_PAYMENT);
        $t4->setReference('Your salary');

        return array($t1, $t2, $t3, $t4);
    }
}

// 0123-123 12345678 01CBA       Some name                 999999Payroll     150714
// 1234-456   098765 130000000345John Smith                      A direct debit    123-12312345678                 00000000
// 1123-456    67832 500000008765Mary Jane                       For dinner        123-12312345678                 00000000
// 1098-765    84736 530000007546Borris Becker                   Your salary       123-12312345678                 00000000
// 1888-888123456789 530000123456Some Dude                       Your salary       123-12312345678                 00000000
// 7999-999            000013942200001397670000000345                        000004

// 0067-102 12341234 01CBA       Smith John Allan          301500ABA Test    0704131530
// 1062-692 43214321 500000000001Smith Joan Emma                 ABA Test CR       067-102 12341234Mr John Smith   00000000
// 7999-999            000000000100000000010000000000                        000001

// $expectedDetailRecords = "";
// $expectedDetailRecords .= "1";
// $expectedDetailRecords .= "1";
// $expectedDetailRecords .= "1";
// $expectedDetailRecords .= "1";


// S.P  E.P  LEN T A F NAME                DESCRIPTION
//   1    1    1 F - - Record Type         Must be 1 for detail record.
//   2    8    7 A L S BSB                 BSB of target account (formatted 000-000).
//   9   17    9 A R S Account Number      Account number of target account (inc leading zeros if part of account number).
//  18   18    1 A L S Indicator           Must be one of: blank space (nothing indicated), N (this record changes details
//                                         of payee as they occured before?), W (this is a dividend payment to a resident
//                                         of a country with a double tax agreement), X (this is a dividend payment to a
//                                         resident of any other country), Y (this is an interest payment to a non-resident
//                                         of Australia). W, X and Y require that a withholding tax amount be specified.
//  19   20    2 N R Z Transaction Code    Must be one of: 13 (externally initiated debit), 50 (externally initiated
//                                         credit - normally what is required), 51 (Australian Government Security
//                                         interest), 52 (Family Allowance), 53 (Payroll payment), 54 (Pension payment),
//                                         55 (Allotment), 56 (Dividend), 57 (Debenture or note interest).
//  21   30   10 N R Z Transaction Amount  Total amount of this transaction as zero-padded number of cents.
//  31   62   32 A L S Account Name        Name target account is held in, normally as "SURNAME First Second Names".
//  63   80   18 A L S Lodgement Reference Reference (narration) that appears on target's bank statement.
//  81   87    7 A L S Trace BSB           BSB of fund (source) account (formatted 000-000).
//  88   96    9 A L S Trace Account Num   Account number of fund (source) account (inc leading zeros if part of number).
//  97  112   16 A L S Remitter Name       Name of remitter (appears on target's bank statement; must not be blank but
//                                         some banks will replace with name fund account is held in).
// 113  120    8 N R Z Withholding amount  Amount of withholding tax (if Indicator is W, X or Y) or all zeros. If not zero
//                                         then will cause Indicator field to be ignored and tax to be withheld.
