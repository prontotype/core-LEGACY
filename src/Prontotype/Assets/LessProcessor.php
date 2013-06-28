<?php

namespace Prontotype\Assets;

Class LessProcessor extends Processor {
    
    public function getHandledExtensions()
    {
        return array(
            'less'
        );
    }
    
    public function process($content, $loadPaths = array())
    {
        $less = new \lessc;
        $less->setImportDir($loadPaths);
        return $less->compile($content);
    }
    
}
