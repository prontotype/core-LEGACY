<?php

abstract class Provider {
    
    protected $loadPath;
    
    public function __construct($app)
    {
        $this->loadPath = __DIR__;
        $this->addLoadPaths($app);
    }
        
    abstract public function register($app)

    
    public function before($app)
    {
        
    }
    
    public function after($app)
    {
        
    }
    
}
