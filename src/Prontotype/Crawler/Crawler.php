<?php

namespace Prontotype\Crawler;

Class Crawler extends \PHPCrawler {
    
    function handleDocumentInfo(\PHPCrawlerDocumentInfo $PageInfo) 
    { 
        echo $PageInfo->url."<br>"; 
        echo $PageInfo->content_tmp_file."<br><br>"; 
    } 
    
}
