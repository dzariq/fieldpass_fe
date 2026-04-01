#!/bin/sh
cd /var/www/dashboard

# php artisan migrate:fresh --seed
php artisan cache:clear
php artisan route:cache
php artisan config:cache
php artisan config:clear
# php artisan notifications:table
# php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider"

php artisan migrate

/usr/bin/supervisord -c /etc/supervisord.conf