.PHONY: tests

tests:
	sudo docker run -v $$(pwd):/app --rm php:5.4-cli bash -c 'cd /app && php ./vendor/bin/phpunit'
