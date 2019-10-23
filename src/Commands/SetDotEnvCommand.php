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
    protected $signature = 'env:set {key} {value?}';

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
        $dotEnv->setDotEnvFile(app()->environmentFilePath());

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
        if (!$value) {
            $parts = explode('=', $key, 2);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException('No value was set');
            }
            $key = $parts[0];
            $value = $parts[1];
        }
        if (!$this->isValidKey($key)) {
            throw new InvalidArgumentException('Invalid argument key');
        }
        if (!is_bool(strpos($value, ' '))) {
            $value = '"' . $value . '"';
        }
        return [strtoupper($key), $value];
    }

    /**
     * Check if a given string is valid as an environment variable key.
     *
     * @param string $key
     * @return boolean
     */
    protected function isValidKey(string $key): bool
    {
        if (mb_strpos($key, '=') !== false) {
            throw new InvalidArgumentException("Environment key should not contain '='");
        }
        if (!preg_match('/^[a-zA-Z_]+$/', $key)) {
            throw new InvalidArgumentException('Invalid environment key. Only use letters and underscores');
        }
        return true;
    }
}