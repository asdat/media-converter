## Установка и настройка

1. Клонировать git-репозиторий: git clone https://username@bitbucket.org/asdat_org/encoder.git.
2. Установить зависимости: composer install.
3. Запустить docker-контейнеры: docker-compose up -d --build.
4. Запустить миграции: docker exec php php artisan migrate.
5. Запустить supervisor в php-контейнере:
- docker exec -d php supervisord -c /etc/supervisor/conf.d/supervisord.conf
- docker exec php supervisorctl -c /etc/supervisor/conf.d/supervisord.conf


