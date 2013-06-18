<?php

abstract class Provider {
    
    protected $loadPath;
    
    public function __construct($app)
    {
        $this->loadPath = __DIR__;
        $this->addLoadPaths($app);
    }
        
    abstract public function register($app)
    
    protected function addLoadPaths($app)
    {
        if ( file_exists($this->loadPath . '/data') ) {
            $app['pt.data']->addLoadPath($this->loadPath . '/data');
        }
        if ( file_exists($this->loadPath . '/assets') ) {
            $app['pt.assets']->addLoadPath($this->loadPath . '/assets');
        }
        if ( file_exists($this->loadPath . '/templates') ) {
            $app['twig.loader.filesystem']->addPath($this->loadPath . '/templates');
        }
    }
    
    public function before($app)
    {
        
    }
    
    public function after($app)
    {
        
    }
    
}
