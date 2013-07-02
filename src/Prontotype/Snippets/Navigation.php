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
            $startPage = $this->app['pt.pages']->getByUrlPath('/' . $this->app['pt.prototype.path']);
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
            'opts' => $opts,
            'attrs' => $attrs
        ));
    }
    
}