<?php

namespace Prontotype\Snippets;

Class Navigation extends Base {
    
    protected $templatePath = '_system/snippets/navigation';
    
    protected $configKey = 'navigation';

    public function pageTree($opts = array(), $parentPage = null, $includeParent = true, $level = 0)
    {
        $opts = $this->mergeOpts(array(
            'type'         => 'ul',
            'attrs'        => array(),
            'maxDepth'     => null,
            'currentClass' => 'is-current',
            'parentClass'  => 'is-parent',
        ), $opts);
        
        if ( $parentPage === null ) {
            // get the homepage
            $parentPage = $this->app['pt.pages']->getByUrlPath('/' . $this->app['pt.prototype.path']);
        }
        
        if ( ! $parentPage->hasSubPages() && ! $includeParent ) {
            return null;
        }
        if ( $includeParent ) {
            $pages = array($parentPage);
        } else {
            $pages = $parentPage->getSubPages();
        }
    
        return $this->renderTemplate('page-tree.twig', array(
            'pages' => $pages,
            'level' => $level,
            'opts' => $opts
        ));
    }
    
}