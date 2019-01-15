## Установка и настройка

1. Клонировать git-репозиторий: git clone https://username@bitbucket.org/asdat_org/encoder.git.
2. Установить зависимости: composer install.
3. Запустить docker-контейнеры: docker-compose up -d --build.
4. В php-контейнере установить необходимый драйвер для Kafka: docker exec -it php composer require rapide/laravel-queue-kafka
5. В php-контейнере запустить миграции для очередей: docker exec -it php php artisan migrate.


