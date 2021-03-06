<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $triggers = $app['pt.config']->get('triggers');
         
        $controllers->get('/{data_path}', function ( $data_path ) use ( $app ) {
                        
            $result = $app['pt.data']->get($data_path, null, null, $app['request']->query->get('path'));
            
            if ( ! $result ) {
                $app->abort(404);
            } else {
                return $app->json($result);
            }
            
        })
        ->assert('data_path', '.+')
        ->value('data_path', '')
        ->bind('data.view.json');
        
        return $controllers;
    }
}
