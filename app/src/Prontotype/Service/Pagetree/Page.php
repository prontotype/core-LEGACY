<?php

namespace Prontotype\Service\PageTree;

use SPLFileInfo;
use DirectoryIterator;
use Exception;

Class Page extends Base {
        
    protected $id = null;
    
    protected $position = null;
    
    protected $shortUrl = null;
    
    protected $isIndex = null;
        
    public function __construct( SPLFileInfo $file, $app )
    {
        if ( ! $file->isFile() ) {
            throw new \Exception('File is not a file');
        }
        parent::__construct($file, $app);
    }
    
    public function getId()
    {
        if ( $this->id === null ) {
            $this->parseFileName();
        }
        return empty($this->id) ? null : $this->id;
    }
    
    public function getShortUrl()
    {
        if ( $id = $this->getId() ) {
            $this->shortUrl = $this->prefixUrl('/' . $this->app['config']['triggers']['shorturl'] . '/' . $id);
        } else {
            $this->shortUrl = $this->getUrlPath();
        }
        return $this->shortUrl;
    }
    
    public function isIndex()
    {
        if ( $this->isIndex === null ) {
            $this->parseFileName();
        }
        return $this->isIndex;
    }
    
    public function isCurrent()
    {
        return $this->matchesUrlPath($this->app['uri']->string());
    }
    
    public function isParentOfCurrent()
    {
        $uriSegments = $this->app['uri']->segments();
        $urlPathSegments = explode('/', trim($this->unPrefixUrl($this->getUrlPath()),'/'));
        if ( count($urlPathSegments) >= count($uriSegments) ) {
            return false;
        }
        for ( $i = 0; $i < count($urlPathSegments); $i++ ) {
            if ( $uriSegments[$i] !== $urlPathSegments[$i] ) {
                return false;
            }
        }
        return true;
    }
    
    public function toArray()
    {
        return array(
            'id'        => $this->getId(),
            'depth'     => $this->getDepth(),
            'shortUrl'  => $this->getShortUrl(),
            'niceName'  => $this->getNiceName(),
            'name'      => $this->getCleanName(),
            'urlPath'   => $this->getUrlPath(),
            'relPath'   => $this->getRelPath(),
            'fullPath'  => $this->getFullPath(),
            'isCurrent' => $this->isCurrent(),
            'isParentOfCurrent' => $this->isParentOfCurrent(),
            'isPage'    => true,
        );
    }
    

}
