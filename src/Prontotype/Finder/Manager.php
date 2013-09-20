<?php

namespace Prontotype\Finder;

Class Manager {

    protected $app;

    public function __construct( $app )
    {
        $this->app = $app;
    }
    
    public function fetch()
    {
        $finder = new Finder();
        $finder->setRoot($this->app['pt.prototype.paths.root']);
        $finder->ignoreUnreadableDirs();
        return $finder;
    }
    
}
