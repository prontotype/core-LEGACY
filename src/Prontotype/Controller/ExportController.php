<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController implements ControllerProviderInterface {
    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/run', function () use ($app) {
            
            $details = $app['pt.exporter']->run();
            if ( $details ) {
                return $app->sendFile($details['path'])->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $details['filename']);    
            } else {
                $app->abort(500);
            }
            
        })
        ->bind('export.run');
        
        $controllers->get('/clear', function () use ($app) {
            
            $app['pt.exporter']->clear();
            
        })
        ->bind('export.clear');
        
        $controllers->get('/list', function () use ($app) {
                        
            // echo '<pre>';
 //            print_r($app['pt.exporter']->listContents());
 //            echo '</pre>';

        })
        ->bind('export.list');
        
        return $controllers;
    }
}

