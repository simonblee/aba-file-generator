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
