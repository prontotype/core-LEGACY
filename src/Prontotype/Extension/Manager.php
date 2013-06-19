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

    }
    
    public function loadPaths()
    {
        foreach($this->extensions as $namespace => $extensions) {
            foreach($extensions as $ext) {
                $this->app['pt.config']->addLoadPath($ext->getConfigPath());
                $this->app['pt.data']->addLoadPath($ext->getDataPath());
                $this->app['pt.assets']->addLoadPath($ext->getAssetsPath());
                $this->app['twig']->addPath($ext->getTemplatesPath());
            }
        }
    }
    
    public function before()
    {
        // foreach($this->extensions as $namespace => $extensions) {
//             $this->app['twig']->addGlobal($namespace, $extensions);
//             foreach($extensions as $ext) {
//                 $extension->before();
//             }
//         }
    }
    
    public function after()
    {
        // foreach($this->extensions as $namespace => $extensions) {
//             foreach($extensions as $ext) {
//                 $extension->after();
//             }
//         }
    }
    
    public function registerPlugin($class, $key, $namespace)
    {
        
    }
    
    protected function addExtension($ext, $name, $namespace = null)
    {
        // if ( $namespace && !isset($this->extensions[$namespace]) ) {
        //     $this->extensions[$namespace] = array();
        // }
        // $this->extensions[$namespace][] = $ext;
    }
}