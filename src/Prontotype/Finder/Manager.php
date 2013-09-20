<?php

namespace Prontotype\Finder;

Class Manager {

    protected $app;

    public function __construct( $app )
    {
        $this->app = $app;
    }
    
    protected function fetch()
    {
        $finder = new Finder();
        return $finder;
    }
    
}
