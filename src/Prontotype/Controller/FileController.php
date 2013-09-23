<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController implements ControllerProviderInterface {
    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
            
        $controllers->get('/{file_path}', function ($file_path) use ($app) {
            
            $path = $app['pt.prototype.paths.files'] . '/' . $file_path;
            if ( ! file_exists($path) ) {
                $app->abort(404);
            }
            
            return $app->sendFile($path);
        })
        ->assert('file_path', '.+')
        ->bind('files.get');
        
        return $controllers;
    }
}

