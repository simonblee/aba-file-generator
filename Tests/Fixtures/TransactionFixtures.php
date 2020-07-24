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
        $t4->setAccountNumber('123456');
        $t4->setBsb('082-888'); // NAB
        $t4->setAmount(123456);
        $t4->setTransactionCode(TransactionCode::PAYROLL_PAYMENT);
        $t4->setReference('12345-12345');

        return array($t1, $t2, $t3, $t4);
    }
}

