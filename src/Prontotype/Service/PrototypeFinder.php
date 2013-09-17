<?php

namespace Prontotype\Service;

use Symfony\Component\Yaml\Yaml;

use Silex\Application as SilexApp;
use Silex\ServiceProviderInterface;

use Prontotype\Prototype;

Class PrototypeFinder implements ServiceProviderInterface {
    
    protected $defPaths;
    
    protected $ptPaths;
    
    public function __construct($defPaths, $ptPaths, $prototype = null)
    {
        $this->defPaths = $defPaths;
        $this->ptPaths = $ptPaths;
        $this->prototype = $prototype;
    }
    
    public function register(SilexApp $app)
    {   
        $app['pt.prototype'] = null;
        
        if ( ! $this->prototype ) {
            $ptDefinitions = Prototype::getPrototypeDefinitions($this->defPaths);
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

            foreach( $ptDefinitions as $label => $definition ) {
                $pt = new Prototype($label, $definition, $app);
                if ( $pt->locate($this->ptPaths, $this->defPaths) ) {
                    if ( $pt->matches($host) ) {
                        $app['pt.prototype'] = $pt;
                        break;
                    }                
                }
            }            
        } else {
            $app['pt.prototype'] = $this->prototype;
        }
        
        if ( ! $app['pt.prototype'] ) {
            throw new \Exception(sprintf("Could not find matching prototype definition for '%s'.", $host));
        }
        
        $app['pt.prototype.label']       = $app['pt.prototype']->getLabel();
        $app['pt.prototype.prototype']   = $app['pt.prototype']->getPrototypePath();
        $app['pt.prototype.uid']         = $app['pt.prototype']->getUid();
        $app['pt.prototype.domain']      = $app['pt.prototype']->getDomain();
        $app['pt.prototype.path']        = $app['pt.prototype']->getPath();
        $app['pt.prototype.environment'] = $app['pt.prototype']->getEnvironment();

        $app['pt.prototype.paths.root']       = $app['pt.prototype']->getRootPath();
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
    
}