<?php

namespace AbaFileGenerator\Tests\Generator;

use \PHPUnit_Framework_TestCase;
use AbaFileGenerator\Generator\AbaFileGenerator;
use AbaFileGenerator\Tests\Fixtures\TransactionFixtures;

class AbaFileGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $bsb = '123-123';
        $accountNumber = '12345678';
        $bankName = 'CBA';
        $userName = 'Some name';
        $remitterName = 'From some guy';
        $directEntryUserId = '999999';
        $description = 'Payroll';
        $generator = new AbaFileGenerator($bsb, $accountNumber, $bankName, $userName, $remitterName, $directEntryUserId, $description);
        $fixtures = new TransactionFixtures();

        $abaString = $generator->generate($fixtures->getTransactions());

        echo "\n".$abaString;
    }
}
