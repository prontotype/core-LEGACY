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
    
    public function run($startPaths = null, $exportTag = null)
    {
        if ( $exportTag ) {
            $this->app['pt.cache']->deleteRaw(Cache::CACHE_TYPE_EXPORTS, $exportTag);  
        } else {
            $exportTag = time() . '-' . date('Y-m-d');
        }
        
        $exportHtml = $exportTag . '/html';
        
        if ( ! $startPaths ) {
            $startPaths = array('/');
        }
        foreach( $startPaths as $path ) {
            $this->processPath($path, $exportHtml);
        }
        
        $exportPath = $this->app['pt.cache']->getCacheDirPath(Cache::CACHE_TYPE_EXPORTS) . '/' . $exportTag;
        $exportHtmlPath = $this->app['pt.cache']->getCacheDirPath(Cache::CACHE_TYPE_EXPORTS) . '/' . $exportHtml;
        $zipPath = $exportPath . '/' . $exportTag . '.zip';
        if ( $this->app['pt.utils']->zipDir($exportHtmlPath, $zipPath) ) {
            return array(
                'filename' => $exportTag . '.zip',
                'tag' => $exportTag,
                'path' => $zipPath
            );            
        } else {
            return false;
        }
    }
    
    public function clear()
    {
        $this->app['pt.cache']->deleteRaw(Cache::CACHE_TYPE_EXPORTS); 
    }
    
    public function listContents()
    {
        $tags = $this->app['pt.cache']->listContents(Cache::CACHE_TYPE_EXPORTS);
        $details = array();
        foreach($tags as $tag) {
            $details[] = $this->getExportDetails($tag);
        }
        return $details;
    }
    
    public function getExportDetails($exportTag)
    {
        $zipPath = $this->app['pt.cache']->getCacheDirPath(Cache::CACHE_TYPE_EXPORTS) . '/' . $exportTag . '/' . $exportTag . '.zip';
            
        if ( ! file_exists($zipPath) ) {
            return null;
        }
        return array(
            'filename' => $exportTag . '.zip',
            'tag' => $exportTag,
            'path' => $zipPath,
        );
    }
    
    protected function processPath($urlPath, $exportTag)
    {        
        if ( $this->isValidPath($urlPath) ) {
            
            $cleanPath = $this->cleanPath($urlPath);
            
            if ( ! in_array($cleanPath, $this->processedPaths) ) {
                
                $this->processedPaths[] = $cleanPath;
                
                $client = new Client($this->app);
                $crawler = $client->request('GET', $urlPath);
                $html = $client->getResponse()->getContent();
                
                $links = $crawler->filter('a[href]');
                $replacements = array();
                
                foreach($links as $link) {
                    $href = $link->getAttribute('href');  
                    if ( ! empty($href) ) {
                        $canonicalHref = $this->cleanPath($href, $urlPath);
                        $rootRelativeHref = $this->convertRelativeToRootRelativeUrl($href, $urlPath);
                        $this->processPath($rootRelativeHref, $exportTag);
                        if ( $this->isValidPath($canonicalHref) ) {
                            $replacements[$href] = $this->convertUrl($canonicalHref, $cleanPath);        
                        }
                    }
                }
                
                uksort($replacements, function($a,$b){
                    return strlen($a) < strlen($b);
                });
                
                $quotedReplacements = array();
                foreach($replacements as $key => $replacement) {
                    $replacement = str_replace('index.php', '',$replacement);
                    $replacement = str_replace('//','/', $replacement);
                    $quotedReplacements['"' . $key] = '"' . $replacement;
                    $quotedReplacements['=' . $key] = '=' . $replacement;
                    $quotedReplacements['\'' . $key] = '\'' . $replacement;
                }
                
                $html = str_replace(array_keys($quotedReplacements), array_values($quotedReplacements), $html);
                
                $cleanPath = $this->stripIndex($cleanPath);

                if ( !empty($html) ) {
                    $this->app['pt.cache']->setRaw(Cache::CACHE_TYPE_EXPORTS, $cleanPath, $html, $exportTag);                    
                }
            }
        }
    }
    
    protected function stripIndex($path)
    {
        $path = str_replace('index.php', '',$path);
        $path = str_replace('//','/', $path);
        return $path;
    }
    
    protected function cleanPath($path, $currentUrlPath = null)
    {
        if ( $path[0] == '.' && $currentUrlPath ) {
            $path = $this->convertRelativeToRootRelativeUrl($path, $currentUrlPath);
        }
        $indexLessPath = $this->stripIndex($path);
        if ( empty($indexLessPath) || $indexLessPath == '/' ) {
            $path = '/index';
        }
        
        return $path . '.html';
    }
    
    protected function convertUrl($urlPath, $currentUrlPath)
    {
        if ( $urlPath[0] == '/' ) {
            return $this->convertRootRelativeToRelativeUrl($urlPath, $currentUrlPath);    
        } elseif ( $urlPath[0] == '.' ) {
            return $this->convertRelativeToRootRelativeUrl($urlPath, $currentUrlPath);
        }
        return $urlPath;
    }
    
    protected function convertRelativeToRootRelativeUrl($relativeUrl, $currentUrlPath)
    {
        $pathParts = explode('/', $relativeUrl);
        $currentPathParts = explode('/', $currentUrlPath);
        $i = 0;
        foreach( $pathParts as &$part ) {
            if ($part == '..') {
                $part = $currentPathParts[$i];
            }
            $i++;
        }
        return implode('/', $pathParts);
    }
    
    protected function convertRootRelativeToRelativeUrl($rootRelativeUrlPath, $currentUrlPath)
    {
        // absolute link
        $relUrl = '';
        
        $currentUrlParts = explode('/', trim($currentUrlPath, '/'));
        $currentUrlPage = $currentUrlParts[count($currentUrlParts)-1];
        unset($currentUrlParts[count($currentUrlParts)-1]);
        
        $repParts = explode('/', trim($rootRelativeUrlPath, '/'));
        $repPartsPage = $repParts[count($repParts)-1];
        unset($repParts[count($repParts)-1]);


        for ($i = 0; $i < count($currentUrlParts); $i++ ) {               
            if ( isset($repParts[$i]) && ($repParts[$i] == $currentUrlParts[$i]) ) {
                unset($currentUrlParts[$i]);
                unset($repParts[$i]);
            } else {
                break;
            }
        }
    
        $currentUrlParts = array_values($currentUrlParts);
        $repParts = array_values($repParts);
    
        foreach($currentUrlParts as $currentUrlPart) {
            array_unshift($repParts, '../');
        }
    
        if ( count($repParts) ) {
            $relUrl = implode('/', $repParts) . '/' . $repPartsPage;    
        } else {
            $relUrl = $repPartsPage;    
        }
                
                        
        if ( $relUrl[0] !== '.' ) {
            $relUrl = './' . $relUrl;
        }
        $relUrl = str_replace('//','/', $relUrl);
            
        return $relUrl;
    }
    
    protected function isValidPath($urlPath)
    {   
        if ( strpos($urlPath, 'http') === 0 ) {
            return false;
        } 
        return true;
    }
    
}
