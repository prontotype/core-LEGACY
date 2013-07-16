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
            'levelClassPrefix' => 'level-',
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
            'type'   => 'ul',
            'offset' => 0,
            'levelClassPrefix' => 'level-',
            'limit'  => null,
            'append' => array(),
            'prepend' => array()
        ), $opts);
        
        $pages = array(
            $this->app['pt.pages']->getByUrlPath('/')
        );
        
        $urlPath = $this->app['pt.request']->getUrlPath();
        if ( !empty($this->app['pt.prototype.path']) ) {
            $urlPath = preg_replace("/^(\\" . $this->app['pt.prototype.path'] . ")/", '', $urlPath);            
        }
        
        if ( ! empty($urlPath) ) {
            $urlParts = explode('/', trim($urlPath,'/') );
            $builtPath = '/' . $this->app['pt.prototype.path'];
        
            if ( count($urlParts) ) {
                foreach( $urlParts as $urlPart ) {
                    $builtPath .= '/' . $urlPart;
                    if ( $pageObj = $this->app['pt.pages']->getByUrlPath($builtPath) ) {
                        $pages[] = $pageObj;
                    } elseif ( $dirObj = $this->app['pt.pages']->getDirectoryByUrlPath($builtPath) ) {
                        $pages[] = $dirObj;
                    }
                }
            }   
        }
        
        if ( $opts['offset'] || $opts['limit'] ) {
            $pages = array_slice($pages, $opts['offset'], $opts['limit']);
        }
        
        if ( count($opts['append']) ) {
            foreach( $opts['append'] as $title => $url ) {
                array_push($pages, array(
                    'url' => $url,
                    'title' => $title
                ));
            }
        }
        
        if ( count($opts['prepend']) ) {
            foreach( $opts['prepend'] as $title => $url ) {
                array_unshift($pages, array(
                    'url' => $url,
                    'title' => $title
                ));
            }
        }
        
        return $this->renderTemplate('breadcrumb.twig', array(
            'pages' => $pages,
            'opts'  => $opts,
            'attrs' => $attrs
        ));
    }
    
    public function logout($text = 'Logout', $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'href' => $this->app['pt.utils']->generateUrlPath('user.logout')
        ), $attrs);
        
        return $this->renderTemplate('link.twig', array(
            'text'  => $text,
            'attrs' => $attrs
        ));
    }
    
}