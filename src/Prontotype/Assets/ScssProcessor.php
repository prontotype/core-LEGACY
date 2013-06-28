<?php

namespace Prontotype\Assets;

Class ScssProcessor extends Processor {
    
    public function getHandledExtensions()
    {
        return array(
            'scss'
        );
    }
    
    public function process($content, $loadPaths = array())
    {
        $scss = new \scssc();
        $scss->setImportPaths($loadPaths);
        return $scss->compile($content);
    }
    
}
