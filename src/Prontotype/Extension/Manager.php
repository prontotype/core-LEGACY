<?php

namespace Prontotype\Extension;

use Symfony\Component\Yaml\Yaml;

class Manager
{
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->path = $app['pt.prototype.paths.extensions'];
        $this->extensions = array();
    }
        
    public function load($path)
    {   
        if ( file_exists($path) ) {
            $baseName = strtolower(basename($path));
            $className = ucfirst($baseName) . 'Extension';
            $configPath = $path . '/config.yml';
            if ( file_exists($configPath) ) {
                $config = Yaml::parse(file_get_contents($configPath));
                $className = isset($config['class']) ? $config['class'] : ;
                $classPath = 
            } else {
                $config = array();
                $pathParts = explode()
            }
        }
        // if ( file_exists($extension) ) {
        //     require_once $extension;
        //     $pathInfo = pathinfo($extension);
        //     $extName = $pathInfo['filename'];
        //     $extObj = new $extName($this->app);
        //     $this->addExtension($extObj, $extObj->getName(), $extObj->getNamespace());
        // }
    }
    
    public function loadPaths()
    {
        foreach($this->extensions as $namespace => $extensions) {
            foreach($extensions as $ext) {
                $this->app['pt.data']->addLoadPath($ext->getDataPath());
                $this->app['pt.assets']->addLoadPath($ext->getAssetsPath());
                $this->app['twig']->addPath($ext->getTemplatesPath());
            }
        }
    }
    
    public function before()
    {
        foreach($this->extensions as $namespace => $extensions) {
            $this->app['twig']->addGlobal($namespace, $extensions);
            foreach($extensions as $ext) {
                $extension->before();
            }
        }
    }
    
    public function after()
    {
        foreach($this->extensions as $namespace => $extensions) {
            foreach($extensions as $ext) {
                $extension->after();
            }
        }
    }
    
    protected function addExtension($ext, $name, $namespace = null)
    {
        if ( $namespace && !isset($this->extensions[$namespace]) ) {
            $this->extensions[$namespace] = array();
        }
        $this->extensions[$namespace][] = $ext;
    }
}