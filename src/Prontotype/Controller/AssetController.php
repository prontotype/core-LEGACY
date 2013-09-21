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

        $controllers->get('/placeholder/{size}/{bgcolour}/{colour}', function ($size, $bgcolour, $colour) use ($app) {
            $imgStr = $app['pt.assets']->generatePlaceholderImg($size, $bgcolour, $colour, $app['request']->query->get('text'));
            $response = new Response($imgStr, 200);
            $response->headers->set('Content-Type', 'image/png');
            return $response;
        })
        ->assert('bgcolour', '^([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$')
        ->assert('colour', '^([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$')
        ->value('bgcolour', 'CCC')
        ->value('colour', '999')
        ->value('size', '300x200')
        ->bind('asset.placeholder');
            
        $controllers->get('/{asset_path}', function ($asset_path) use ($app) {
            
            try {
                $assetDetails = $app['pt.assets']->generateAsset($asset_path);                
            } catch ( \Exception $e ) {
                return $app->abort(404);
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

