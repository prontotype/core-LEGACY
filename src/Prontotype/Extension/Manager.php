<?php

namespace Prontotype\Extension;

use Symfony\Component\Yaml\Yaml;

class Manager
{
    protected $extensions = array();
    
    public function __construct($app, $extensionsPath)
    {
        $this->app = $app;
        $this->path = $extensionsPath;
        $this->loadFromDirectory($extensionsPath);
        $this->loadFromClassPaths($app['pt.config']->get('extensions'));
    }
    
    public function loadFromDirectory($path)
    {
        if ( file_exists($path) ) {
            $extensions = glob($path . '/*', GLOB_ONLYDIR);            
            foreach($extensions as $extension) {
                $className = $this->getExtensionClassName($extension);
                $classPath = $this->getExtensionClassPath($extension);
                require $classPath;
                $this->load(new $className($this->app, $extension));
            }
        }
    }
    
    public function loadFromClassPaths($classes)
    {
        if ( ! empty($classes) ) {
            foreach($classes as $class) {
                $this->load(new $class);
            }
        }
    }
        
    public function boot()
    {
        foreach($this->extensions as $extension) {
            $extension->boot();
        }
    }
    
    public function load(Extension $extension)
    {   
        $extension->register();
        $this->extensions[] = $extension;
    }
    
    public function before()
    {
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
    
    protected function getExtensionClassName($path)
    {
        $dirName = basename($path);
        return ucfirst($dirName) . 'Extension';
    }
    
    protected function getExtensionClassPath($path)
    {
        $fileName = $this->getExtensionClassName($path);
        return $path . '/' . $fileName . '.php';
    }
    
}