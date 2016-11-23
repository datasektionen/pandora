# Pandora
PHP Laravel application that handles bookings. Live at https://bokning.datasektionen.se.

## API
There will be an API for Pandora. The API will located at ```/api``` (https://bokning.datasektionen.se/api).

### API endpoints
When the API is implemented, there will exist some endpoints. 
The following endpoints are based on the above API URL. Note that these are not implemented yet.
```
  GET /events/{entityId}/{year}/{week}          Returns all events for the given week as JSON
```

## Required environment variables
```
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
	DATABASE_URL:driver://user:password@host:port/database
	### END ALT 2

	LOGIN_API_KEY=[the key]
	LOGIN_API_URL=https://login2.datasektionen.se
	PLS_API_URL=http://pls.froyo.datasektionen.se/api
	ZFINGER_API_URL=https://zfinger.datasektionen.se
	SPAM_API_KEY=[the key]
	SPAM_API_URL=https://spam.datasektionen.se/api/sendmail
```

## Roadmap
Random features are implemented at a random speed. Post an [issue](https://github.com/datasektionen/pandora/issues), and maybe it will be implemented. One day.

## Installation and setup
To setup the app, a web server is needed. The installation depends on your web server. One option is to user [Laravel Homestead](https://laravel.com/docs/4.2/homestead). 





# Laravel

<p align="center"><a href="https://laravel.com" target="_blank"><img width="150"src="https://laravel.com/laravel.png"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## Learning Laravel

Laravel has the most extensive and thorough documentation and video tutorial library of any modern web application framework. The [Laravel documentation](https://laravel.com/docs) is thorough, complete, and makes it a breeze to get started learning the framework.

If you're not in the mood to read, [Laracasts](https://laracasts.com) contains over 900 video tutorials on a range of topics including Laravel, modern PHP, unit testing, JavaScript, and more. Boost the skill level of yourself and your entire team by digging into our comprehensive video library.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](http://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
