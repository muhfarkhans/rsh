### RSH

Dashboard klinik kesehatan

### Teknologi yang Digunakan
- Laravel 11

### Persyaratan
- PHP 8.2 atau lebih tinggi
- Composer
- MySQL atau PostgreSQL

## Installation

Clone repositori project.

Jalankan composer install untuk menginstal dependensi project.
```bash
    composer install
```
Copy .env.example ke .env dan set database sesuai dengan db yang ada
```bash
    cp .env.example .env
```
Generate app key
```bash
    php artisan key:generate
```
Buat database dan jalankan php artisan migrate.
```bash
    php artisan migrate:fresh
```
Jalankan seeder
```bash
    php artisan db:seed
```
Jalankan command untuk role
```bash
    php artisan shield:install admin
    php artisan shield:super-admin
```
Jalankan php artisan serve untuk memulai server web.
```bash
    php artisan serve
```
### Gunakan url berikut sebagai base url http://localhost:8000/api/