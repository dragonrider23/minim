<?php
namespace Minim;

use Exception;

class Cache
{
    private $path = '';

    public function __construct($cachePath)
    {
        $this->path = $cachePath;
    }

    public function set($key, $value)
    {
        $this->checkKey($key);
        file_put_contents("$this->path/$key", $value);
    }

    public function get($key)
    {
        $this->checkKey($key);
        if (file_exists("$this->path/$key")) {
            return file_get_contents("$this->path/$key");
        }
        return false;
    }

    public function getArray($key, $options = 0)
    {
        $this->checkKey($key);
        if (file_exists("$this->path/$key")) {
            return file("$this->path/$key", $options);
        }
        return false;
    }

    private function checkKey($key) {
        if (strpos($key, '/')) {
            throw new Exception('Cannot use \'/\' in key');
        }
    }

    public function purge()
    {
        return array_map('unlink', glob("$this->path/*"));
    }
}
