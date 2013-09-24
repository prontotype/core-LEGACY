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
        $path = $this->app['pt.assets']->aliasFilePath($path);
        $root = $this->app['pt.prototype.path'] . '/';  
        $path = $root . $this->app['pt.config']->get('triggers.assets') . '/' . trim($path, '/');
        if ( ! $this->app['pt.env.clean_urls'] ) {
            $path = '/index.php' . $path;
        }   
        return $path;
    }
    
    public function getFileUrl($path)
    {
        $path = str_replace('/index.php', '', $this->getFileUrlPath($path));
        return $this->app['pt.request']->getUriForPath($path);
    }
    
    public function getFileUrlPath($path)
    {
        $root = $this->app['pt.prototype.paths.files'] . '/';  
        $path = $this->app['pt.config']->get('triggers.files') . '/' . trim($path, '/');
        if ( ! $this->app['pt.env.clean_urls'] ) {
            $path = '/index.php' . $path;
        }   
        return $path;
    }
    
}