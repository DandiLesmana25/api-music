# api-music
**Backend Aplikasi  music player**
aplikasi frontendnya menggunakan **ionic framework **bisa di akses di https://github.com/DandiLesmana25/soundbox-musicPlayerApp.git

API ini dapat diakses di  http://soundbox.my.id

**Requirments :**

PHP versi 8.x.x
Laravel Versi 9
DBMS (MySQL, etc)
Composer

**Installation**
Clone the repository or download the source code.

-git clone <repository-url>
-cd api-music
-Install Composer dependencies.
Run:
composer install
Install NPM dependencies.
-npm install
or
yarn install
-Create a copy of the .env.example file and rename it to .env.
cp .env.example .env
-Generate the application key.
run:
php artisan key:generate

-Update the .env file with your database connection information.

Change code
DB_CONNECTION=mysql
DB_HOST=your_database_host
DB_PORT=your_database_port
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
Run the database migrations.

Run
-php artisan migrate
Start the development server.

-php artisan serve
You can now access the application at http://localhost:8000.

-Usage
Register a new account and log in to access the features of the application.
License
