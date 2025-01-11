Использован базовый скелет социальной сети, который был подготовлен в предыдущих ДЗ.
Проект выполнен на чиcтом php без использовани фреймворков, использована БД postgres.

Инструкция дляразвертывания проекта:
1. В корне проекта выполнить команду docker compose build (или docker-compose в зависимости от установленной версии сompose).
2. Запустить контейнеры docker compose up -d
3. Выполнить sql файлы. Для этого сначала нужно скопировать файл с данными docker compose cp ./sql/people.v2.csv postgres:/docker-entrypoint-initdb.d/people.v2.csv. 
Далее:
     3.1. docker compose cp ./sql/create_users_table.sql postgres:/docker-entrypoint-initdb.d/create_users_table.sql (копируем файл в контейнер)
     3.2. docker compose exec -u root postgres psql admin root -f docker-entrypoint-initdb.d/create_users_table.sql (выполняем sql команду)
   То же самое сделать для create_friends_table.sql и create_posts_table.sql. Чтобы заполнить таблицы friends и posts требуется в php контейнере выполнить скрипт scripts/script.php, подставив свои данные в файле.
4. Установить необходимые пакеты. Для этого нужно:
    4.1. Зайти в php контейнер командой docker compose exec php bash
    4.2. В bash выполнить composer install

Всё, проект готов к работе.
К домашнему заданию приложена коллекция из Postman. Добавлены следующие новые запросы:
Добавление/удаление друга (методы /friend/set, /friend/delete из спецификации)
CRUD для постов пользователей (методы /post/create, /post/update, /post/delete, /post/get из спецификации)
Лента постов друзей (метод /post/feed из спецификации)

Лента постов формируется на уровне кэшей. В идеале нужно было использовать очереди для обновления кэшей, но это было пропущено, так как в другом домашнем задании есть этот пункт.
