<?php

namespace Prontotype\Snippets;

Class Navigation extends Base {
    
    protected $templatePath = '_system/snippets/navigation';
    
    protected $configKey = 'navigation';

    public function pageTree($startPage = null, $opts = array(), $attrs = array(), $level = 0)
    {
        if ( ! $opts ) {
            $opts = array();
        }
        
        $opts = $this->mergeOpts(array(
            'includeParent' => true,
            'type'         => 'ul',
            'maxDepth'     => null,
            'currentClass' => 'is-current',
            'parentClass'  => 'is-parent',
        ), $opts);
        
        if ( $startPage === null ) {
            // get the homepage
            $startPage = $this->app['pt.pages']->getByUrlPath('/');
        } elseif ( is_string($startPage) ) {
            // start page is an ID?
            if ( ! $startPage = $this->app['pt.pages']->getById($startPage) ) {
                return null;
            }
        }
        
        if ( ! $startPage->hasSubPages() && ! $opts['includeParent'] ) {
            return null;
        }
        if ( $opts['includeParent'] ) {
            $pages = array($startPage);
        } else {
            $pages = $startPage->getSubPages();
        }
    
        return $this->renderTemplate('page-tree.twig', array(
            'pages' => $pages,
            'level' => $level,
            'opts'  => $opts,
            'attrs' => $attrs
        ));
    }
    
    public function breadcrumb($opts = array(), $attrs = array())
    {
        if ( ! $opts ) {
            $opts = array();
        }
        
        $opts = $this->mergeOpts(array(
            'type' => 'ul',
        ), $opts);
        
        $pages = array(
            $this->app['pt.pages']->getByUrlPath('/')
        );
        
        $urlPath = $this->app['pt.request']->getUrlPath();
        if ( !empty($this->app['pt.prototype.path']) ) {
            $urlPath = preg_replace("/^(\\" . $this->app['pt.prototype.path'] . ")/", '', $urlPath);            
        }

        $urlParts = explode('/', trim($urlPath,'/') );
        $builtPath = '/' . $this->app['pt.prototype.path'];
        if ( count($urlParts) ) {
            foreach( $urlParts as $urlPart ) {
                $builtPath .= '/' . $urlPart;
                $pages[] = $this->app['pt.pages']->getByUrlPath($builtPath);
            }
        }
        
        return $this->renderTemplate('breadcrumb.twig', array(
            'pages' => $pages,
            'opts'  => $opts,
            'attrs' => $attrs
        ));
    }
    
}