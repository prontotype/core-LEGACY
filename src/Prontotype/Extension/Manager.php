<?php

namespace Prontotype\Extension;

class Manager
{
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->path = $app['pt.prototype.paths.extensions'];
        $this->extensions = array();
    }
        
    public function load($extension)
    {   
        if ( file_exists($extension) ) {
            require_once $extension;
            $pathInfo = pathinfo($extension);
            $extName = $pathInfo['filename'];
            $extObj = new $extName($this->app);
            $this->extensions[$extObj->getName()] = $extObj;
        }
    }
    
    public function before()
    {
        $this->app['twig']->addGlobal('ext', $this->extensions);
        foreach($this->extensions as $extension) {
            $extension->before();
        }
    }
    
    public function after()
    {
        foreach($this->extensions as $extension) {
            $extension->after();
        }
    }
        
}