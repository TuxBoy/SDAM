server: ## Lance le serveur de dev
	php -S localhost:8000 -d display_errors=1 -d xdebug.remote_enable=1 -d xdebug.remote_autostart=1

test: ## Lance les test unitaires
	./vendor/bin/phpunit