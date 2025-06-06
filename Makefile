.PHONY: up down migrate test setup-test-db

# Start the application
up:
	docker compose up -d

# Stop the application
down:
	docker compose down

# Run migrations
migrate:
	docker compose exec app php bin/console doctrine:migrations:migrate

# Setup test database
#TODO: implement
setup-test-db:

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
