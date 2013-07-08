<?php

namespace Prontotype\PageTree;

use SPLFileInfo;
use DirectoryIterator;
use Exception;

Class Directory extends Base {
    
    protected $urlPath;
    
    public function __construct(SPLFileInfo $directory, $app)
    {
        if ( ! $directory->isDir() ) {
            throw new Exception('Not a directory');
        }
        parent::__construct($directory, $app);
        $items = array();
        foreach( new DirectoryIterator($this->fullPath) as $item ) {
            if ( $this->isValidFile($item) ) {
                $path = $item->getPath() . '/' .  $item->getBasename();
                if ( $item->isDir() ) {
                    $items[] = new Directory($item, $app);
                } else {
                    $items[] = new Page($item, $app);
                }
            }
        }
        uasort($items, function( $a, $b ){          
            return strnatcasecmp($a->getRelPath(), $b->getRelPath());
        });
        $this->items = $items;
    }
    
    public function getUrlPath()
    {
        if ( $this->urlPath === null ) {
            $segments = explode('/', trim($this->getRelPath(),'/'));
            $cleanSegments = array();
            foreach($segments as $segment) {
                $cleanSegments[] = preg_replace('/^((\d*)[\._\-]).*?/', '', $segment);
            }
            $up = rtrim($this->app['pt.prototype.path'] . '/' . implode('/', $cleanSegments),'/');
            if ( $up == '' ) {
                $up = '/';
            }
            $this->urlPath = $this->prefixUrl($up);
        }
        return $this->urlPath;
    }
    
    public function matchesUrlPath($urlPath)
    {
        return parent::matchesUrlPath($urlPath);
    }
    
    public function hasSubPages()
    {
        return count($this->items);
    }
    
    public function getSubPages()
    {
        return $this->items;
    }
    
    public function toArray($siblings = null)
    {
        $subPages = array();
        $output = array(
            'depth'    => $this->getDepth(),
            'niceName' => $this->getNiceName(),
            'title'    => $this->getTitle(),
            'name'     => $this->getCleanName(),
            "relPath"  => $this->getRelPath(),
            "fullPath" => $this->getFullPath(),
            'urlPath'  => $this->getUrlPath(),
            "isPage"   => false,
        );
        $hasIndex = false;
        foreach( $this as $item ) {
            if ( $item instanceof Page && $item->isIndex() ) {
                $hasIndex = true;
                $output = $item->toArray();
            } else {
                $subPages[] = $item->toArray($this->getItems());
            }
        }
        if ( ! $hasIndex && $siblings ) {
            foreach( $siblings as $sibling ) {
                if (  $this->getUrlPath() == $sibling->getUrlPath() ) {
                    $output = $sibling->toArray();
                    break;
                }
            }
        }
        $filteredChildren = array();
        foreach( $subPages as $child ) {
            if ( ! isset($filteredChildren[$child['urlPath']]) ) {
                $filteredChildren[$child['urlPath']] = $child;
            } else {
                if ( ! isset($filteredChildren[$child['urlPath']]['subPages']) || ! count($filteredChildren[$child['urlPath']]['subPages']) ) {
                    $filteredChildren[$child['urlPath']] = $child;
                }
            }
        }
        $subPages = $filteredChildren;
        uasort($subPages, function( $a, $b ){          
            return strnatcasecmp($a['relPath'], $b['relPath']);
        });
        $output['subPages'] = array_values($subPages);
        return $output;
    }
}
