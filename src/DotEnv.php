<?php

namespace Santutu\LaravelDotEnv;

use Webmozart\PathUtil\Path;

class DotEnv
{
    public $dotEnvFilePath;
    public $oldValue;
    public $newValue;
    public $key;
    public $useAutoPrefix = true;
    public $prefix = '.env';

    //key is must  small letter
    protected $conversionValueMap = [
        'null' => null,
        'true' => true,
        'false' => false,
    ];

    public function __construct(?string $envFilePath = null)
    {
        if ($envFilePath !== null) {
            if ($this->useAutoPrefix) {
                $envFilePath = $this->makePrefix($envFilePath);
            }
            $this->load($envFilePath);
        }
    }


    public function load(string $filePath)
    {
        if (!file_exists($filePath)) {
            $myfile = fopen($filePath, "w");
            fclose($myfile);
        }
        $this->dotEnvFilePath = $filePath;
        return $this;
    }


    public function get(string $key, $default = null): ?string
    {
        $contents = file_get_contents($this->dotEnvFilePath);
        $value = $this->reverseValue($this->getOldValueFromDotEnv($contents, $key));
        return $value === null ? $default : $value;
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
        $this->isValidKey($key);

        $this->assertDotEnvFilePath();

        $value = $this->convertValue($value);
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


    public function copy(string $path = '.env.example', ?string $target = null): self
    {
        if ($target === null) {
            $this->assertDotEnvFilePath();
            $target = $this->getDotEnvFilePath();
        }

        if ($this->useAutoPrefix) {
            $path = $this->makePrefix($path);
            $target = $this->makePrefix($target);
        }

        if (file_exists($target)) {
            copy($target, '.env.temp');
        }

        copy($path, $target);
        $this->load($target);
        return $this;
    }

    public function copyByIns(DotEnv $source, ?DotEnv $target = null): self
    {
        if ($target === null) {
            $target = $this;
        }
        $this->assertDotEnvFilePath();
        if ($source->getDotEnvFilePath() === null) {
            throw new \Exception('dot env file path is null');
        }
        $this->copy($source->getDotEnvFilePath(), $target->getDotEnvFilePath());
        return $this;
    }

    public function getDotEnvFilePath()
    {
        return $this->dotEnvFilePath;
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

    public function setUseAutoPrefix(bool $useAutoPrefix)
    {
        $this->useAutoPrefix = $useAutoPrefix;
        return $this;
    }

    protected function makePrefix(string $envFilePath): string
    {
        $fileName = Path::getFilename($envFilePath);
        if (mb_strpos($fileName, '.env') !== 0) {
            $prefix = '.env';
            if (mb_strpos($fileName, '.') !== 0) {
                $prefix .= '.';
            }
            $fileName = $prefix . $fileName;
        }

        return Path::join(Path::getDirectory($envFilePath), $fileName);
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


    public function reverseValue(?string $key)
    {
        if (is_string($key)) {
            $key = $this->stripDoubleQuotes($key);
            $mapKey = mb_strtolower($key);
            if (array_key_exists($mapKey, $this->conversionValueMap)) {
                return $this->conversionValueMap[$mapKey];
            }
        }
        return $key;
    }

    public function convertValue(?string $val)
    {
        if (is_string($val))
            $val = $this->ensureContainSpaceValue($val);

        foreach ($this->conversionValueMap as $key => $value) {
            $key = mb_strtolower($key);
            if ($value === $val) {
                return $key;
            }
        }
        return $val;
    }

    protected function ensureContainSpaceValue(string $val): string
    {
        if ((mb_strpos($val, ' ') !== false)) {
            return '"' . $val . '"';
        }
        return $val;
    }

    protected function stripDoubleQuotes(string $val)
    {
        if (mb_strpos($val, '"') === 0 && mb_strrpos($val, '"') === mb_strlen($val) - 1) {
            return mb_substr($val, 1, mb_strlen($val) - 2);
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


    protected function isValidKey(string $key): bool
    {
        if (mb_strpos($key, '=') !== false) {
            throw new \Exception("Environment key should not contain '='");
        }
        if (!preg_match('/^[a-zA-Z_]+$/', $key)) {
            throw new \Exception('Invalid environment key. Only use letters and underscores');
        }
        return true;
    }

}