# TailorFit Installation Guide

This guide will walk you through the process of installing and configuring TailorFit on your server.

## System Requirements

Before installing TailorFit, ensure your server meets the following requirements:

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and NPM
- Web server (Apache or Nginx)
- PHP extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/tailorit.git
cd tailorit/wtailorfit
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Create Environment File

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Environment Variables

Open the `.env` file and update the following settings:

```
APP_NAME=TailorFit
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tailorit
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Create Database

Create a new MySQL database for TailorFit:

```sql
CREATE DATABASE tailorit;
```

### 7. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

### 8. Build Assets

```bash
npm run build
```

### 9. Set Up Scheduler (Optional)

For features like appointment reminders to work, you need to set up the Laravel scheduler. Add the following Cron entry to your server:

```
* * * * * cd /path-to-your-project/wtailorfit && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Configure Web Server

#### Apache

Create a new virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/tailorit/wtailorfit/public

    <Directory "/path/to/tailorit/wtailorfit/public">
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tailorit-error.log
    CustomLog ${APACHE_LOG_DIR}/tailorit-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/tailorit/wtailorfit/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 11. Set Directory Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 12. Access the Application

Open your browser and navigate to your domain. You should see the TailorFit login page.

## Default Login Credentials

After installation, you can log in with the following default credentials:

- **Email**: admin@example.com
- **Password**: password

**Important**: Change these credentials immediately after your first login.

## Troubleshooting

If you encounter any issues during installation, please check the following:

1. Make sure all system requirements are met
2. Verify that the `.env` file is properly configured
3. Check the Laravel log file at `storage/logs/laravel.log` for errors
4. Ensure proper permissions are set on the storage and bootstrap/cache directories
5. Verify that your web server is properly configured

For more detailed troubleshooting, refer to the [Troubleshooting Guide](troubleshooting.md).
