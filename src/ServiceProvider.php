<?php

namespace Santutu\LaravelDotEnv;

use Santutu\LaravelDotEnv\Commands\DeleteDotEnvCommand;
use Santutu\LaravelDotEnv\Commands\GetDotEnvCommand;
use Santutu\LaravelDotEnv\Commands\SetDotEnvCommand;
use Santutu\LaravelDotEnv\Commands\CopyDotEnvCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DotEnv::class, function () {
            return (new DotEnv(app()->environmentFilePath()));
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetDotEnvCommand::class,
                GetDotEnvCommand::class,
                DeleteDotEnvCommand::class,
                CopyDotEnvCommand::class,
            ]);
        }

    }


}