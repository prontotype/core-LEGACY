<?php

namespace Prontotype;

Class Prototype {
    
    protected $app;
    
    protected $ptPaths;

    public function __construct( $label, $definition, $ptPaths, $app )
    {
        $this->app = $app;
        $this->label = $label;
        $this->definition = $definition;
        $this->ptPaths = $ptPaths;
        $this->location = $this->findPrototype($this->definition['prototype']);
        
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
        return $this->definition['path'];
    }
    
    public function getEnvironment()
    {
        return isset($this->definition['environment']) ? $this->definition['environment'] : 'live';
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
