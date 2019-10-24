<?php


namespace Santutu\LaravelDotEnv\Commands;


use Illuminate\Console\Command;
use Santutu\LaravelDotEnv\DotEnv;

class GetDotEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:get {key*} {--env=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get value by key';

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
        $dotEnv = resolve(DotEnv::class);
        $dotEnvPath = $this->option('env') ?? app()->environmentFilePath();
        $dotEnv->load($dotEnvPath);

        $keys = $this->argument('key');
        foreach ($keys as $key) {
            $this->info($dotEnv->get($key));
        }

    }


}