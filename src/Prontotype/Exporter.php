<?php

namespace Prontotype;

use Prontotype\Cache;
use Symfony\Component\HttpKernel\Client;

Class Exporter {

    protected $app;
    
    protected $client;
    
    protected $processedPaths = array();
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function run($startPaths = null)
    {
        $this->app['pt.cache']->deleteRaw(Cache::CACHE_TYPE_EXPORTS);
        $exportTag = 'export';
        
        if ( ! $startPaths ) {
            $startPaths = array('/');
        }
        foreach( $startPaths as $path ) {
            $this->processPath($path, $exportTag);
        }
    }
    
    protected function processPath($urlPath, $exportTag)
    {        
        if ( $this->isValidPath($urlPath) ) {
            
            $canonicalPath = $this->getCanonicalPath($urlPath);
            
            if ( ! in_array($canonicalPath, $this->processedPaths) ) {
                
                $this->processedPaths[] = $canonicalPath;
                
                $client = new Client($this->app);
                $crawler = $client->request('GET', $urlPath);
                $html = $client->getResponse()->getContent();
                
                $links = $crawler->filter('a[href]');
                $replacements = array();
                
                foreach($links as $link) {
                    $href = $link->getAttribute('href');  
                    if ( ! empty($href) ) {
                        $canonicalHref = $this->getCanonicalPath($href);
                        $this->processPath($href, $exportTag);
                        if ( $this->isValidPath($href) ) {
                            $replacements[$href] = $canonicalHref;    
                        }
                    }
                }
                
                echo '<pre>';
                print_r($canonicalPath);
                echo '</pre>';
                
                $currentUrlParts = array_slice(explode('/', trim($canonicalPath, '/')), 0, -1);
                // echo '<pre>';
                // print_r($currentUrlParts);
                // echo '</pre>';
                // echo '<pre>';
                // print_r('-------');
                // echo '</pre>';
                
                $relReplacements = array();
                foreach($replacements as $key => $replacement) {
                    $repParts = explode('/', trim($replacement, '/'));
                    if ( $repParts < $currentUrlParts ) {
                        for ($i = 0; $i < count($currentUrlParts); $i++ ) {
                            if ( $repParts[$i] == $currentUrlParts[$i] ) {
                                unset($repParts[$i]);
                            }
                        }                        
                    } else {
                        for ($i = 0; $i < count($currentUrlParts); $i++ ) {
                            if ( $repParts[$i] == $currentUrlParts[$i] ) {
                                $repParts[$i] == '../';
                            }
                        }
                    }
                    
                    $relReplacements[$key] = './' . implode('/', $repParts);
                }
                
                uksort($relReplacements, function($a,$b){
                    return strlen($a) < strlen($b);
                });

                
                echo '<pre>';
                print_r($relReplacements);
                echo '</pre>';
                
                echo '<pre>';
                print_r("================");
                echo '</pre>';
                

                
                $html = str_replace(array_keys($relReplacements), array_values($relReplacements), $html);
                
                $this->app['pt.cache']->setRaw(Cache::CACHE_TYPE_EXPORTS, $canonicalPath, $html, $exportTag);
            }   
        }
    }
    
    protected function getCanonicalPath($path)
    {
        $path = str_replace('/index.php', '', $path);
        if ( empty($path) || $path == '/' ) {            
            $path = '/index';
        }
        return str_replace('/index.php', '', $path) . '.html';
    }
    
    protected function isValidPath($urlPath)
    {   
        if ( strpos($urlPath, 'http') === 0 ) {
            return false;
        } 
        return true;
    }
    
}
