<?php

namespace Prontotype;

use Symfony\Component\Yaml\Yaml;

Class Prototype {
    
    protected $app;
    
    protected $ptPaths = array();
    
    protected $defPaths = array();

    public function __construct( $label, $definition, $app )
    {
        $this->app = $app;
        $this->label = $label;
        $this->definition = $definition;
    }
    
    public function locate($ptPaths, $defPaths)
    {
        $this->location = $this->findPrototype($ptPaths, $this->definition['prototype']);
        $this->ptPaths = $ptPaths;
        $this->defPaths = $defPaths;
        return true;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function getRootPath()
    {
        return $this->location;
    }
    
    public function getPrototypePath()
    {
        return $this->definition['prototype'];
    }
    
    public function getUid()
    {
        return md5($this->definition['prototype']);
    }
    
    public function getDomain()
    {
        return $this->definition['domain'];
    }
    
    public function getPath()
    {
        return isset($this->definition['path']) ? $this->definition['path'] : '';
    }
    
    public function getEnvironment()
    {
        return isset($this->definition['environment']) ? $this->definition['environment'] : 'live';
    }
    
    public function getPtPaths()
    {
        return $this->ptPaths;
    }
    
    public function getDefPaths()
    {
        return $this->defPaths;
    }
    
    public function matches($host)
    {
        $matches = is_array($this->definition['domain']) ? $this->definition['domain'] : array($this->definition['domain']);
        $regexp = '/^(';
        $regexp .= implode('|', array_map(function($value){
            return str_replace(array('.','*'), array('\.','(.*)'), $value);
        }, $matches));
        $regexp .= ')/';
        
        if ( preg_match($regexp, $host, $matches) ) {
            
            if ( isset($this->definition['path']) && $this->definition['path'] != '/' ) {
                // check the path
                $requestPath = trim(str_replace(array('/index.php'), '', $_SERVER['REQUEST_URI']),'/');
                $requestPathParts = explode('/', $requestPath);
                $definitionPathParts = explode('/',trim($this->definition['path'],'/'));
                if ( count($definitionPathParts) > count($requestPathParts) ) {
                    return false;
                }
                for ( $i = 0; $i < count($definitionPathParts); $i++) {
                    if ( $requestPathParts[$i] !== $definitionPathParts[$i] ) {
                        return false;
                    }
                }
                $this->definition['path'] = '/' . implode($definitionPathParts);
            } else {
                $this->definition['path'] = '';
            }
            $replacements = array_slice($matches, 2);                
            $replacementTokens = array();
            for ( $j = 0; $j < count($replacements); $j++ ) {
                $replacementTokens['$' . ($j+1)] = $replacements[$j];
            }
            $this->definition['prototype'] = str_replace(array_keys($replacementTokens), array_values($replacementTokens), $this->definition['prototype']);
            
            return true;
        } else {
            return false;
        }
    }
         
    protected function findPrototype($ptPaths, $location)
    {
        $path = null;
        
        if ( strpos($location,'/') === 0 && file_exists($location) ) {
            $path = $location;
        }
                
        if ( $path === null ) {
            foreach($ptPaths as $ptPath) {
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
    
    public static function getPrototypeDefinitions($defPaths)
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
