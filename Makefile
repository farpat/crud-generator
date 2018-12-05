.PHONY: test dev install help update clean
.DEFAULT_GOAL= help

INFO_COLOR      = \033[0;34m
PRIMARY_COLOR   = \033[0;36m
SUCCESS_COLOR   = \033[0;32m
DANGER_COLOR    = \033[0;31m
WARNING_COLOR   = \033[0;33m
NO_COLOR        = \033[m


FILTER      ?= tests
DIR         ?=


node_modules:
ifneq ("$(wildcard package.json)", "")
	npm i
endif

vendor: composer.json
	@composer install

install: vendor node_modules ## Install the composer dependencies and npm dependencies

update: ## Update the composer dependencies and npm dependencies
	@composer update
	@npm run update
	@npm i

clean: ## Clean composer dependencies and npm dependencies
	@rm -rf vendor node_modules package-lock.json composer.lock symfony.lock

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(PRIMARY_COLOR)%-15s$(NO_COLOR) %s\n", $$1, $$2}'

test: install ## Run unit tests (parameters : DIR:tests/Feature/LoginTest.php || FILTER:get)
	@bin/phpunit $(DIR) --filter $(FILTER) --stop-on-failure

dev: install ## Run development servers
	@tmux new-session "bin/console server:run" \;\
		split-window -h "npm run dev" \;\

build: install ## Build project in production
	@npm run build

migrate: install ## Refresh database by running new migrations
	@echo "$(PRIMARY_COLOR)Drop database$(NO_COLOR)"
	@bin/console doctrine:database:drop --force -q
	@echo "$(PRIMARY_COLOR)Create database$(NO_COLOR)"
	@bin/console doctrine:database:create -q
	@echo "$(PRIMARY_COLOR)Migrate and load fixtures$(NO_COLOR)"
	@bin/console doctrine:migration:migrate -q
	@bin/console doctrine:fixtures:load -q
