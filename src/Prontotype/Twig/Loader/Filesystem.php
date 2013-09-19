<?php

namespace Prontotype\Twig\Loader;

use Prontotype\Prototype;

class Filesystem extends \Twig_Loader_Filesystem
{
    protected $app;
    
    protected $loaderType = 'templates';
    
    public function setApp($app)
    {
        $this->app = $app;
    }
    
    public function setLoaderType($type)
    {
        $this->loaderType = $type;
    }
    
    protected function findTemplate($name)
    {
        $name = (string) $name;
                
        $namespace = '__main__';
        $oldPaths = $this->paths[$namespace];
        
        if ( strpos($name,'::') !== false && $this->app['pt.config']->get('templates.sideload') ) {
            list($label, $location) = explode('::', $name);
            $pt = new Prototype($this->app['pt.prototypes.definitions'], $this->app['pt.prototypes.loadpaths'], $this->app);
            try {
                $pt->load($label);
                $name = $location;
                
                $this->cache = array();
                $this->prependPath($pt->getPathTo($this->loaderType));
                $result = parent::findTemplate($name);
                $this->setPaths($oldPaths);
                
                return $result;
                
            } catch ( \Twig_Error_Loader $e ) {
                
                $this->setPaths($oldPaths);
                throw $e;
                
            } catch( \Exception $e ) {}
        } 
        
        return parent::findTemplate($name);
    }    
}


