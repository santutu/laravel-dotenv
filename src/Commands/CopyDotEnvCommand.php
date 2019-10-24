<?php


namespace Santutu\LaravelDotEnv\Commands;


use Illuminate\Console\Command;
use Santutu\LaravelDotEnv\DotEnv;

class CopyDotEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:copy {env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'use .env file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $env = $this->argument('env');
        $dotEnv = new DotEnv('.env');

        if (file_exists('.env')) {
            if (!$this->confirm('Already exist .env Do you force this command? (be backup as.env.temp)')) {
                return false;
            }
        }

        $dotEnv->copy($env);
        $this->info("copy {$env}->.env");
    }

}