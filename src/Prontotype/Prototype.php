<?php

namespace Prontotype;

use Symfony\Component\Yaml\Yaml;

Class Prototype {
    
    protected $app;
    
    protected $definition = null;
    
    protected $location = null;
    
    protected $label = null;
    
    public function __construct( $defs, $app )
    {
        $this->app = $app;
        $this->definitions = $defs;
    }
    
    public function load($label, $ptPaths = array())
    {
        $path = null;
        
        if ( ! isset($this->definitions[$label]) ) {
            throw new \Exception(sprintf("Prototype with label '%s' does not exist.", $label));
        }
        
        if ( ! isset($this->definitions[$label]['prototype']) ) {
            throw new \Exception(sprintf("Prototype with label '%s' does not have a 'prototype' key set.", $label));
        }
        
        $location = $this->definitions[$label]['prototype'];
        
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
        
        // this is a valid prototype definition
        
        $this->definition = $this->definitions[$label];
        $this->label = $label;
        $this->location = $path;
        
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
    
    public function getPathTo($dir)
    {
        return $this->getRootPath() . '/' . $dir;
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
        
}
