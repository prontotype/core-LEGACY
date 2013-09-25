<?php

namespace Prontotype\Assets;

use Prontotype\Cache;
use Prontotype\Prototype;

Class FileManager {
    
    protected $app;
    
    protected $processors = array();
    
    protected $loadPaths = array();
    
    protected $fallbackPath = null;

    public function __construct($app, $loadPaths = array(), $fallbackPath = null)
    {
        $this->app = $app;
        $this->loadPaths = $loadPaths;
        $this->fallbackPath = $fallbackPath;
    }
    
    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }
    
    public function getLoadPaths()
    {
        $paths = $this->loadPaths;
        if ( $this->fallbackPath ) {
            $paths[] = $this->fallbackPath;
        }
        return $paths;
    }
    
    public function findFile($filePath)
    {
        $loadPaths = $this->getLoadPaths();        
        
        if ( strpos($filePath,'::') !== false && $this->app['pt.config']->get('sideload') ) {
            list($label, $path) = explode('::', $filePath);
            $pt = new Prototype($this->app['pt.prototypes.definitions'], $this->app['pt.prototypes.loadpaths'], $this->app);
            try {
                $pt->load($label);
                $filePath = $path;
                array_unshift($loadPaths, $pt->getPathTo('files'));
            } catch( \Exception $e ) {}
        }        
        foreach($loadPaths as $loadPath) {            
            $fullPath = $loadPath . '/' . strtolower($filePath);
            if ( file_exists( $fullPath ) ) {
                break;
            }
        }
        
        if ( ! file_exists($fullPath) ) {
            throw new \Exception('File \'' . $filePath . '\' not found'); // TODO replace with specific exception
        }
        
        return $fullPath;
    }
    
}