# ABA File Generator (NAB specs)

## Overview
Generates an aba file for bulk banking transactions with Australian banks.

## License
[MIT License](http://en.wikipedia.org/wiki/MIT_License)

## Installation
Copy the files where needed or install via composer:
```bash
composer require bruno-rodrigues/aba-file-generator
```

## Usage
Create a generator object with the descriptive type information for this aba file:
```php
use AbaFileGenerator\Generator\AbaFileGenerator;

$generator = new AbaFileGenerator(
    '123-456', // bsb
    '12345678', // account number
    'CBA', // bank name
    'User Name',
    'Remitter',
    '175029', // direct entry id for CBA
    'Payroll' // description
);

// Set a custom processing date if required
$generator->setProcessingDate('tomorrow');
```

Create an object or array of objects implementing `AbaFileGenerator\Model\TransactionInterface`. A simple Transaction object
is provided with the library but may be too simple for your project:
```php
use AbaFileGenerator\Model\Transaction;

$transaction = new Transaction();
$transaction->setAccountName(...);
$transaction->setAccountNumber(...);
$transaction->setBsb(...);
$transaction->setTransactionCode(...);
$transaction->setReference(...);
$transaction->setAmount(...);
```

Generate the aba string and save into a file (or whatever else you want):
```php
$abaString = $generator->generate($transaction); // $transaction could also be an array here
file_put_contents('/my/aba/file.aba', $abaString);
```

## References
- https://www.nab.com.au/content/dam/nabconnectcontent/file-formats/nab-connect-consolidated-file-format-specification_v0.05-pdf.pdf
