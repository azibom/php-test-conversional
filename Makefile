DATABASE_PASS=4813494d137e1631bba301d5acab6e7bb7aa74ce1185d456565ef51d737677b2

init:
	sudo docker exec -it lemp-php bash -c "cp /var/www/html/website/conversional/.env.example /var/www/html/website/conversional/.env"
	sudo docker exec -it lemp-php bash -c "cd /var/www/html/website/conversional;composer install;chown -R :www-data storage;chown -R :www-data bootstrap/cache;chmod -R 775 storage;chmod -R 775 bootstrap/cache"
	sudo docker exec -it lemp-mariadb bash -c "mysql -u root -p${DATABASE_PASS} -e 'create database conversional'"
	sudo docker exec -it lemp-php bash -c "cd /var/www/html/website/conversional;php artisan migrate"
test:
	sudo docker exec -it lemp-php bash -c "cd /var/www/html/website/conversional/;./vendor/bin/phpunit --testdox"
seed:
	sudo docker exec -it lemp-php bash -c "cd /var/www/html/website/conversional/;php artisan db:seed"