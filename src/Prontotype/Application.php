<?php

namespace Prontotype;

use Silex\Provider\TwigServiceProvider;
use Silex\Application as SilexApp;
use Twig_Extension_Debug;
use Twig_Loader_String;
use Twig_Environment;
use Symfony\Component\HttpFoundation\Response;
use Silextend\Config\YamlConfig;

Class Application {
    
    static VERSION = '3.0.0';

    protected $app;
    
    protected $sharedServices = array(
        'pt.request'       => 'Request',
        'pt.pagetree'      => 'PageTree\Manager',
        'pt.store'         => 'Store',
        'pt.auth'          => 'Auth',
        'pt.notifications' => 'Notifications',
        'pt.exporter'      => 'Exporter',
        'pt.cache'         => 'Cache',
        'pt.scraper'       => 'Scraper\Scraper',
        'pt.utils'         => 'Utils',
        'pt.user_manager'  => 'UserManager',
        'pt.snippets'      => 'Snippets\Manager',
    );
    
    protected $relPaths = array(
        'config'    => '/../../config';
        'assets'    => '/../../assets';
        'templates' => '/../../templates';
    );

    public function __construct($paths)
    {
        $this->app = new SilexApp();
        
        $this->app['pt.core.paths.root'] = $paths['root'];
        $this->app['pt.core.paths.cache'] = $paths['cache'];
        $this->app['pt.core.paths.vendor'] = $paths['vendor'];
        $this->app['pt.core.paths.prototypes'] = $paths['prototypes'];
        $this->app['pt.core.paths.prototype_config'] = $paths['root'] . '/prototypes.yml';
        
        foreach($this->relPaths as $key => $path) {
            $this->app['pt.core.paths.' . $key] = __DIR__ . $path;
        }
    }
    
    public function run()
    {
        $this->doHealthCheck();
        $this->loadPrototype();
        $this->loadConfig();
        $this->registerServices();
        $this->registerMiddleware();
        $this->mountRoutes();
        $this->app->run();
    }
    
    public function doHealthCheck()
    {
        $errors = array();
        if ( ! file_exists($this->app['pt.core.paths.prototypes']) ) {
            $errors[] = 'The prototypes directory (' . $this->app['pt.core.paths.prototypes'] . ') does not exist.';
        }
        if ( ! file_exists($this->app['pt.core.paths.prototype_config']) ) {
            $errors[] = 'The required prototypes configuration file (' . $this->app['pt.core.paths.prototype_config'] . ') does not exist.';
        }
        if ( ! is_writeable($this->app['pt.core.paths.cache']) ) {
            $errors[] = 'The cache directory (' . $this->app['pt.core.paths.cache'] . ') is not writeable or does not exist.';
        }
        if ( count($errors) ) {
            throw new \Exception(implode('<br>', $errors));
        }
    }
    
    protected function loadPrototype()
    {
        $ptDefinitionsPath = $this->app['pt.core.paths.root'] . '/prototypes.yml';
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
        
        $ptDirPath = $this->app['pt.core.paths.prototypes'] . '/' . $ptConfig['prototype'];
        
        if ( ! file_exists($ptDirPath) ) {
            throw new \Exception(sprintf("Prototype directory '%s' does not exist.", $ptDirPath));
        }
        
        $app['pt.prototype.label'] = $label;
        $app['pt.prototype.environment'] = $ptConfig['environment'];
        
        $app['pt.prototype.paths.root'] = $ptDirPath;
        $app['pt.prototype.paths.templates'] = $app['pt.prototype.paths.root'] . '/templates';
        $app['pt.prototype.paths.pages'] = $app['pt.prototype.paths.templates'] . '/pages';
        $app['pt.prototype.paths.data'] = $app['pt.prototype.paths.root'] . '/data';
        $app['pt.prototype.paths.config'] = $app['pt.prototype.paths.root'] . '/config';
        $app['pt.prototype.paths.extensions'] = $app['pt.prototype.paths.root'] . '/extensions';
    }
    
    protected function loadConfig()
    {
        $config = array(
            $this->app['pt.core.paths.config'] . '/common.yml'    
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
    
    protected function registerServices()
    {
        foreach( $this->sharedServices as $serviceName => $serviceClass ) {
            $this->app[$serviceName] = $this->app->share(function() use ($this->app, $serviceClass) {
                return new $serviceClass($this->app);
            });
        }
        
        $this->app->register(new TwigServiceProvider(), array(
            'twig.path'         => array( $this->app['pt.prototype.paths.templates'], $this->app['pt.core.paths.templates'] ),
            'twig.options'      => array(
                'strict_variables'  => false,
                'cache'             => $this->app['pt.core.paths.cache'],
                'auto_reload'       => true,
                'debug'             => $this->app['pt.config']['debug'],
                'autoescape'        => false
            )
        ));

        $this->app['twig'] = $this->app->share($this->app->extend('twig', function($twig, $this->app) {
            $twig->addExtension(new Twig_Extension_Debug());
            $twig->addExtension(new Twig\HelperExtension($this->app));
            return $twig;
        }));

        $this->app['twig.stringloader'] = $this->app->share(function($this->app) {
            $loader = new Twig_Loader_String();
            return new Twig_Environment($loader);
        });
    }
    
    protected function registerMiddleware()
    {
        $this->app->before(function () use ($this->app) {
            if ( ! $this->app['pt.auth']->check() ) {
                return $this->app->redirect($this->app['pt.utils']->generateUrlPath('auth.login')); // not logged in, redirect to auth page
            }
            $this->app['pt.extensions']->before();
        });
        
        $this->app->after(function() use ($this->app) {
            $this->app['pt.extensions']->after();
        });

        $this->app->error(function(\Exception $e, $code) use ($this->app) {
    
            switch( $code ) {
                case '404':
                    $template = 'system/pages/404.twig';
                    break;
                default:
                    $template = 'system/pages/error.twig';
                    break;
            }
    
            return new Response($this->app['twig']->render($template, array(
                'message' => $e->getMessage()
            )), $code);
        });
    }
    
    protected function mountRoutes()
    {
        $this->app->mount('/' . $this->app['pt.config']['triggers']['auth'], new Controller\AuthController());
        $this->app->mount('/' . $this->app['pt.config']['triggers']['data'], new Controller\DataController());
        $this->app->mount('/' . $this->app['pt.config']['triggers']['user'], new Controller\UserController());
        $this->app->mount('/' . $this->app['pt.config']['triggers']['assets'], new Controller\AssetController());
        $this->app->mount('/' . $this->app['pt.config']['triggers']['shorturl'], new Controller\RedirectController());
        $this->app->mount('/', new Controller\MainController());
    }

}
