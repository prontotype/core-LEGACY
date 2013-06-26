<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController implements ControllerProviderInterface {
    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/login', function() use ($app) {
            
            if ( $app['pt.auth']->isAuthed() ) {
                return $app->redirect($app['pt.utils']->generateUrlPath('home'));
            }
            
            return $app['twig']->render('_system/authenticate.twig');
            
        })->bind('auth.login');
        
        
        $controllers->post('/login', function() use ($app) {
            
            if ( $app['pt.auth']->attemptLogin($app['request']->get('password')) ) {
                return $app->redirect($app['pt.utils']->generateUrlPath('home'));
            } else {
                return $app->redirect($app['pt.utils']->generateUrlPath('auth.login'));
            }
            
        })->bind('auth.check');
        
        
        $controllers->get('/logout', function($result) use ($app) {
            
            $app['pt.auth']->logout();
            return $app->redirect($app['pt.utils']->generateUrlPath('auth.login'));

        })
        ->value('result', null)
        ->bind('auth.logout');
        
        
        return $controllers;
    }
}

