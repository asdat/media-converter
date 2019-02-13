## Установка и настройка

1. Клонировать git-репозиторий: `git clone https://username@bitbucket.org/asdat_org/encoder.git`
2. Установить зависимости: `composer install`
3. Запустить docker-контейнеры: `docker-compose up -d --build`
4. Запустить supervisor в php-контейнере:
- `docker exec -d php supervisord -c /etc/supervisor/conf.d/supervisord.conf`
- `docker exec php supervisorctl -c /etc/supervisor/conf.d/supervisord.conf`

## Инструкция по кодированию
Запустить команду вида `docker exec php php artisan file:put --input=http://filepath/file.ext  --id=database_id --output-path=dir1/dir2`, где 
- `--input=` - путь ко входящему файлу,
- `--id=` - идентификатор файла для базы данных,
- `--output-path=` - путь для хранения готовых файлов (любого уровня вложенности). Данные директории сохраняются в корневую директорию output
                     

Адрес внешнего API нужно прописать в .env по ключу EXTERNAL_API_URL. Туда отправляется POST-запрос с параметром под названием `id`.



