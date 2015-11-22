<?php
namespace Minim;

class Config
{
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->config)) {
            return null;
        }
        return $this->config[$key];
    }
}
