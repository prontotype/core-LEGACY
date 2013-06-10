<?php

namespace Prontotype\Assets;

Class Manager {
    
    protected $app;

    public function __construct( $app )
    {
        $this->app = $app;
    }
    
    public function getProcessedAssetPath($assetPath)
    {
        
        
        return $this->app['pt.prototype.paths.cache.assets'] . '/' .$assetPath;
    }

}