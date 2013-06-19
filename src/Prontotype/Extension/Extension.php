<?php

namespace Prontotype\Extension;

abstract class Extension
{
    protected $app;
    
    protected $loadPath;
    
    public function __construct($app, $loadPath)
    {
        $this->loadPath = $loadPath;
        $this->app = $app;
    }
    
    abstract public function register();
    
    public function boot()
    {
        $this->registerLoadPaths();
    }
    
    public function before()
    {
        // called before the request is processed
    }
    
    public function after()
    {
        // called after the request is processed
    }
    
    public function registerLoadPaths()
    {    
        if ( file_exists($this->loadPath . '/config') ) {
            $this->app['pt.config']->addLoadPath($this->loadPath . '/config');
        }
        if ( file_exists($this->loadPath . '/data') ) {
            $this->app['pt.data']->addLoadPath($this->loadPath . '/data');
        }
        if ( file_exists($this->loadPath . '/assets') ) {
            $this->app['pt.assets']->addLoadPath($this->loadPath . '/assets');
        }
        if ( file_exists($this->loadPath . '/templates') ) {
            $this->app['twig.loader.filesystem']->addPath($this->loadPath . '/templates');
        }
    }
    
}
