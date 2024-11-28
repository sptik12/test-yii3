Installation notes:

1. Copy .env-dist to .env
2. Check and change parameters in .env if needed
3. Build docker images, create and run containers

   docker-compose build
   docker-compose up

4. Update your 'hosts' file by adding corresponding records for CLIENT_ROOT_URL, DEALER_ROOT_URL and ADMIN_ROOT_URL params.

   Example:
   127.0.0.1 carwow.local
   127.0.0.1 dealer.carwow.local
   127.0.0.1 admin.carwow.local
   					
5. You can check web part entering http://carwow.local:[PORT] in your browser
   Example:
   http://carwow.local:7777/i.php

6. You can view db using phpmyadmin entering http://localhost:[PMA_PORT] in your browser
   Example:
   http://localhost::7779

   login: root
   password: docker 

7. Export db dump to database.
   You can do it logging to web container and executing a command like:
   
   mysql -hdb -uroot -pdocker carwow < carwow.dump.sql

8. Log to web container and install app using instructions in readme.ru.md
   (
		-composer install
		-npm i
        -Check and change parameters in root .env if needed
	)