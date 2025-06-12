.PHONY: up down build migrate test setup-test-db drop create recreate migration fixtures

# Start the application
up:
	docker compose up -d

# Stop the application
down:
	docker compose down

build:
	docker compose build

drop:
	docker compose exec app php bin/console doctrine:database:drop --force

create:
	docker compose exec app php bin/console doctrine:database:create

recreate: drop create

migration:
	docker compose exec app php bin/console make:migration

# Run migrations
migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate

fixtures:
	docker compose exec app php bin/console doctrine:fixtures:load

# Setup test database
setup-test-db:
	docker compose exec mysql mysql -uroot -psecret -e "CREATE DATABASE IF NOT EXISTS symfony_test;"
	#docker compose exec app php bin/console doctrine:migrations:migrate --env=test

# Run tests
test: setup-test-db
	docker compose exec app php bin/phpunit

# Setup project from scratch
setup: build up
	docker compose exec app composer install
	docker compose exec app php bin/console doctrine:migrations:migrate
	@echo "Project setup completed!"

# Show logs
logs:
	docker compose logs -f

# Enter app container
shell:
	docker compose exec app bash
