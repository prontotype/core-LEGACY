<?php

namespace Prontotype\Finder;

Class Finder extends \Symfony\Component\Finder\Finder {
    
    protected $root = null;
    
    public function setRoot($root)
    {
        if ( $this->root === null ) {
            $this->root = $root; 
        }
    }

    public function in($path)
    {   
        if ( $this->root) {
            $path = $this->root . '/' . ltrim($path,'/');
        }
        return parent::in($path);
    }
    
}
