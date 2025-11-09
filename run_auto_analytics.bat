@echo off
cd /d C:\xampp\htdocs\EV-Data-Analytics-Marketplace

docker exec ev-app php /var/www/html/backend/data-consumer-service/scripts/auto_insert_analytics.php

pause
