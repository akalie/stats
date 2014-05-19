## как поставить все это дело
  Надо поставить:
  - apt-get install php5-json
  - apt-get install php5-mcrypt
  
  Nginx-config:
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


  после клонирования репозитория, в папке проекта
  curl -sS https://getcomposer.org/installer | php
  php composer.phar install
  sudo chmod 777 app/storage -R
  
