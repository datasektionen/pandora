# Pandora

PHP Laravel application that handles bookings. Live at https://bokning.datasektionen.se.

## API

There will be an API for Pandora. The API will located at ```/api``` (https://bokning.datasektionen.se/api).

### API endpoints

When the API is implemented, there will exist some endpoints. The following endpoints are based on the above API URL.
Note that these are not implemented yet.

```http request
GET /events/{entityId}/{year}/{week}          Returns all events for the given week as JSON
```

## Required environment variables

```dotenv
APP_ENV=production
APP_KEY=12345678901234567890abcdefabcdef
APP_DEBUG=false
APP_LOG_LEVEL=debug
APP_URL=

### ALT 1
DB_CONNECTION=
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
### END ALT 1

### ALT 2
DB_CONNECTION=
DATABASE_URL:driver://user:password@host:port/database
### END ALT 2

LOGIN_API_KEY=[the key]
LOGIN_API_URL=https://login.datasektionen.se
PLS_API_URL=http://pls.froyo.datasektionen.se/api
ZFINGER_API_URL=https://zfinger.datasektionen.se
SPAM_API_KEY=[the key]
SPAM_API_URL=https://spam.datasektionen.se/api/sendmail
```

If you set up locally, `DB_HOST` should likely be `localhost`. If you set up with docker it should be set to `mysql`.

The `DB_CONNECTION` determines which type of database is used. Default is `mysql` but allowed values are: `sqlite`,
`mysql`, `pgsql`, and `sqlsrv`.

## Roadmap

Random features are implemented at a random speed. Post an [issue](https://github.com/datasektionen/pandora/issues), and
maybe it will be implemented. One day.

## Installation and setup

### Using Docker

This project is set up using [Sail](http://laravel.com/docs/sail) which uses Docker. To first install the project you
can run the following command to install `sail` and all other dependencies.:

```shell
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```

Now `sail` is available as a command as `./vendor/bin/sail`. If you want you can alias the command, like:

```shell
alias sail=./vendor/bin/sail
```

To run the project, including setting up a `mysql` database based on `.env` variables, run:

```shell
sail up

# Or to run in the background
sail up -d

# To stop you can use CTRL + C or if it running in the background:
sail stop
```

It should now be running on [`localhost:80`](http://localhost:80). If it is running for the first time you should start
by migrating the database (command listed below).

#### Running Commands

```shell
# Migrate Database
sail artisan migrate

# Run tests
sail test

# Generate APP_KEY
sail artisan key:generate
```

#### Other locally running services

Keep in mind that from the Docker container's point of view: `localhost` is the container itself, your local machine is
reachable at `host.docker.internal`.

So, for example, if you have a local `pls` instance on port `6000` to test for different permissions, the `PLS_API_URL`
can be set in `.env` as:

```dotenv
PLS_API_URL=http://host.docker.internal:6000/api
```

### Locally

You will need to have PHP and [Composer](https://getcomposer.org/download/) installed. The Docker container uses PHP
version 8.1 but other versions could work. You should also set up a database, for example a `mysql` database.

Then run:

```shell
# Install dependencies
composer install

# Generate APP_KEY
php artisan key:generate

# Migrate database
php artisan migrate

# Run tests
php artisan test

# Start server
php artisan serve
```

At least I think that is how it works, I haven't tested running it this way. Feel free to update this section or remove
this comment if it actually does work this way.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and
creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in
many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache)
  storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all
modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video
tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging
into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in
becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[CMS Max](https://www.cmsmax.com/)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**
- **[Romega Software](https://romegasoftware.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in
the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by
the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell
via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
