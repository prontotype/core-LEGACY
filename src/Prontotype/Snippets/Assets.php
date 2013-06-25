<?php

namespace Prontotype\Snippets;

Class Assets extends Base {
    
    protected $templatePath = '_system/snippets/assets';
    
    protected $configKey = 'assets';
    
    public function stylesheet($href, $media = 'all', $attrs = array(), $rawPath = false)
    {
        if ( ! $rawPath ) {
            $href = $this->app['pt.assets_helper']->getUrl($href);
        }
        
        $attrs = $this->mergeOpts(array(
            'type'  => 'text/css',
            'href'  => $href,
            'media' => $media,
        ), $attrs);
            
        return $this->renderTemplate('stylesheet.twig', array(
            'attrs'  => $attrs,
        ));
    }
    
    public function image($src, $attrs = array(), $rawPath = false)
    {
        if ( ! $rawPath ) {
            $src = $this->app['pt.assets_helper']->getUrl($src);
        }
        
        $attrs = $this->mergeOpts(array(
            'src' => $src,
        ), $attrs);
            
        return $this->renderTemplate('image.twig', array(
            'attrs'  => $attrs,
        ));
    }
    
}