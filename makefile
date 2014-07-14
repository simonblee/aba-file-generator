.PHONY: test

test:
	vendor/bin/phpunit -c Tests/ --log-junit log.xml
	vendor/bin/phpcs -n --standard=PSR2 --extensions=php Generator/ Model/ Tests/
