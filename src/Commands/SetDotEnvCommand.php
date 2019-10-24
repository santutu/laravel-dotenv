<?php


namespace Santutu\LaravelDotEnv\Commands;


use Illuminate\Console\Command;
use InvalidArgumentException;
use Santutu\LaravelDotEnv\DotEnv;

class SetDotEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:set {key} {value?} {--env=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set .env key value';

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

        [$key, $value] = $this->getKeyValue();
        if ($dotEnv->set($key, $value)) {
            $this->info("Environment variable with key '{$dotEnv->getKey()}' has been changed from '{$dotEnv->getOldValue()}' to '{$dotEnv->getNewValue()}'");
        } else {
            $this->info("A new environment variable with key '{$dotEnv->getKey()}' has been set to '{$dotEnv->getNewValue()}'");
        }
    }


    /**
     * Determine what the supplied key and value is from the current command.
     *
     * @return array
     */
    protected function getKeyValue(): array
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        if (!isset($value)) {
            $value = '';
        }

        return [strtoupper($key), $value];
    }


}