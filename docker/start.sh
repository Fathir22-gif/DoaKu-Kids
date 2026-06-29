#!/bin/sh

# Buat folder khusus untuk database sqlite jika belum ada
mkdir -p /var/www/storage/database

# Buat file database jika belum ada
if [ ! -f /var/www/storage/database/database.sqlite ]; then
    touch /var/www/storage/database/database.sqlite
    echo "Created empty SQLite database in /var/www/storage/database/database.sqlite"
fi

# Pastikan permission folder storage dan database benar untuk SQLite & Laravel
chown -R www-data:www-data /var/www/storage /var/www/database /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/database /var/www/bootstrap/cache

# Jalankan migrasi database
echo "Running database migrations..."
php artisan migrate --force

# Jalankan PHP-FPM di background
php-fpm -D

# Gunakan PORT dari Railway (default ke 80 jika tidak diset)
PORT=${PORT:-80}
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default

# Jalankan Nginx di foreground agar container tetap hidup
echo "Starting Nginx on port ${PORT}..."
nginx -g "daemon off;"
