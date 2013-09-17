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
        $ptDefinitions = $this->getPrototypeDefinitions();

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $ptConfig = null;

        foreach( $ptDefinitions as $label => $definition ) {
            
            $test = new Prototype($label, $definition, $this->ptPaths, $app);
            
            if ( $test->matches($host) ) {
                $pt = $test;
                break;
            }
            
        }

        if ( ! $pt ) {
            throw new \Exception(sprintf("Could not find matching prototype definition for '%s'.", $host));
        }
        
        $app['pt.prototype.label']       = $pt->getLabel();
        $app['pt.prototype.prototype']   = $pt->getPrototypePath();
        $app['pt.prototype.uid']         = $pt->getUid();
        $app['pt.prototype.domain']      = $pt->getDomain();
        $app['pt.prototype.path']        = $pt->getPath();
        $app['pt.prototype.environment'] = $pt->getEnvironment();

        $app['pt.prototype.paths.root']       = $pt->getRootPath();
        $app['pt.prototype.paths.templates']  = $app['pt.prototype.paths.root'] . '/templates';
        $app['pt.prototype.paths.data']       = $app['pt.prototype.paths.root'] . '/data';
        $app['pt.prototype.paths.config']     = $app['pt.prototype.paths.root'] . '/config';
        $app['pt.prototype.paths.extensions'] = $app['pt.prototype.paths.root'] . '/extensions';
        $app['pt.prototype.paths.assets']     = $app['pt.prototype.paths.root'] . '/assets';

        $app['pt.prototype.paths.cache.root']      = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'];
        $app['pt.prototype.paths.cache.templates'] = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/templates';
        $app['pt.prototype.paths.cache.assets']    = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/assets';
        $app['pt.prototype.paths.cache.data']      = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/data';
        $app['pt.prototype.paths.cache.requests']  = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/requests';
        $app['pt.prototype.paths.cache.exports']   = $app['pt.install.paths.cache.root'] . '/' . $app['pt.prototype.uid'] . '/exports';        
    }
        
    public function boot(SilexApp $app) {}
    
    protected function getPrototypeDefinitions()
    {
        $defs = array();
        foreach($this->defPaths as $loadPath) {
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