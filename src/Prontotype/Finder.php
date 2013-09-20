<?php

namespace Prontotype;

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
        $this->dirs = array();   
        if ( $this->root) {
            $path = $this->root . '/' . ltrim($path,'/');
        }
        return parent::in($path);
    }
    
    public function andIn($path)
    {
        $this->dirs = array();   
        if ( $this->root) {
            $path = $this->root . '/' . ltrim($path,'/');
        }
        return parent::in($path);
    }
    
}
