@echo off
cd /d C:\xampp\htdocs\EV-Data-Analytics-Marketplace

docker exec ev-consumer php /var/www/html/scripts/auto_insert_analytics.php

pause
