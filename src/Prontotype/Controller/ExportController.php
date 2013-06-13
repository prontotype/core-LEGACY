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
            return $app->redirect($app['pt.utils']->generateUrlPath('export.tools'));
            
        })
        ->bind('export.run');
        
        
        $controllers->get('/current', function () use ($app) {
            
            $details = $app['pt.exporter']->run();
            return $app->redirect($app['pt.utils']->generateUrlPath('export.download', array(
                'tag' => $details['tag']
            )));
            
        })
        ->bind('export.current');
        
        
        $controllers->get('/download/{tag}', function ($tag) use ($app) {
            
            if ( ! $tag ) {
                $app->abort(404);
            }
            
            if ( $details = $app['pt.exporter']->getExportDetails($tag) ) {
                return $app->sendFile($details['path'])->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $details['filename']);  
            } else {
                $app->abort(404);
            }
            
        })
        ->value('tag', null)
        ->bind('export.download');
        
        
        $controllers->get('/clear', function () use ($app) {
            
            $app['pt.exporter']->clear();
            return $app->redirect($app['pt.utils']->generateUrlPath('export.tools'));
            
        })
        ->bind('export.clear');
        
        
        $controllers->get('/tools', function () use ($app) {
            
            return $app['twig']->render('system/pages/exports/tools.twig', array(
                'exports' => $app['pt.exporter']->listContents()
            ));

        })
        ->bind('export.tools');
        
        
        return $controllers;
    }
}

