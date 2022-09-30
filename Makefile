# Executables (local)
DOCKER_COMP = docker-compose
DOCKER = docker

# Docker containers
PHP_CONT = $(DOCKER) exec -it inserjeune-v2
MYSQL_CONT = $(DOCKER) exec -it mysql-inserjeune-v2

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
NPM  	 = $(PHP_CONT) npm
SY  	 = $(PHP_CONT) symfony

.DEFAULT_GOAL = help
.PHONY        = Help build up start down logs sh composer vendor sf cc

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker
build: ## Builds the Docker images removing cache
		@$(DOCKER_COMP) build --pull --no-cache

build-cache: ## Builds the Docker images
		@$(DOCKER_COMP) build --pull

up: ## Start the docker hub in detached mod (no logs)
		@$(DOCKER_COMP) up --detach

start: build-cache up ## Build and start the containers

down: ## Stop the docker hub
		@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
		@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the App container
		@$(PHP_CONT) sh

sh-db: ## Connect to the MYSQL container
		@$(MYSQL_CONT) sh

stop: ## Stop a container, pass the pass the parameter "c=" to run a given command, exple: make stop c="app"
		@$(eval c ?=)
		@$(DOCKER) stop $(c) || true && @$(DOCKER) rm $(c) || true

## —— Composer
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c="req phpunit/phpunit"
		@$(eval c ?=)
		@$(COMPOSER) $(c)

composer-install: ## Run composer install with no interaction
		echo "Installing packages with composer..."
		@$(COMPOSER) install --apcu-autoloader

## —— Symfony
server-run: ## Run symfony server with "symfony server:run"
	@$(SY) server:start
	echo "App is running at http://127.0.0.1:8090"

server-watch: ## Run webpack with "npm run watch"
	@$(NPM) run watch

schema-update: ## Create database and run migrations
	echo "Creating database and schema..."
	@$(SYMFONY) doctrine:database:drop --force
	@$(SYMFONY) doctrine:database:create --if-not-exists  --no-interaction
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

run-fixtures: ## Load fixtures
	echo "Loading fixtures..."
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

cache: ## Clear cache
	echo "Clear cache..."
	@$(SYMFONY) cache:clear --env=dev

install: ## Install the project on local (remove/create database, run migrations, load fixture)
	 composer install && php bin/console d:d:d --force && php bin/console d:d:c && php bin/console d:m:m -n && php bin/console d:f:l -n

install-front: ## Install npm packages and build
	 npm install && npm run build

run-app: start composer-install cache schema-update run-fixtures server-run
