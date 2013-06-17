<?php

namespace Prontotype\Extension;

class Base
{
    
    public function __construct($app, $path, $config = array())
    {
        $this->app = $app;
        $this->config = $config;
    }
    
    public function getName()
    {
        return isset($config['name']) ?  $config['name'] : 'ext';    
    }
    
    public function getNamespace()
    {
        return isset($config['namespace']) ?  $config['namespace'] : 'ext';
    }
        
    public function before()
    {
        
    }
    
    public function after()
    {
        
    }
    
    public function getTemplatesPath()
    {
        
    }
    
    public function getAssetsPath()
    {
        
    }
    
    public function getDataPath()
    {
        
    }
    
}
