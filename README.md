# laravel-dotenv

### Installation

```bash
composer require santutu/laravel-dotenv
```

### Default Ussage.

```php
\DotEnv::copy('.env.example','.env'); // if exist .env, Can be skipped.
\DotEnv::set('APP_NAME','MY_APP_NAME');
\DotEnv::getOldValue(); //Laravel
\DotEnv::get('APP_NAME'); //MY_APP_NAME
\DotEnv::delete('APP_NAME');
```

```php
php artisan env:set APP_NAME MY_APP_NAME  //default is .env
php artisan env:get APP_NAME //MY_APP_NAME 
php artisan env:delete APP_NAME //APP_NAME=MY_APP_NAME

php artisan env:set APP_NAME MY_APP_NAME --env=.env.prod
php artisan env:get APP_NAME --env=.env.prod //MY_APP_NAME 
php artisan env:delete APP_NAME --env=.env.prod //APP_NAME=MY_APP_NAME
```



#### Load another .env

```php
$dotEnv= new DotEnv('.env.prod');
```


#### Create another .env

```php
$dotEnv= new DotEnv('.env.prod');
```

or

```php
$dotEnv= new DotEnv();
$dotEnv->copy('.env.example','.env.prod');
```


### Testing

``` bash
composer test
```

## Inspiration
Inspiration for this package came from [imliam's laravel-env-set-command](https://github.com/imliam/laravel-env-set-command).
(This package is not managed at time of writing.)

## Contributing
All contributions (pull requests, issues and feature requests) are
welcome. Make sure to read through the [CONTRIBUTING.md](CONTRIBUTING.md) first,
though. See the [contributors page](../../graphs/contributors) for all contributors.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.