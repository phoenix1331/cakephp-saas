.PHONY: up down build shell migrate seed test logs fresh lint lint-fix css css-watch help

up: ## start containers in the background
	@docker compose up -d

down: ## stop and remove containers
	@docker compose down

build: ## rebuild the app image
	@docker compose build

shell: ## open a shell in the app container
	@docker compose exec app bash

migrate: ## run pending database migrations
	@docker compose exec app bin/cake migrations migrate

seed: ## seed the database
	@docker compose exec app bin/cake migrations seed

test: ## run the test suite
	@docker compose exec app vendor/bin/phpunit

logs: ## tail app container logs
	@docker compose logs -f app

lint: ## check code style with phpcs
	@docker compose exec app vendor/bin/phpcs

lint-fix: ## fix code style issues with phpcbf
	@docker compose exec app vendor/bin/phpcbf

css: ## build Tailwind CSS once (runs on the host, not in the container)
	@npx @tailwindcss/cli -i webroot/css/src/app.css -o webroot/css/app.css --minify

css-watch: ## rebuild Tailwind CSS on file changes
	@npx @tailwindcss/cli -i webroot/css/src/app.css -o webroot/css/app.css --watch

fresh: ## drop and rebuild the database, then migrate and seed
	@docker compose exec app bin/cake migrations rollback --target=0 --force
	@docker compose exec app bin/cake migrations migrate
	@docker compose exec app bin/cake migrations seed

help: ## list available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-10s\033[0m %s\n", $$1, $$2}'
