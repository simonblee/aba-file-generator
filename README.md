# ABA File Generator

## Overview
Generates an aba file for bulk banking transactions with Australian banks.

## Project Status:
This library is very new and all test cases are not accounted for. It is recommended
that you run a few manual tests and validate the file with your banking institute.

As always, if you notice any errors please submit an issue or even better, a pull request.

## License
[MIT License](http://en.wikipedia.org/wiki/MIT_License)

## Installation
Copy the files where needed or install via composer:
```bash
composer require simonblee/aba-file-generator
```

## Usage
Create a generator object with the descriptive type information for this aba file:
```php
use AbaFileGenerator\Model\Transaction;
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
- http://www.anz.com/Documents/AU/corporate/clientfileformats.pdf
- http://www.cemtexaba.com/aba-format/cemtex-aba-file-format-details.html
- https://github.com/mjec/aba/blob/master/sample-with-comments.aba
