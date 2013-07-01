<?php

namespace Prontotype\PageTree;

use SPLFileInfo;
use DirectoryIterator;
use Exception;

Class Page extends Base {
        
    protected $id = null;
    
    protected $position = null;
    
    protected $shortUrl = null;
    
    protected $isIndex = null;
        
    public function __construct(SPLFileInfo $file, $app)
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
    
    public function getShortUrlPath()
    {
        if ( $id = $this->getId() ) {
            $this->shortUrlPath = $this->prefixUrl('/' . $this->app['pt.config']->get('triggers.shorturl') . '/' . $id);
        } else {
            $this->shortUrlPath = $this->getUrlPath();
        }
        return $this->shortUrlPath;
    }
    
    public function getShortUrl()
    {
        return $this->app['pt.request']->getUriForPath($this->getShortUrlPath());
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
        return $this->matchesUrlPath($this->app['pt.request']->getUrlPath());
    }
    
    public function isParentOfCurrent()
    {
        $uriSegments = $this->app['pt.request']->getUrlSegments();
        if ( $urlPath = trim($this->unPrefixUrl($this->getUrlPath()),'/') ) {
            $urlPathSegments = explode('/', $urlPath);            
        } else {
            $urlPathSegments = array();
        }

        if ( count($uriSegments) && count($urlPathSegments) == 0 ) {
            return true;
        }
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
    
    public function hasSubPages()
    {
        $subPageArray = $this->app['pt.pages']->getSubPagesByUrlPath($this->getUrlPath());
        return !! count($subPageArray);
    }
    
    public function getSubPages()
    {
        $subPageArray = $this->app['pt.pages']->getSubPagesByUrlPath($this->getUrlPath());        
        $subPageObjects = array();
        foreach( $subPageArray as $subPage ) {
            $subPageObjects[] = $this->app['pt.pages']->getByUrlPath($subPage['urlPath']);
        }
        return $subPageObjects;
    }
    
    public function toArray()
    {
        return array(
            'id'        => $this->getId(),
            'depth'     => $this->getDepth(),
            'shortUrlPath'  => $this->getShortUrlPath(),
            'shortUrl'  => $this->getShortUrl(),
            'niceName'  => $this->getNiceName(),
            'title'     => $this->getTitle(),
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
