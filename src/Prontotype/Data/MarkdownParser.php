<?php

namespace Prontotype\Data;

Class MarkdownParser extends Parser {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getHandledExtensions()
    {
        return array(
            'md',
            'markdown',
            'mdown'
        );
    }
    
    public function parse($content)
    {
        if ( ! is_string($content) ) {
            throw new \Exception('Markdown data format error');
        }
        try {
           return $this->app['markdown']->transform($content);
        } catch( \Exception $e ) {
            throw new \Exception('Markdown data format error');
        }
    }
    
}
