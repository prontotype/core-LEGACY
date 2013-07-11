<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MainController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->match('/{route}', function ( $route ) use ( $app ) {
            
            if ( ! $page = $app['pt.pages']->getByRoute($route) ) {
                $app->abort(404);
            }
                            
            try {
                return $app['twig']->render($page->getTemplatePath(), array());
            } catch ( \Exception $e ) {
          
                if ( $e instanceof \Twig_Error and $e->getPrevious() instanceof HttpException ) {
                    return $app->abort($e->getPrevious()->getStatusCode());
                }
                
                return $app['twig']->render('_system/error.twig', array(
                    'message'=>$e->getMessage()
                ));
            }
        })
        ->method('GET|POST')
        ->assert('route', '.+')
        ->value('route', '')
        ->bind('catchall');
    
        return $controllers;
    }
}
