# ABA File Generator
[![Build Status](https://secure.travis-ci.org/simonblee/generator-marionette.png?branch=master)](https://travis-ci.org/simonblee/generator-marionette)

## Overview
Generates an aba file for bulk banking transactions with Australian banks.

## Project Status:
This library is very new and all test cases are not accounted for. It is recommended
that you run a few manual tests and validate the file with your banking institute.

As always, if you notice any errors please submit an issue or even better, a pull request.

## License
[MIT License](http://en.wikipedia.org/wiki/MIT_License)

## Usage
Create a generator object with the descriptive type information for this aba file:

Create an array (or single) of objects implementing TransactionInterface. A simple Transaction object
is provided with the library but may be too simple for your project:

Generate the aba string and save into a file (or whatever else you want):

