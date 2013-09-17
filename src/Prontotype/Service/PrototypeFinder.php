<?php

namespace Prontotype\Service;

use Symfony\Component\Yaml\Yaml;

use Silex\Application as SilexApp;
use Silex\ServiceProviderInterface;

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
            $matches = is_array($definition['domain']) ? $definition['domain'] : array($definition['domain']);
            $regexp = '/^(';
            $regexp .= implode('|', array_map(function($value){
                return str_replace(array('.','*'), array('\.','(.*)'), $value);
            }, $matches));
            $regexp .= ')/';
            if ( preg_match($regexp, $host, $matches) ) {
                if ( isset($definition['path']) && $definition['path'] != '/' ) {
                    // check the path
                    $requestPath = trim(str_replace(array('/index.php'), '', $_SERVER['REQUEST_URI']),'/');
                    $requestPathParts = explode('/', $requestPath);
                    $definitionPathParts = explode('/',trim($definition['path'],'/'));
                    if ( count($definitionPathParts) > count($requestPathParts) ) {
                        continue;
                    }
                    for ( $i = 0; $i < count($definitionPathParts); $i++) {
                        if ( $requestPathParts[$i] !== $definitionPathParts[$i] ) {
                            continue 2;
                        }
                    }
                    $definition['path'] = '/' . implode($definitionPathParts);
                } else {
                    $definition['path'] = '';
                }
                $replacements = array_slice($matches, 2);                
                $ptConfig = $definition;
                $replacementTokens = array();
                for ( $j = 0; $j < count($replacements); $j++ ) {
                    $replacementTokens['$' . ($j+1)] = $replacements[$j];
                }
                $ptLabel = $label;
                $ptConfig['prototype'] = str_replace(array_keys($replacementTokens), array_values($replacementTokens), $ptConfig['prototype']);
                break;
            }
        }

        if ( ! $ptConfig ) {
            throw new \Exception(sprintf("Could not find matching prototype definition for '%s'.", $host));
        }

        $ptDirPath = $this->findPrototype($ptConfig['prototype']);
        
        $app['pt.prototype.label']       = $label;
        $app['pt.prototype.location']    = $ptConfig['prototype'];
        $app['pt.prototype.uid']         = md5($ptConfig['prototype']);
        $app['pt.prototype.domain']      = $ptConfig['domain'];
        $app['pt.prototype.path']        = $ptConfig['path'];
        $app['pt.prototype.environment'] = isset($ptConfig['environment']) ? $ptConfig['environment'] : 'live';

        $app['pt.prototype.paths.root']       = $ptDirPath;
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
    
    protected function findPrototype($location)
    {
        $path = null;
        
        if ( strpos($location,'/') === 0 && file_exists($location) ) {
            $path = $location;
        }
                
        if ( $path === null ) {
            foreach($this->ptPaths as $ptPath) {
                if ( file_exists($ptPath . '/' . $location) ) {
                    $path = $ptPath . '/' . $location;
                    break;
                }
            }
        }
        
        if ( $path === null ) {
            throw new \Exception(sprintf("Prototype directory '%s' does not exist.", $location));
        }
        
        return $path;
    }
    
}