<?php

namespace Prontotype\Service;

use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Application as SilexApp;
use Silex\ServiceProviderInterface;

use Twig_Extension_Debug;
use Twig_Loader_String;
use Twig_Environment;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

use Prontotype\Cache;
use Prontotype\Twig\HelperExtension;
use Prontotype\Twig\GeshiExtension;
use Prontotype\Data\Manager as DataManager;
use Prontotype\Data\JsonParser;
use Prontotype\Data\YamlParser;
use Prontotype\Data\XmlParser;
use Prontotype\Data\CsvParser;
use Prontotype\Data\MarkdownParser;
use Prontotype\Store\Manager as StoreManager;
use Prontotype\Extension\Manager as ExtensionManager;
use Prontotype\Assets\Manager as AssetManager;
use Prontotype\Config as ConfigManager;
use Prontotype\Assets\LessProcessor;
use Prontotype\Assets\ScssProcessor;

Class Prontotype implements ServiceProviderInterface {
    
    protected $sharedServices = array();
    
    public function __construct($sharedServices)
    {
        $this->sharedServices = $sharedServices;
    }
          
    public function register(SilexApp $app)
    {
        $app['pt.config'] = $app->share(function($app) {
            return new ConfigManager($app, array(
                $app['pt.prototype.paths.config'],
                $app['pt.install.paths.config'],    
            ), $app['pt.app.paths.config'], $app['pt.prototype.environment']);
        });
        
        date_default_timezone_set($app['pt.config']->get('timezone'));
    }
    
    protected function registerServices($app)
    {   
        $app->register(new \Silex\Provider\SessionServiceProvider(), array(
            'session.storage.options' => array(
                'name' => $app['pt.config']->get('storage.prefix') . 'SESS',
                'cookie_lifetime' => $app['pt.config']->get('storage.lifetime')
            )
        ));
        
        foreach( $this->sharedServices as $serviceName => $serviceClass ) {
            $app[$serviceName] = $app->share(function() use ($app, $serviceClass) {
                return new $serviceClass($app);
            });
        }
        
        $app['pt.cache'] = $app->share(function($app) {
            return new Cache($app, array(
                Cache::CACHE_TYPE_ASSETS => array(
                    'expiry' => 60 * 60 * 24 * 365,
                    'path' => $app['pt.prototype.paths.cache.assets'],
                ),
                Cache::CACHE_TYPE_DATA => array(
                    'expiry' => 60 * 60 * 24 * 365,
                    'path' => $app['pt.prototype.paths.cache.data'],
                ),
                Cache::CACHE_TYPE_REQUESTS => array(
                    'expiry' => $app['pt.config']->get('cache.requests.expiry'),
                    'path' => $app['pt.prototype.paths.cache.requests'],
                ),
                Cache::CACHE_TYPE_EXPORTS => array(
                    'expiry' => 60 * 60 * 24 * 365,
                    'path' => $app['pt.prototype.paths.cache.exports'],
                )
            ));
        });
        
        $app['pt.data'] = $app->share(function($app) {
            return new DataManager($app, array(
                new JsonParser($app),
                new YamlParser($app),
                new XmlParser($app),
                new CsvParser($app),
                new MarkdownParser($app)
            ));
        });
        
        $app['pt.store'] = $app->share(function($app) {
            return new StoreManager($app, $app['pt.config']->get('storage.adapter'));
        });
        
        $app['pt.assets'] = $app->share(function($app) {
            return new AssetManager($app, array(
                new LessProcessor($app),
                new ScssProcessor($app),
            ), array(
                $app['pt.prototype.paths.assets'],
            ), $app['pt.app.paths.assets']);
        });

        $app['pt.extensions'] = $app->share(function($app) {
            return new ExtensionManager($app, $app['pt.prototype.paths.extensions']);
        });
        
        $app->register(new UrlGeneratorServiceProvider());
        
        $app->register(new TwigServiceProvider(), array(
            'twig.path'         => array( $app['pt.prototype.paths.templates']),
            'twig.options'      => array(
                'strict_variables'  => false,
                'cache'             => $app['pt.prototype.paths.cache.templates'],
                'auto_reload'       => true,
                'debug'             => $app['pt.config']->get('debug'),
                'autoescape'        => false
            )
        ));

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            if ( $app['pt.config']->get('debug') ) {
                $twig->addExtension(new Twig_Extension_Debug());  
            } 
            $twig->addExtension(new HelperExtension($app));
            $twig->addExtension(new GeshiExtension());
            return $twig;
        }));
        
        $app['twig.stringloader'] = $app->share(function($app) {
            $loader = new Twig_Loader_String();
            return new Twig_Environment($loader);
        });
        
        $app['twig.dataloader'] = $app->share(function ($app) {
            
            $paths = array();
            foreach( array(
                $app['pt.prototype.paths.data'],
                $app['pt.app.paths.data']
            ) as $path ) {
                if ( is_dir($path) ) {
                    $paths[] = $path;
                }
            }
            $twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem($paths),
                array(
                    'strict_variables'  => false,
                    'cache'             => $app['pt.prototype.paths.cache.data'],
                    'auto_reload'       => true,
                    'debug'             => $app['pt.config']->get('debug'),
                    'autoescape'        => false
                )
            );
            $twig->addGlobal('app', $app);
            $twig->addExtension(new \Silex\Provider\TwigCoreExtension());
            if ( $app['pt.config']->get('debug') ) {
                $twig->addExtension(new Twig_Extension_Debug());  
            }
            return $twig;
        });
        
        $app->register(new \SilexMarkdown\MarkdownExtension(), array(
            'markdown.features' => array(
                'entities' => true,
            )
        ));
        
        $app['pt.extensions']->boot();
        
        $app['twig.loader.filesystem']->addPath($app['pt.app.paths.templates']);
    }
    
    protected function bindMiddleware($app)
    {
        $app->before(function () use ($app) {
            if ( ! $app['pt.auth']->check() ) {
                return $app->redirect($app['pt.utils']->generateUrlPath('auth.login')); // not logged in, redirect to auth page
            }
            $app['pt.extensions']->before();
        });
        
        $app->after(function() use ($app) {
            $app['pt.extensions']->after();
        });

        $app->error(function(\Exception $e, $code) use ($app) {
            
            switch( $code ) {
                case '404':
                    $template = '_system/404.twig';
                    break;
                default:
                    $template = '_system/error.twig';
                    break;
            }
            
            return new Response($app['twig']->render($template, array(
                'message' => $e->getMessage()
            )), $code);
        });
    }
    
    protected function mountRoutes($app)
    {
        $root = $app['pt.prototype.path'] . '/';
        if ($app['pt.config']->get('triggers.auth')) {
            $app->mount($root . $app['pt.config']->get('triggers.auth'), new \Prontotype\Controller\AuthController());  
        }
        if ($app['pt.config']->get('triggers.data')) {
            $app->mount($root . $app['pt.config']->get('triggers.data'), new \Prontotype\Controller\DataController());
        }
        if ($app['pt.config']->get('triggers.user')) {
            $app->mount($root . $app['pt.config']->get('triggers.user'), new \Prontotype\Controller\UserController());  
        } 
        if ($app['pt.config']->get('triggers.assets')) {
            $app->mount($root . $app['pt.config']->get('triggers.assets'), new \Prontotype\Controller\AssetController());  
        } 
        if ($app['pt.config']->get('triggers.shorturl')) {
            $app->mount($root . $app['pt.config']->get('triggers.shorturl'), new \Prontotype\Controller\RedirectController());   
        }
        if ($app['pt.config']->get('triggers.tools')) {
            $app->mount($root . $app['pt.config']->get('triggers.tools'), new \Prontotype\Controller\ToolsController());   
        }
        $app->mount('/', new \Prontotype\Controller\MainController());
    }
    
    public function boot(SilexApp $app)
    {
        $this->registerServices($app);
        $this->bindMiddleware($app);
        $this->mountRoutes($app);
    }
    
}
