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

Copy the `.env.format` to `.env`.

```
// for Unix
cp .env.format .env

// for windows
copy .env.format .env
```

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