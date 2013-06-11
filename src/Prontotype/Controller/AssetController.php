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
            
            try {
                $assetDetails = $app['pt.assets']->generateAsset($asset_path);                
            } catch ( \Exception $e ) {
                $app->abort(404);
            }
            
            return new Response($assetDetails['content'], 200, array(
                'Content-Type' => $assetDetails['mime']
            ));

        })
        ->assert('asset_path', '.+')
        ->bind('asset.get');
        
        return $controllers;
    }
}

