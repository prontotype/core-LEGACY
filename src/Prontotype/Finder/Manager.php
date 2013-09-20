<?php

namespace Prontotype\Finder;

Class Manager {

    protected $app;

    public function __construct( $app )
    {
        $this->app = $app;
    }
    
    public function in($location)
    {
        $finder = new Finder();
        $finder->setRoot($this->app['pt.prototype.paths.root']);
        $finder->ignoreUnreadableDirs();
        $finder->in($location);
        return $finder;
    }
    
}
