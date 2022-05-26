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

.DEFAULT_GOAL = help
.PHONY        = Help build up start down logs sh composer vendor sf cc

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ———————————————————————————————————————————————————————————————
build: ## Builds the Docker images
		@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mod (no logs)
		@$(DOCKER_COMP) up --detach
		echo "App is running at http://127.0.0.1:8090"

start: build up ## Build and start the containers

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

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c="req phpunit/phpunit"
		@$(eval c ?=)
		@$(COMPOSER) $(c)

## —— Symfony ——————————————————————————————————————————————————————————————
schema-update:
	#make cache
	@$(SYMFONY) doctrine:database:create --if-not-exists
	@$(SYMFONY) doctrine:migrations:migrate

run-fixtures:
	@$(SYMFONY) doctrine:fixtures:load

cache:
	@$(SYMFONY) cache:clear --env=dev


#exit:
#	docker stop inserjeune-v2 || true && docker rm inserjeune-v2 || true
#	docker stop mysql-8-inserjeune || true && docker rm mysql-8-inserjeune || true
#
