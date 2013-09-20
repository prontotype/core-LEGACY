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
    
    public function getMimeType()
    {
        $mime = $this->app['pt.utils']->getMimeTypeForExtension($this->getTypeHint());
        return $mime ? $mime : 'text/html';
    }
    
    public function isIndex()
    {
        if ( $this->isIndex === null ) {
            $this->parseFileName();
        }
        return $this->isIndex;
    }
    
    public function isHome()
    {
        if ( $this->matchesUrlPath($this->app['pt.pages']->getHomeUrlPath()) ) {
            return true;
        }
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
            if ( ! $this->app['pt.pages']->getByUrlPath($subPage['urlPath']) ) {
                // see if there is a directory that matches
                if ( $dir = $this->app['pt.pages']->getDirectoryByUrlPath($subPage['urlPath']) ) {
                    $subPageObjects[] = $dir;                 
                }
            } else {
                $subPageObjects[] = $this->app['pt.pages']->getByUrlPath($subPage['urlPath']);   
            }             
        }
        return $subPageObjects;
    }
    
    public function getUrlPath()
    {
        if ( $this->urlPath === null ) {
            $segments = explode('/', trim($this->getRelPath(),'/'));
            $cleanSegments = array();
            foreach( $segments as $segment ) {
                preg_match($this->nameFormatRegex, str_replace($this->nameExtension, '', $segment), $segmentParts);
                $cleanSegments[] = empty($segmentParts[3]) ? $segmentParts[6] : $segmentParts[3];
            }
            if ( $cleanSegments[count($cleanSegments)-1] == 'index' || strpos($cleanSegments[count($cleanSegments)-1], 'index.') === 0 ) {
                unset($cleanSegments[count($cleanSegments)-1]);
            }
            $up = rtrim($this->app['pt.prototype.path'] . '/' . implode('/', $cleanSegments),'/');
            if ( $up == '' ) {
                $up = '/';
            }
            $this->urlPath = $this->prefixUrl($up);
        }
        return $this->urlPath;
    }
    
    public function toArray()
    {
        return array(
            'id'        => $this->getId(),
            'depth'     => $this->getDepth(),
            'mimeType'  => $this->getMimeType(),
            'contentType' => $this->getTypeHint(),
            'typeHint'  => $this->getTypeHint(),
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
