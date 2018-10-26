.PHONY: test dev install help
.DEFAULT_GOAL= help

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

composer.json:
	composer init --verbose

node_modules:
ifneq ("$(wildcard package.json)", "")
	npm i
endif

composer.lock: composer.json
	composer update

vendor: composer.lock
	composer install

install: vendor node_modules ## Install the composer dependencies and npm dependencies

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-15s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

test: install ## Run unit tests
ifneq ($(FRAMEWORK),symfony)
	vendor/bin/phpunit --stop-on-failure
else
	bin/phpunit --stop-on-failure
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

