<?php

namespace Prontotype\Store;

abstract Class Base {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    abstract public function get($key);
    
    abstract public function set($key, $value);
    
    abstract public function clear($key);
    
}
