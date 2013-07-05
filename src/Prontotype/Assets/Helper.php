<?php

namespace Prontotype\Assets;

use Prontotype\Cache;

use Symfony\Component\HttpFoundation\File\File;

Class Helper {
    
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getUrl($path)
    {
        $path = str_replace('/index.php', '', $this->getUrlPath($path));
        return $this->app['pt.request']->getUriForPath($path);
    }
    
    public function getUrlPath($path)
    {
        $root = $this->app['pt.prototype.path'] . '/';     
        $path = $root . $this->app['pt.config']->get('triggers.assets') . '/' . trim($path, '/');
        if ( ! $this->app['pt.env.clean_urls'] ) {
            $path = '/index.php' . $path;
        }   
        return $path;
    }
    
}