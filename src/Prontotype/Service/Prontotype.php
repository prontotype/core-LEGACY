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
use Prontotype\Twig\HelperExtension as TwigHelperExtension;
use Prontotype\Data\Manager as DataManager;
use Prontotype\Data\JsonParser;
use Prontotype\Data\YamlParser;
use Prontotype\Data\XmlParser;
use Prontotype\Data\CsvParser;
use Prontotype\Extension\Manager as ExtensionManager;

use Silextend\Config\YamlConfig;

Class Prontotype implements ServiceProviderInterface {
    
    protected $sharedServices = array();
    
    public function __construct( $sharedServices )
    {
        $this->sharedServices = $sharedServices;
    }
          
    public function register( SilexApp $app )
    {
        // load up prototype
        $this->loadPrototype($app);
        $this->loadConfig($app);
        $this->registerServices($app);
        $this->bindMiddleware($app);
        $this->mountRoutes($app);
    }
    
    protected function loadPrototype($app)
    {
        $ptDefinitionsPath = $app['pt.core.paths.root'] . '/prototypes.yml';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $ptConfig = null;
        
        $ptDefinitions = Yaml::parse($ptDefinitionsPath);        
        if (null === $ptDefinitions) {
            throw new \Exception(sprintf("The config file '%s' appears to be invalid YAML.", $filename));
        }
        
        foreach( $ptDefinitions as $label => $definition ) {
            $matches = is_array($definition['matches']) ? $definition['matches'] : array($definition['matches']);
            $regexp = '/^(';
            $regexp .= implode('|', array_map(function($value){
                return str_replace(array('.','*'), array('\.','(.*)'), $value);
            }, $matches));
            $regexp .= ')/';
            if ( preg_match($regexp, $host, $matches) ) {
                $replacements = array_slice($matches, 2);                
                $ptConfig = $definition;
                $replacementTokens = array();
                for ( $j = 0; $j < count($replacements); $j++ ) {
                    $replacementTokens['$' . ($j+1)] = $replacements[$j];
                }
                $ptLabel = $label;
                $ptConfig['prototype'] = str_replace(array_keys($replacementTokens), array_values($replacementTokens), $ptConfig['prototype']);
                break;
            }
        }
        
        if ( ! $ptConfig ) {
            throw new \Exception(sprintf("Could not find matching prototype definition for '%s'.", $host));
        }
        
        $ptDirPath = $app['pt.core.paths.prototypes'] . '/' . $ptConfig['prototype'];
        
        if ( ! file_exists($ptDirPath) ) {
            throw new \Exception(sprintf("Prototype directory '%s' does not exist.", $ptDirPath));
        }
        
        $app['pt.prototype.label'] = $label;
        $app['pt.prototype.folder'] = $ptConfig['prototype'];
        $app['pt.prototype.environment'] = $ptConfig['environment'];
        
        $app['pt.prototype.paths.root'] = $ptDirPath;
        $app['pt.prototype.paths.templates'] = $app['pt.prototype.paths.root'] . '/templates';
        $app['pt.prototype.paths.pages'] = $app['pt.prototype.paths.templates'] . '/pages';
        $app['pt.prototype.paths.data'] = $app['pt.prototype.paths.root'] . '/data';
        $app['pt.prototype.paths.config'] = $app['pt.prototype.paths.root'] . '/config';
        $app['pt.prototype.paths.extensions'] = $app['pt.prototype.paths.root'] . '/extensions';
        
        $app['pt.prototype.paths.cache.root'] = $app['pt.core.paths.cache.root'] . '/' . $app['pt.prototype.folder'];
        $app['pt.prototype.paths.cache.templates'] = $app['pt.core.paths.cache.root'] . '/' . $app['pt.prototype.folder'] .'/views';
        $app['pt.prototype.paths.cache.assets'] = $app['pt.core.paths.cache.root'] . '/' . $app['pt.prototype.folder'] .'/assets';
        $app['pt.prototype.paths.cache.data'] = $app['pt.core.paths.cache.root'] . '/' . $app['pt.prototype.folder'] .'/data';
        $app['pt.prototype.paths.cache.requests'] = $app['pt.core.paths.cache.root'] . '/' . $app['pt.prototype.folder'] .'/requests';
    }
    
    protected function loadConfig($app)
    {
        $config = array(
            $app['pt.core.paths.config'] . '/common.yml'    
        );
        $commonConfig = $app['pt.prototype.paths.config'] . '/common.yml';
        if ( file_exists($commonConfig) ) {
            $config[] = $commonConfig;
        }
        $envConfig = $app['pt.prototype.paths.config'] . '/' . $app['pt.prototype.environment'] . '.yml';
        if ( file_exists( $envConfig ) ) {
            $config[] = $envConfig;
        }
        $app->register(new YamlConfig($config));
        $app['pt.config'] = $app['config'];
    }
    
    protected function registerServices($app)
    {
        foreach( $this->sharedServices as $serviceName => $serviceClass ) {
            $app[$serviceName] = $app->share(function() use ($app, $serviceClass) {
                return new $serviceClass($app);
            });
        }
        
        $app['pt.extensions'] = $app->share(function($app) {
            $ext = new ExtensionManager($app);
            $ext->load($app['pt.config']['extensions']);
            return $ext;
        });
        
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
                    'expiry' => $app['pt.config']['cache']['requests']['expiry'],
                    'path' => $app['pt.prototype.paths.cache.data'],
                )
            ));
        });
        
        $app['pt.data'] = $app->share(function($app) {
            return new DataManager($app, array(
                new JsonParser($app),
                new YamlParser($app),
                new XmlParser($app),
                new CsvParser($app)
            ));
        });
        
        $app->register(new SessionServiceProvider());
        $app->register(new UrlGeneratorServiceProvider());
    
        $app->register(new TwigServiceProvider(), array(
            'twig.path'         => array( $app['pt.prototype.paths.templates'], $app['pt.core.paths.templates'] ),
            'twig.options'      => array(
                'strict_variables'  => false,
                'cache'             => $app['pt.prototype.paths.cache.templates'],
                'auto_reload'       => true,
                'debug'             => $app['pt.config']['debug'],
                'autoescape'        => false
            )
        ));

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            $twig->addExtension(new Twig_Extension_Debug());
            $twig->addExtension(new TwigHelperExtension($app));
            return $twig;
        }));

        $app['twig.stringloader'] = $app->share(function($app) {
            $loader = new Twig_Loader_String();
            return new Twig_Environment($loader);
        });
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
                    $template = 'system/pages/404.twig';
                    break;
                default:
                    $template = 'system/pages/error.twig';
                    break;
            }
    
            return new Response($app['twig']->render($template, array(
                'message' => $e->getMessage()
            )), $code);
        });
    }
    
    protected function mountRoutes($app)
    {
        $app->mount('/' . $app['pt.config']['triggers']['auth'], new \Prontotype\Controller\AuthController());
        $app->mount('/' . $app['pt.config']['triggers']['data'], new \Prontotype\Controller\DataController());
        $app->mount('/' . $app['pt.config']['triggers']['user'], new \Prontotype\Controller\UserController());
        $app->mount('/' . $app['pt.config']['triggers']['assets'], new \Prontotype\Controller\AssetController());
        $app->mount('/' . $app['pt.config']['triggers']['shorturl'], new \Prontotype\Controller\RedirectController());
        $app->mount('/', new \Prontotype\Controller\MainController());
    }
    
    public function boot ( SilexApp $app ) {}
    
}
