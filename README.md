# DTMS BACKEND

The backend for the **DTMS** application. This app uses **Laravel Framework**.

## Prerequisite

1. [Git](https://git-scm.com/downloads)
2. [Xampp](https://www.apachefriends.org/download.html)
	- PHP v8.2 and above
	- MySQL
3. [Composer](https://getcomposer.org/download/)

## Setup

Clone the repository:

```
git clone https://<your-username>@bitbucket.org/chedro4/dtms-backend.git
```

Go to the project directory and install dependencies.

```
composer install --optimize-autoloader
```

Setup environment variables by editing the `.env` file.

| Key | Value | Description |
|---|---|---|
| APP_NAME | DTMS | The name of the app. Any name will do on development.|
| APP_ENV | local |The current environment of the app. Use `local` for development.|
| APP_KEY | | The key used for encryptions of the app. Use `php artisan key:generate` command to create a random key. |
| APP_URL | http://localhost:8000 | The base url of the app. |
| APP_CLIENT | http://localhost:3000 | The base url of the frontend app. |
| DB_\* | | The details about the database used. |
| FILESYSTEMS_DELETE | false | Determines whether the uploaded files will be permanently deleted or just move to a trash folder. |

## Running Development Server

### Using PHP's built-in server

```
php -S <host>:<port> -t public
```

### Using Artisan Serve

```
php artisan server
```

For more serving options run:

```
php artisan server --help
```