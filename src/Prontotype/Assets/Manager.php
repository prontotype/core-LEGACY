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

    public function __construct($app, $processors = array())
    {
        $this->app = $app;
        foreach( $processors as $processor ) {
            $this->registerProcessor($processor);
        }
    }
    
    public function generateAsset($assetPath)
    {        
        $fullPath = $this->app['pt.prototype.paths.assets'] . '/' . strtolower($assetPath);
        
        if ( ! file_exists( $fullPath ) ) {
            $aliasPath = null;
            $aliases = $this->getPathAliases($fullPath);
            if ( count($aliases) ) {
                foreach($aliases as $alias) {
                    if ( file_exists($alias) ) {
                        $aliasPath = $alias;
                    }
                }
            }
            if ( ! $aliasPath ) {
                throw new \Exception('File not found'); // TODO replace with specific exception
            } else {
                $fullPath = $aliasPath;
            }
        }
        
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