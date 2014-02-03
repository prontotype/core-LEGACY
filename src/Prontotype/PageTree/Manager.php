<?php

namespace Prontotype\PageTree;

Class Manager {
    
    protected $app;
    
    protected $tree;
    
    protected $treeArray = null;
    
    protected $current = null;
    
    protected $routeCache = array();
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->tree = new Directory(new \SPLFileInfo($app['pt.prototype.paths.templates']), $app);
    }
    
    public function getHomeUrlPath()
    {
        return empty($this->app['pt.prototype.path']) ? '/' : $this->app['pt.prototype.path'];
    }
    
    public function getCurrent()
    {
        if ( ! $this->current ) {
            $requestPath = $this->app['pt.request']->getUrlPath();
            $requestPath = empty($requestPath) ? '/' : $requestPath;
            $this->current = clone $this->getByRoute($requestPath);
            if ( $this->current && $requestPath !== str_replace('/index.php', '', $this->current->getUrlPath()) ) {
                // been rerouted
                $this->current->setUrlPath($requestPath);
            }
        }
        return $this->current;
    }
    
    public function getById($id)
    {
        foreach( $this->getRecursivePagesIterator() as $page ) {
            if ( $page instanceof Page && $page->getId() == $id ) {
                return $page;
            }
        }
        return null;
    }
    
    public function getByUrlPath($path, $matchHidden = false)
    {
        if ( $path === '/' && ! empty($this->app['pt.prototype.path']) ) {
            $path = $this->app['pt.prototype.path'];
        }
        foreach( $this->getRecursivePagesIterator() as $page ) {
            if ( $page->matchesUrlPath($path, $matchHidden) ) {
                return $page;
            }
        }
        return null;
    }
    
    public function getDirectoryByUrlPath($path)
    {
        if ( $path === '/' && ! empty($this->app['pt.prototype.path']) ) {
            $path = $this->app['pt.prototype.path'];
        }
        foreach( new \RecursiveIteratorIterator($this->tree, true) as $item ) {
            if ( $item instanceof Directory && $item->matchesUrlPath($path) ) {
                return $item;
            }
        }
        return null;
    }
    
    public function getByRoute($route)
    {
        $newRoute = $this->app['pt.route_matcher']->convertRoute($route);
        $usingCustomRoute = ($route == $newRoute ? false : true);
        return $this->getByUrlPath($newRoute, $usingCustomRoute);
    }
    
    public function getUrlById($id)
    {
        if ( $page = $this->getById($id) ) {
            return $page->getUrl();
        }
        return '#';
    }
    
    public function getUrlForRoute($routeName, $params = array())
    {
        if ( $url = $this->app['pt.route_matcher']->getUrlForRoute($routeName, $params) ) {
            return $url;
        }
        return '#';
    }
    
    public function getUrlPathForRoute($routeName, $params = array())
    {
        if ( $urlPath = $this->app['pt.route_matcher']->getUrlPathForRoute($routeName, $params) ) {
            return $urlPath;
        }
        return '#';
    }
    
    public function getUrlPathById($id)
    {
        if ( $page = $this->getById($id) ) {
            return $page->getUrlPath();
        }
        return '#';
    }
    
    public function getSubPagesById($id)
    {
        if ( $page = $this->getById($id) ) {
            return $this->getSubPagesByUrlPath($page->getUrlPath());
        }
        return null;
    }
    
    public function getSubPagesByUrlPath($urlPath)
    {
        if ( $urlPath == '/' || $urlPath == '/index.php/' || str_replace('/index.php', '', rtrim($urlPath, '/')) == $this->getHomeUrlPath() ) {
            $data = $this->getAll();
            return isset($data[0]['subPages']) ? $data[0]['subPages'] : null;
        }
        $fullTree = new \RecursiveIteratorIterator($this->tree, true);
        foreach( $fullTree as $item ) {
            if ( $item instanceof Directory && $item->matchesUrlPath($urlPath) ) {
                $data = $item->toArray();
                return $data['subPages'] ? $data['subPages'] : array();
            }
        }
        return array();
    }
    
    public function getAll()
    {
        if ( $this->treeArray === null ) {
            $this->treeArray = array($this->tree->toArray());
        }
        return $this->treeArray;
    }
    
    protected function getRecursivePagesIterator()
    {
        return new \RecursiveIteratorIterator($this->tree, \RecursiveIteratorIterator::LEAVES_ONLY);
    }
    
}