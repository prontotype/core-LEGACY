<?php

namespace Prontotype\Scraper;

class Scraper {

	protected $app;
	protected $client;

    public function __construct( $app )
    {
        $this->app = $app;
    }

    public function get( $uri )
    {
        $response = $this->app['pt.utils']->fetchFromUrl($uri);
        $html = $response['body'];
        $dom = new \DOMDocument('1.0', 'utf8');
        $dom->validateOnParse = false;

        $current = libxml_use_internal_errors(true);
        $dom = new Scrap($html, $uri);
        libxml_use_internal_errors($current);

        return $dom;
    }
    
}
