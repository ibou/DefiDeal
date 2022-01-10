env ?= dev
.PHONY: tests install_dev fixtures database prepare tests phpstan php-cs-fixer composer-valid doctrine fix analyse

install_dev:
	cp .env .env.$(env).local
	sed -i -e 's/DATABASE_USER/$(db_user)/' .env.$(env).local
	sed -i -e 's/DATABASE_PASSWORD/$(db_password)/' .env.$(env).local
	sed -i -e 's/ENV/$(env)/' .env.$(env).local
	composer install
	make prepare env=$(env)
	yarn install
	yarn run dev

install:
	cp .env.dist .env.$(env).local
	sed -i -e 's/DATABASE_USER/$(db_user)/' .env.$(env).local
	sed -i -e 's/DATABASE_PASSWORD/$(db_password)/' .env.$(env).local
	sed -i -e 's/ENV/$(env)/' .env.$(env).local
	composer install
	make prepare env=$(env)
	yarn install
	yarn run dev

fixtures:
	php bin/console doctrine:fixtures:load -n --env=$(env)

database:
	php bin/console doctrine:database:drop --if-exists --force --env=$(env)
	php bin/console doctrine:database:create --env=$(env)
	php bin/console doctrine:query:sql "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));" --env=$(env)
	php bin/console doctrine:schema:update --force --env=$(env)

prepare:
	make database env=$(env)
	make fixtures env=$(env)

tests:
	php bin/phpunit --testdox

eslint:
	npx eslint assets/

stylelint:
	npx stylelint "assets/styles/**/*.scss"

phpstan:
	php vendor/bin/phpstan analyse -c phpstan.neon

php-cs-fixer:
	php vendor/bin/php-cs-fixer fix

composer-valid:
	composer valid

doctrine:
	php bin/console doctrine:schema:valid --skip-sync

twig:
	php bin/console lint:twig templates

yaml:
	php bin/console lint:yaml config translations

container:
	php bin/console lint:container

fix: php-cs-fixer
	npx eslint assets/ --fix
## npx stylelint "assets/styles/**/*.scss" --fix

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