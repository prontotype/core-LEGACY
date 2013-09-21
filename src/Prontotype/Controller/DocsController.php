<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $triggers = $app['pt.config']->get('triggers');
         
        $controllers->get('/{docs_path}', function ( $docs_path ) use ( $app ) {
            
            $dataPath = '_system/docs/' . (empty($docs_path) ? 'index.md' : $docs_path . '.md');
            
            return $app['twig']->render('_system/docs/index.html', array(
                'dataPath' => $dataPath
            ));
            
        })
        ->assert('docs_path', '.+')
        ->value('docs_path', '')
        ->bind('docs.view');
        
        return $controllers;
    }
}
