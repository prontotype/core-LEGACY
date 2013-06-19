<?php

namespace Prontotype\Extension;

abstract class Plugin
{
    protected $app;
    
    protected $namespace = 'ext';
    
    protected $name = null;
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getNamespace()
    {
        return $this->namespace;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
}
