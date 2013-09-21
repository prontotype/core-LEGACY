<?php

namespace Prontotype\Service;

use Symfony\Component\Yaml\Yaml;

use Silex\Application as SilexApp;
use Silex\ServiceProviderInterface;

use Prontotype\Prototype;

Class PrototypeFinder implements ServiceProviderInterface {
    
    protected $defPaths;
    
    protected $ptPaths;
    
    public function __construct($defPaths, $ptPaths)
    {
        $this->defPaths = $defPaths;
        $this->ptPaths = $ptPaths;
    }
    
    public function register(SilexApp $app)
    {   
        $app['pt.prototypes.loadpaths'] = $this->ptPaths;
        $app['pt.prototypes.defpaths'] = $this->defPaths;
        $app['pt.prototypes.definitions'] = $this->getPrototypeDefinitions($this->defPaths);
        
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        
        $app['pt.prototype'] = new Prototype($app['pt.prototypes.definitions'], $app['pt.prototypes.loadpaths'], $app);
        $app['pt.prototype']->loadByHost($host);
        
        $app['pt.prototype.label']       = $app['pt.prototype']->getLabel();
        $app['pt.prototype.prototype']   = $app['pt.prototype']->getPrototypePath();
        $app['pt.prototype.uid']         = $app['pt.prototype']->getUid();
        $app['pt.prototype.domain']      = $app['pt.prototype']->getDomain();
        $app['pt.prototype.path']        = $app['pt.prototype']->getPath();
        $app['pt.prototype.environment'] = $app['pt.prototype']->getEnvironment();        

        $app['pt.prototype.paths.root']       = $app['pt.prototype']->getRootPath();
        $app['pt.prototype.paths.templates']  = $app['pt.prototype']->getPathTo('templates');
        $app['pt.prototype.paths.data']       = $app['pt.prototype']->getPathTo('data');
        $app['pt.prototype.paths.config']     = $app['pt.prototype']->getPathTo('config');
        $app['pt.prototype.paths.extensions'] = $app['pt.prototype']->getPathTo('extensions');
        $app['pt.prototype.paths.assets']     = $app['pt.prototype']->getPathTo('assets');

        $app['pt.prototype.paths.cache.root']      = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'];
        $app['pt.prototype.paths.cache.templates'] = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/templates';
        $app['pt.prototype.paths.cache.assets']    = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/assets';
        $app['pt.prototype.paths.cache.data']      = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/data';
        $app['pt.prototype.paths.cache.requests']  = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/requests';
        $app['pt.prototype.paths.cache.exports']   = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/exports';        
        $app['pt.prototype.paths.cache.images']    = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/images';
    }
        
    public function boot(SilexApp $app) {}
        
    protected function getPrototypeDefinitions($defPaths)
    {
        $defs = array();
        foreach($defPaths as $loadPath) {
            $loadPath = $loadPath . '/prototypes.yml';
            if ( file_exists($loadPath) ) {
                $ptDefinitions = Yaml::parse($loadPath);       
                if (null === $ptDefinitions) {
                    throw new \Exception(sprintf("The prototype loader file '%s' appears to be invalid YAML.", $loadPath));
                }
                $defs = array_merge($ptDefinitions, $defs);                
            }
        }
        return $defs;
    }
    
}