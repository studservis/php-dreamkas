install:
	docker build -t php-dreamkas .
	./bin/composer install

test:
	./bin/php vendor/bin/phpunit
