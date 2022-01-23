env ?= dev
.PHONY       =  # Not needed here, but you can put your all your targets to be sure
                # there is no name conflict between your files and your targets.

## —— 🐝 The Strangebuzz Symfony Makefile 🐝 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

all: simple-test install_dev fixtures database prepare phpstan php-cs-fixer composer-valid doctrine fix analyse
install_dev: ## Install the dev dependencies
	cp .env .env.$(env).local
	sed -i -e 's/DATABASE_USER/$(db_user)/' .env.$(env).local
	sed -i -e 's/DATABASE_PASSWORD/$(db_password)/' .env.$(env).local
	sed -i -e 's/ENV/$(env)/' .env.$(env).local
	composer install
	make prepare env=$(env)
	yarn install
	yarn run dev

install: # Install the project in production mode
	cp .env.dist .env.$(env).local
	sed -i -e 's/DATABASE_USER/$(db_user)/' .env.$(env).local
	sed -i -e 's/DATABASE_PASSWORD/$(db_password)/' .env.$(env).local
	sed -i -e 's/ENV/$(env)/' .env.$(env).local
	composer install
	make prepare env=$(env)
	yarn install
	yarn run dev

fixtures: # Generate the fixtures
	php bin/console doctrine:fixtures:load -n --env=$(env)

database: #Renew db
	php bin/console doctrine:database:drop --if-exists --force --env=$(env)
	php bin/console doctrine:database:create --env=$(env)
	php bin/console doctrine:query:sql "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));" --env=$(env)
	php bin/console doctrine:schema:update --force --env=$(env)

update-db: # update database schema
	php bin/console doctrine:schema:update --force --env=$(env)

prepare:
	make database env=$(env)
	make fixtures env=$(env)

simple-test: ## test all
	php bin/phpunit --testdox

eslint: ## lint all
	npx eslint assets/

stylelint:
	npx stylelint "assets/styles/**/*.scss"

phpstan: ## phpstan
	php vendor/bin/phpstan analyse -c phpstan.neon

php-cs-fixer: ## fix all
	php vendor/bin/php-cs-fixer fix

composer-valid:
	composer valid

doctrine: ## Doctrine valid schema
	php bin/console doctrine:schema:valid --skip-sync

twig:
	php bin/console lint:twig templates

yaml:## Lint
	php bin/console lint:yaml config translations

container: 
	php bin/console lint:container

## php cs fixer
fix: php-cs-fixer
	npx eslint assets/ --fix
## npx stylelint "assets/styles/**/*.scss" --fix

npm-dev:
	yarn run dev
	
analyse: eslint twig yaml composer-valid container doctrine phpstan

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
SYMFONY_BIN   = symfony
SYMFONY       = php bin/console

sf: ## List all Symfony commands
	$(SYMFONY)

serve: ## Serve the application with HTTPS support
	$(SYMFONY_BIN) serve -d

unserve: ## Stop the webserver
	$(SYMFONY_BIN) server:stop

## —— Docker ———————————————————————————————————————————————————————————————

start: ## start docker bdd postgres
	docker-compose up -d
stop: ## start docker bdd postgres
	docker-compose down
restart: \
	stop \
	start