<?php

namespace Prontotype\Assets;

Class Processor {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getHandledExtensions()
    {
        return array();
    }
    
    public function process($content)
    {
        
    }
    
}
