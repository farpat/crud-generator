.PHONY: test dev install help update
.DEFAULT_GOAL= help

COM_COLOR   = \033[0;34m
OBJ_COLOR   = \033[0;36m
OK_COLOR    = \033[0;32m
ERROR_COLOR = \033[0;31m
WARN_COLOR  = \033[0;33m
NO_COLOR    = \033[m

# Set $(FRAMEWORK)
FRAMEWORK=not
ifneq ("$(wildcard bin)", "")
	FRAMEWORK=symfony
else ifneq ("$(wildcard app)", "")
	FRAMEWORK=laravel
endif

# set $(PHP_SERVER)
ifeq ($(FRAMEWORK),symfony)
	PHP_SERVER=bin/console server:run
else ifeq ($(FRAMEWORK),laravel)
	PHP_SERVER=php artisan serve
else
	PHP_SERVER=php -S localhost:8000 -t public/ -d display_errors=1
endif

FILTER?=tests
DIR?=


node_modules:
ifneq ("$(wildcard package.json)", "")
	npm i
endif

vendor: composer.json
	composer install

install: vendor node_modules ## Install the composer dependencies and npm dependencies

update: ## Update the composer dependencies and npm dependencies
	composer update
	npm run update
	npm i

clean: ## Clean composer dependencies and npm dependencies
	rm -rf vendor node_modules package-lock.json composer.lock

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(OK_COLOR)%-15s$(NO_COLOR) %s\n", $$1, $$2}'

test: install ## Run unit tests
ifneq ($(FRAMEWORK),symfony)
	vendor/bin/phpunit $(DIR) --filter $(FILTER) --stop-on-failure
else
	bin/phpunit $(DIR) --filter $(FILTER) --stop-on-failure
endif

dev: install ## Run development servers
	tmux new-session "$(PHP_SERVER)" \;\
		split-window -h "npm run dev" \;\
		split-window -v "laravel-echo-server start" \;\

build: install ## Build project in production
	npm run build

migrate: install ## Refresh database by running new migrations
ifeq ($(FRAMEWORK),symfony)
	bin/console doctrine:database:drop --force
	bin/console doctrine:database:create
	bin/console doctrine:migration:migrate -n
	bin/console doctrine:fixtures:load -n
else
	php artisan migrate:fresh --seed
endif

