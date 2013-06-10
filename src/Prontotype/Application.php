<?php

namespace Prontotype;

use Silex\Provider\TwigServiceProvider;
use Silex\Application as SilexApp;
use Silex\ServiceProviderInterface;
use Twig_Extension_Debug;
use Twig_Loader_String;
use Twig_Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Silextend\Config\YamlConfig;


Class Application {
    
    const VERSION = '3.0.0';

    protected $app;
    
    protected $paths;
    
    protected $sharedServices = array(
        'pt.request'       => '\Prontotype\Request',
        'pt.pagetree'      => '\Prontotype\PageTree\Manager',
        'pt.store'         => '\Prontotype\Store',
        'pt.auth'          => '\Prontotype\Auth',
        'pt.notifications' => '\Prontotype\Notifications',
        'pt.exporter'      => '\Prontotype\Exporter',
        'pt.cache'         => '\Prontotype\Cache',
        'pt.scraper'       => '\Prontotype\Scraper\Scraper',
        'pt.utils'         => '\Prontotype\Utils',
        'pt.user_manager'  => '\Prontotype\UserManager',
        'pt.snippets'      => '\Prontotype\Snippets\Manager',
    );
    
    protected $relPaths = array(
        'config'    => '/../../config',
        'assets'    => '/../../assets',
        'templates' => '/../../views',
    );
    
	public function __construct($paths)
    {
        $this->paths = $paths;
        $this->app = $app = new SilexApp();
        
        $this->app['pt.core.paths.root'] = $this->paths['root'];
        $this->app['pt.core.paths.cache'] = $this->paths['cache'];
        $this->app['pt.core.paths.vendor'] = $this->paths['vendor'];
        $this->app['pt.core.paths.prototypes'] = $this->paths['prototypes'];
        $this->app['pt.core.paths.prototype_config'] = $this->paths['root'] . '/prototypes.yml';
        
        foreach($this->relPaths as $key => $path) {
            $this->app['pt.core.paths.' . $key] = __DIR__ . $path;
        }
        
        $this->doHealthCheck();
    }
    
    public function run()
    {
        $app = $this->app;
        
        $this->app->register(new Service\Prontotype($this->sharedServices));
        
        // redirect if there is a trailing slash
        $this->app->before(function() use ($app){
            if ( $app['pt.request']->urlPathHasTrailingSlash() ) {
                return $app->redirect($app['pt.request']->getRawUrlPath());
                exit();
            }
        });
        
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
    
}
