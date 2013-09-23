<?php

namespace Prontotype\Assets;

use Prontotype\Cache;
use Prontotype\Prototype;

use Symfony\Component\HttpFoundation\File\File;
use PHPImageWorkshop\ImageWorkshop;

Class Manager {
    
    protected $app;
    
    protected $processors = array();
    
    protected $loadPaths = array();
    
    protected $fallbackPath = null;
    
    protected $aliases = array(
        'css' => array('less', 'scss')
    );

    public function __construct($app, $processors = array(), $loadPaths = array(), $fallbackPath = null)
    {
        $this->app = $app;
        $this->loadPaths = $loadPaths;
        $this->fallbackPath = $fallbackPath;
        foreach( $processors as $processor ) {
            $this->registerProcessor($processor);
        }
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
    
    public function generatePlaceholderImg($size = 300, $bgcolour = 'CCC', $colour = '999', $text = null)
    {
        $sizes = explode('x', strtolower($size));
        if (count($sizes) == 1) {
            $width = $sizes[0];
            $height = $sizes[0];
        } elseif (count($sizes) > 1) {
            $width = $sizes[0];
            $height = $sizes[1];
        }
        
        $bgcolour = empty($bgcolour) ? $this->makeHexColour('CCC') : $this->makeHexColour($bgcolour);
        $colour = empty($colour) ? $this->makeHexColour('999') : $this->makeHexColour($colour);
        
        $text = empty($text) ? $width . 'x' . $height : $text;
        $fontPath = $this->app['pt.app.paths.assets'] . '/_system/fonts/prontotype.ttf';
        
        $key = md5($width . $height . $bgcolour . $colour . $text);

        if ( $imageData = $this->app['pt.cache']->get(Cache::CACHE_TYPE_IMAGES, $key) ) {
            return $imageData;
        }
        
        $layer = ImageWorkshop::initVirginLayer($width, $height, $bgcolour);
        $textLayer = ImageWorkshop::initTextLayer($text, $fontPath, 20, $colour, 0, $bgcolour);
        if ( ($textLayer->getWidth() - 40) > $layer->getWidth() ) {
            $textLayer->resizeInPixel($width - 40, null, true);   
        }
        $layer->addLayer(1, $textLayer, 0, 0, 'MM');
        $img = $layer->getResult();
        
        ob_start();
        imagepng($img);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        $this->app['pt.cache']->set(Cache::CACHE_TYPE_IMAGES, $key, $imageData);
        return $imageData;
    }
    
    protected function makeHexColour($colour)
    {
        if (strlen($colour) == 3) {
            $colour = $colour[0] . $colour[0] . $colour[1] . $colour[1] . $colour[2] . $colour[2];
        }
        return $colour;
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
    
    public function aliasFilePath($path)
    {
        $pathParts = pathinfo($path);
        $ext = strtolower($pathParts['extension']);
        foreach( $this->aliases as $extension => $aliases ) {
            if ( in_array($ext, $aliases) ) {
                return preg_replace('/\.' . $ext . '$/', '.' . $extension, $path);
            }
        }
        return $path;
    }
    
    protected function process($contents, $extension)
    {
        $extension = strtolower($extension);
        if ( ! isset($this->processors[$extension]) ) {
            return $contents;
        }
        foreach( $this->processors[$extension] as $parser ) {
            try {
                $contents = $parser->process($contents, $this->getLoadPaths());
            } catch ( \Exception $e ) {
                throw new \Exception(sprintf('Error processing file'));
            }
        }
        return $contents;
    }
    
    protected function findAssetFile($assetPath)
    {
        $loadPaths = $this->getLoadPaths();        
        
        if ( strpos($assetPath,'::') !== false && $this->app['pt.config']->get('sideload') ) {
            list($label, $path) = explode('::', $assetPath);
            $pt = new Prototype($this->app['pt.prototypes.definitions'], $this->app['pt.prototypes.loadpaths'], $this->app);
            try {
                $pt->load($label);
                $assetPath = $path;
                array_unshift($loadPaths, $pt->getPathTo('assets'));
            } catch( \Exception $e ) {}
        }        
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
        if ( isset($pathParts['extension']) ) {
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
        return array();
    }
    
    protected function getMimeType($fullPath)
    {
        $parts = pathinfo($fullPath);
        $extension = strtolower($parts['extension']);
        $mime = $this->app['pt.utils']->getMimeTypeForExtension($extension);
        if ( $mime ) {
            return $mime;
        }
        $file = new File($fullPath);
        return $file->getMimeType();
    }
}