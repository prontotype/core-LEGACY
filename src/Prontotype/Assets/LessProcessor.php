<?php

namespace Prontotype\Assets;

Class LessProcessor extends Processor {
    
    public function getHandledExtensions()
    {
        return array(
            'less'
        );
    }
    
    public function process($content)
    {
        $less = new \lessc;
        return $less->compile($content);
    }
    
}
