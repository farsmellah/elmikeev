.PHONY: init migrate import all orders sales stocks incomes

init:
	composer install --no-interaction --prefer-dist
	cp .env.example .env
	php artisan key:generate --force

migrate:
	php artisan migrate
