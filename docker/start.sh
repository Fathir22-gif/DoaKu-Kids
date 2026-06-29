#!/bin/sh

# Buat folder khusus untuk database sqlite jika belum ada
mkdir -p /var/www/storage/database

# Buat file database jika belum ada
if [ ! -f /var/www/storage/database/database.sqlite ]; then
    touch /var/www/storage/database/database.sqlite
    echo "Created empty SQLite database in /var/www/storage/database/database.sqlite"
fi

# Pastikan permission folder storage benar untuk SQLite & Laravel
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Jalankan migrasi database
echo "Running database migrations..."
php artisan migrate --force

# Jalankan PHP-FPM di background
php-fpm -D

# Jalankan Nginx di foreground agar container tetap hidup
echo "Starting Nginx..."
nginx -g "daemon off;"
