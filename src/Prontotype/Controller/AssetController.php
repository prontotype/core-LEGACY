<?php

namespace Prontotype\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetController implements ControllerProviderInterface {
    
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/{asset_path}', function ($asset_path) use ($app) {
            
            $cachePath = $app['pt.assets']->getProcessedAssetPath($asset_path);
            
            return $app->sendFile($cachePath);

        })
        ->assert('asset_path', '.+')
        ->bind('asset.get');
        
        return $controllers;
    }
}

