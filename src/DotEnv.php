<?php

namespace Santutu\LaravelDotEnv;

class DotEnv
{
    public $dotEnvFilePath;
    public $oldValue;
    public $newValue;
    public $key;

    //key is must  small letter
    protected $conversionValueMap = [
        'null' => null,
        'true' => true,
        'false' => false,
    ];

    public function __construct(?string $dotEnvFilePath = null)
    {
        if ($dotEnvFilePath !== null) {
            $this->setDotEnvFile($dotEnvFilePath);
        }
    }


    public function setDotEnvFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            $myfile = fopen($filePath, "w");
            fclose($myfile);
        }
        $this->dotEnvFilePath = $filePath;
        return $this;
    }


    public function get(string $key): ?string
    {
        $contents = file_get_contents($this->dotEnvFilePath);
        return $this->reverseValue($this->getOldValueFromDotEnv($contents, $key));
    }

    /**
     * true is set
     * false is add
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     * @throws \Exception
     */
    public function set(string $key, ?string $value = null): bool
    {
        $this->assertDotEnvFilePath();

        $value = $this->conversionValue($value);
        $this->key = $key;
        $this->newValue = $value;

        $contents = file_get_contents($this->dotEnvFilePath);
        if ($this->hasKey($contents, $key)) {
            $oldValue = $this->getOldValueFromDotEnv($contents, $key);
            $contents = str_replace("{$key}={$oldValue}", "{$key}={$value}", $contents);
            $this->writeFile($this->dotEnvFilePath, $contents);
            $this->oldValue = $oldValue;
            return true;
        }
        $contents = $contents . "\n{$key}={$value}\n";
        $this->writeFile($this->dotEnvFilePath, $contents);
        return false;
    }

    public function delete(string $key): ?string
    {
        $contents = file_get_contents($this->dotEnvFilePath);
        $matches = $this->getMatches($contents, $key);

        if (count($matches)) {
            $contents = str_replace($matches[0], "", $contents);
            $this->writeFile($this->dotEnvFilePath, $contents);
            return $matches[0];
        }
        return null;
    }

    public function copy(string $path = '.env.example', string $target = '.env')
    {
        return copy($path, $target);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOldValue(): ?string
    {
        return $this->reverseValue($this->oldValue);
    }

    public function getNewValue(): ?string
    {
        return $this->reverseValue($this->newValue);
    }

    /**
     * Overwrite the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    protected function writeFile(string $path, string $contents): bool
    {
        $file = fopen($path, 'w');
        fwrite($file, $contents);
        return fclose($file);
    }

    protected function hasKey(string $content, string $key): bool
    {
        $matches = $this->getMatches($content, $key);
        if (count($matches)) {
            return true;
        }
        return false;
    }

    /**
     * Get the old value of a given key from an environment file.
     *
     * @param string $content
     * @param string $key
     * @return string
     */
    protected function getOldValueFromDotEnv(string $content, string $key): ?string
    {
        // Match the given key at the beginning of a line
        $matches = $this->getMatches($content, $key);
        if (count($matches)) {
            $value = substr($matches[0], strlen($key) + 1);
            return $value;
        }
        return null;
    }


    protected function reverseValue(?string $key)
    {
        if (is_string($key)) {
            $mapKey = mb_strtolower($key);
            if (array_key_exists($mapKey, $this->conversionValueMap)) {
                return $this->conversionValueMap[$mapKey];
            }
        }
        return $key;
    }

    protected function conversionValue(?string $val)
    {

        foreach ($this->conversionValueMap as $key => $value) {
            $key = mb_strtolower($key);
            if ($value === $val) {
                return $key;
            }
        }
        return $val;
    }


    protected function getMatches(string $content, string $key)
    {
        preg_match("/^{$key}=[^\r\n]*/m", $content, $matches);
        return $matches;
    }

    private function assertDotEnvFilePath()
    {
        if (!isset($this->dotEnvFilePath)) {
            throw  new \Exception('need $this->dotEnvFile property');
        }
    }
}