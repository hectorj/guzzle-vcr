all: phpstan-result.checkstyle.xml phpunit-result.junit.xml phpcs-result.checkstyle.xml

SRC := $(shell find ./src | sed 's/ /\\ /g')
TESTS := $(shell find ./tests | sed 's/ /\\ /g')
VENDOR := $(shell find ./vendor | sed 's/ /\\ /g')

phpstan-result.checkstyle.xml: $(SRC) $(TESTS) $(VENDOR)
	./vendor/bin/phpstan analyse -l 5 src tests --errorFormat=checkstyle > ./phpstan-result.checkstyle.xml
	@echo ""

phpunit-result.junit.xml: $(SRC) $(TESTS) $(VENDOR) phpunit.xml
	./vendor/bin/phpunit --configuration ./phpunit.xml --log-junit ./phpunit-result.junit.xml

phpcs-result.checkstyle.xml: $(SRC) $(TESTS) $(VENDOR) phpcs.xml
	./vendor/bin/phpcs  --report-checkstyle=phpcs-result.checkstyle.xml --report-full

vendor/: composer.lock
	composer install

composer.lock: composer.json
	@echo "\nPlease update your composer.lock\n"
	@exit 1

.PHONY: all
