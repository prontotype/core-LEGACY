<?php

namespace Prontotype;

use Symfony\Component\Yaml\Yaml;

Class Config {
    
    protected $app;
    
    protected $env;
    
    protected $configs;
    
    protected $loadPaths = array();
    
    protected $fallbackPath;

    public function __construct($app, $loadPaths = array(), $fallbackPath = null, $env = 'live')
    {
        $this->app = $app;
        $this->env = $env;
        $this->fallbackPath = $fallbackPath;
        foreach($loadPaths as $path) {
            $this->addLoadPath($path);
        }
        $this->mergeConfig();
    }
    
    public function addLoadPath($path)
    {
        if ( file_exists($path) ) {
            $this->loadPaths[] = $path . '/' . $this->env . '.yml';
            $this->loadPaths[] = $path . '/config.yml';
            $this->mergeConfig();
        }
    }
    
    public function getLoadPaths()
    {
        $paths = $this->loadPaths;
        if ( $this->fallbackPath ) {
            array_push($paths, $this->fallbackPath . '/' . $this->env . '.yml');
            array_push($paths, $this->fallbackPath . '/config.yml');
        }
        array_push($paths, $this->fallbackPath . '/_system/config.yml');
        return array_reverse($paths);
    }
    
    public function get($path = null)
    {
        $config = $this->configs;
        if ( empty($config) ) {
            return null;
        }
        if ( empty($path) ) {
            return $config;
        }
        $pathparts = explode( '.', trim( $path, '.') );
        if ( count( $pathparts) ) {
            foreach ( $pathparts as $key ) {
                if ( isset( $config[$key] ) ) {
                    $config = $config[$key];
                } else {
                    return null;
                    break;
                }
            }
        }
        return $config;
    }
    
    public function getDefaultConfigs($raw = false)
    {
        $config = file_get_contents($this->fallbackPath . '/config.yml');        
        if ( ! $raw ) {
            $config = Yaml::parse($config);
        }
        return $config;
    }
    
    protected function mergeConfig()
    {
        $config = array();
        $loadPaths = $this->getLoadPaths();
        foreach($loadPaths as $loadPath) {
            if ( file_exists($loadPath)) {
                if ( trim(file_get_contents($loadPath)) !== '' ) {
                    $parsed = Yaml::parse($loadPath);
                    if ($parsed !== null) {
                        $config = $this->merge($config, $parsed);
                    }
                }
            }
        }
        $this->configs = $config;
    }
    
    protected function merge( array &$array1, array &$array2 )
    {
        $merged = $array1;
        foreach ( $array2 as $key => &$value ) {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
                $merged [$key] = $this->merge( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }
    
}