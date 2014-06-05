#stat

laravel + bootstrap

## как поставить все это дело

  Надо поставить:
  * apt-get install php5-json
  * apt-get install php5-mcrypt
  * apt-get install php5-curl
  * apt-get install php5-mysql
  
  Nginx-config:
  ```
        server {
            listen  80;
            server_name statlaravel.local;
            root /home/akalie/work/stats/stats/public;

            index index.php index.html index.htm;

            rewrite ^/(.*)/$ /$1 permanent;

            location / {
                try_files $uri $uri/ /index.php;
            }

            location ~ \.php$ {
                try_files $uri =404;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
            }
        }
  ```

  после клонирования репозитория, в папке проекта:
  * curl -sS https://getcomposer.org/installer | php
  * php composer.phar install
  * sudo chmod 777 app/storage -R
  * sudo chmod 777 public/csv -R

  настроить конфиг для бд (app/config/database.php)
  раскомитить последню строчку в app/routes.php, выполнить http://сайт/init/all, закомитить обратно

  ## Настройка демонов (под рутом)
  crontab
  ```
    1-59/5     *       *       *       *       php /var/www/work/stats/invoke.php   адрес сайта /daemons/albums-parser > /dev/null
    1-59/2     *       *       *       *       php /var/www/work/stats/invoke.php   адрес сайта /daemons/posts-parser > /dev/null
    1-59/2     *       *       *       *       php /var/www/work/stats/invoke.php   адрес сайта /daemons/boards-parser > /dev/null
    1-59/5     *       *       *       *       php /var/www/work/stats/invoke.php   адрес сайта /daemons/csv-parser > /dev/null
  ```

  ## Апп для контакта
  * 4394678