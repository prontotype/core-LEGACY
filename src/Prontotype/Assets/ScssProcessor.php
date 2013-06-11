<?php

namespace Prontotype\Assets;

Class ScssProcessor extends Processor {
    
    public function getHandledExtensions()
    {
        return array(
            'scss'
        );
    }
    
    public function process($content)
    {
        $scss = new \scssc();
        return $scss->compile($content);
    }
    
}
