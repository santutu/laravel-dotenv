# laravel-dotenv

## Installation

```bash
composer require santutu/laravel-dotenv
```

## Usage In Code


__Facade__
```php
\DotEnv::copy('.env.example') // is equels \DotEnv::copy('.env.example','.env');
\DotEnv::load('.env.example') // Not copy, just load '.env.example'

\DotEnv::set('APP_NAME','MY_APP_NAME');
\DotEnv::getOldValue(); //Laravel
\DotEnv::get('APP_NAME'); //MY_APP_NAME
\DotEnv::delete('APP_NAME');
```

__Instance__

```php
$dotEnv= (new DotEnv('.env'))->copy('.env.example')) // copy .env.example->.env. if already exist, backup to .env.temp
$dotEnv->copy('.env.prod') // copy .env.prod -> .env. if already exist, backup to .env.temp
$dotEnv->overwrite('.env.prod') // same as copy
$dotEnv->changeTo('.env.prod') // same as copy

$dotEnv->load('.env.dev') //load .env.dev. if not exist, create empty file.

$dotEnv->set('APP_NAME', 'name')
$dotEnv->get('APP_NAME') //name
$dotEnv->delete('APP_NAME')

````

#### As alias
```php
$devDotEnv = new DotEnv('dev'); // is equels new DotEnv('.env.dev'); 
\DotEnv::copy('dev'); //is equels \DotEnv::copy('.env.dev') 
```


## Usage In Console

```php
php artisan env:copy prod // if exist .env, Can be skipped.
php artisan env:set APP_NAME MY_APP_NAME  //default is .env
php artisan env:get APP_NAME //MY_APP_NAME 
php artisan env:delete APP_NAME //APP_NAME=MY_APP_NAME
```

#### Can set another file with --env argument in console 

```php
php artisan env:set APP_NAME MY_APP_NAME --env=.env.prod
php artisan env:get APP_NAME --env=.env.prod //MY_APP_NAME 
php artisan env:delete APP_NAME --env=.env.prod
```

Also you can alias like --env=prod 

## Testing

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