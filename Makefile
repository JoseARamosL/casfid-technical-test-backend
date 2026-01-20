# Makefile

CONTAINER_PHP = server_casfid_technical_test

.PHONY: help start stop bash test scrape cache-clear

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

start:
	docker-compose up -d --build
	@echo "🚀 Proyecto listo en http://localhost:8890/feeds"

stop:
	docker-compose down

bash:
	docker exec -it -w /var/www $(CONTAINER_PHP) bash

test:
	docker exec -it -w /var/www $(CONTAINER_PHP) php bin/phpunit

scrape:
	docker exec -it -w /var/www $(CONTAINER_PHP) php bin/console app:fetch-news

cache-clear:
	docker exec -it -w /var/www $(CONTAINER_PHP) php bin/console cache:clear
