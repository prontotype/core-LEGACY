<?php

namespace Prontotype\Assets;

use Prontotype\Cache;

use Symfony\Component\HttpFoundation\File\File;

Class Manager {
    
    protected $app;
    
    protected $processors = array();
    
    protected $aliases = array(
        'css' => array('less', 'scss')
    );
    
    protected $loadPaths = array();

    public function __construct($app, $processors = array(), $loadPaths = array())
    {
        $this->app = $app;
        $this->loadPaths = $loadPaths;
        foreach( $processors as $processor ) {
            $this->registerProcessor($processor);
        }
    }
    
    public function generateAsset($assetPath)
    {        
        $fullPath = $this->findAssetFile($assetPath);
        
        $lastEditTime = filemtime($fullPath);
        $parts = pathinfo($fullPath);
        
        if ( ! $parsed = $this->app['pt.cache']->get(Cache::CACHE_TYPE_ASSETS, $fullPath, $lastEditTime) ) {
            $contents = file_get_contents($fullPath);        
            $parsed = $this->process($contents, $parts['extension']);
            $path = $this->app['pt.cache']->set(Cache::CACHE_TYPE_ASSETS, $fullPath, $parsed);            
        }
        
        return array(
            'mime' => $this->getMimeType($fullPath),
            'content' => $parsed
        );
    }
    
    public function registerProcessor(Processor $processor)
    {
        foreach( $processor->getHandledExtensions() as $extension ) {
            $extension = strtolower($extension);
            if ( ! isset($this->processors[$extension]) ) {
                $this->processors[$extension] = array();
            }
            $this->processors[$extension][] = $processor;
        }
    }
    
    protected function process($contents, $extension)
    {
        $extension = strtolower($extension);
        if ( ! isset($this->processors[$extension]) ) {
            return $contents;
        }
        foreach( $this->processors[$extension] as $parser ) {
            try {
                $contents = $parser->process($contents);
            } catch ( \Exception $e ) {
                throw new \Exception(sprintf('Error processing file'));
            }
        }
        return $contents;
    }
    
    protected function findAssetFile($assetPath)
    {
        $loadPaths = $this->loadPaths;
        $loadPaths[] = $this->app['pt.core.paths.assets']; // append core path
        foreach($loadPaths as $loadPath) {            
            $fullPath = $loadPath . '/' . strtolower($assetPath);
            if ( ! file_exists( $fullPath ) ) {
                $aliases = $this->getPathAliases($fullPath);
                if ( count($aliases) ) {
                    foreach($aliases as $alias) {
                        if ( file_exists($alias) ) {
                            $fullPath = $alias;
                            break 2;
                        }
                    }
                }
            } else {
                break;
            }
        }
        
        if ( ! file_exists($fullPath) ) {
            throw new \Exception('Asset file \'' . $assetPath . '\' not found'); // TODO replace with specific exception
        }
        
        return $fullPath;
    }
    
    protected function getPathAliases($path)
    {
        $pathParts = pathinfo($path);
        $ext = strtolower($pathParts['extension']);
        $aliases = array();
        if ( isset($this->aliases[$ext]) ) {
            $baseName = $pathParts['dirname'] . '/' . $pathParts['filename'] . '.';
            foreach( $this->aliases[$pathParts['extension']] as $aliasExt ) {
                $aliases[] = $baseName . $aliasExt;
            }
        }
        return $aliases;
    }
    
    protected function getMimeType($fullPath)
    {
        $parts = pathinfo($fullPath);
        $extension = strtolower($parts['extension']);
        switch( $extension ) {
            case 'css':
            case 'less':
            case 'scss':
                return 'text/css';
                break;
            case 'js':
            case 'coffee':
                return 'text/javascript';
                break;
            default:
                $file = new File($fullPath);
                return $file->getMimeType(); 
        }
    }
}