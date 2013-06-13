<?php

namespace Prontotype\Snippets;

Class Assets extends Base {
    
    protected $templatePath = 'system/snippets/assets';
    
    protected $configKey = 'assets';
    
    public function stylesheet($href, $media = 'all', $attrs = array(), $appendAssetPath = true)
    {
        if ($appendAssetPath) {
            $href = $this->prefixPath($href);
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
    
    public function prefixPath($path)
    {        
        $path = '/' . $this->app['pt.config']['triggers']['assets'] . '/' . trim($path, '/');
        if ( ! $this->app['pt.config']['clean_urls'] ) {
            $path = '/index.php' . $path;
        }
        return $path;
    }
    
}