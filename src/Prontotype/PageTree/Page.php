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
    
    public function getExtension()
    {
        return $this->extension;
    }
    
    public function getMimeType()
    {
        $mime = $this->app['pt.utils']->getMimeTypeForExtension($this->getExtension());
        return $mime ? $mime : 'text/html';
    }
    
    public function isIndex()
    {
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
                $hideFlag = strpos($segment, '_') === 0 ? '_' : '';
                preg_match($this->nameFormatRegex, $segment, $segmentParts);
                $cleanSegments[] = $hideFlag . (empty($segmentParts[3]) ? $segmentParts[6] : $segmentParts[3]);
            }
            if ( strpos($cleanSegments[count($cleanSegments)-1],'index.') === 0 ) {
                unset($cleanSegments[count($cleanSegments)-1]);
            }
            $up = rtrim($this->app['pt.prototype.path'] . '/' . implode('/', $cleanSegments),'/');
            if ( $up == '' ) {
                $up = '/';
            }
            $this->urlPath = $this->prefixUrl($up);
        }
        $ext = pathinfo($this->urlPath, PATHINFO_EXTENSION);
        if ( in_array($ext, $this->cloakedExtensions) ) {
            $this->urlPath = $this->stripExtension($this->urlPath);
        }
        return $this->urlPath;
    }
    
    public function setUrlPath($urlPath)
    {
        $this->urlPath = $this->prefixUrl('/' . trim($urlPath,'/'));
        $originalExtension = $this->pathInfo['extension'];
        $this->pathInfo['basename'] = pathinfo($urlPath, PATHINFO_BASENAME);
        $this->pathInfo['filename'] = pathinfo($urlPath, PATHINFO_FILENAME);
        $this->pathInfo['extension'] = pathinfo($urlPath, PATHINFO_EXTENSION);
        if ( empty($this->pathInfo['extension']) ) {
            $this->pathInfo['extension'] = $originalExtension;
        }
        $this->parseFileName($this->pathInfo['filename']);
    }
    
    public function toArray()
    {
        return array(
            'id'                => $this->getId(),
            'depth'             => $this->getDepth(),
            'mimeType'          => $this->getMimeType(),
            'extension'         => $this->getExtension(),
            'shortUrlPath'      => $this->getShortUrlPath(),
            'shortUrl'          => $this->getShortUrl(),
            'niceName'          => $this->getNiceName(),
            'title'             => $this->getTitle(),
            'name'              => $this->getCleanName(),
            'urlPath'           => $this->getUrlPath(),
            'relPath'           => $this->getRelPath(),
            'fullPath'          => $this->getFullPath(),
            'isHidden'          => $this->isHidden(),
            'isPublic'          => $this->isPublic(),
            'isCurrent'         => $this->isCurrent(),
            'isParentOfCurrent' => $this->isParentOfCurrent(),
            'isPage'            => true,
        );
    }
    

}
