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
        'pt.scraper'       => '\Prontotype\Scraper\Scraper',
        'pt.utils'         => '\Prontotype\Utils',
        'pt.user_manager'  => '\Prontotype\UserManager',
        'pt.snippets'      => '\Prontotype\Snippets\Manager',
        'pt.assets_helper' => '\Prontotype\Assets\Helper',
    );
    
    protected $srcPaths = array(
        'root'       => '/../../',
        'data'       => '/../../app/data',
        'config'     => '/../../app/config',
        'assets'     => '/../../app/assets',
        'templates'  => '/../../app/templates',
        'prototypes' => '/../../app/prototypes',
    );
    
	public function __construct($paths)
    {
        $this->paths = $paths;
        $this->app = $app = new SilexApp();
        
        $this->app['pt.install.paths.root'] = $this->paths['root'];
        $this->app['pt.install.paths.cache.root'] = $this->paths['cache'];
        $this->app['pt.install.paths.vendor'] = $this->paths['vendor'];
        $this->app['pt.install.paths.prototypes'] = $this->paths['prototypes'];
        $this->app['pt.install.paths.config'] = $this->paths['config'];
        
        foreach($this->srcPaths as $key => $path) {
            $this->app['pt.app.paths.' . $key] = realpath(__DIR__ . $path);
        }
        
        $this->app['pt.env.clean_urls'] = file_exists($this->app['pt.install.paths.root'] . '/.htaccess');
    }
    
    public function run()
    {
        $app = $this->app;
        
        $app->register(new Service\PrototypeFinder(array(
            $app['pt.install.paths.config'],
            $app['pt.app.paths.config'],
        ), array(
            $app['pt.install.paths.prototypes'],
            $app['pt.app.paths.prototypes'],
        )));
        
        $app->register(new Service\Prontotype($this->sharedServices));
        
        $this->doHealthCheck();
        
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
        if ( $this->app['pt.prototype.environment'] !== '_system' ) {
            if ( ! file_exists($this->app['pt.install.paths.prototypes']) ) {
                $errors[] = 'The prototypes directory (' . $this->app['pt.install.paths.prototypes'] . ') does not exist.';
            }
        }
        if ( ! is_writeable($this->app['pt.install.paths.cache.root']) ) {
            $errors[] = 'The cache directory (' . $this->app['pt.install.paths.cache.root'] . ') is not writeable or does not exist.';
        }
        if ( count($errors) ) {
            throw new \Exception(implode('<br>', $errors));
        }
        
        foreach(array(
            $this->app['pt.prototype.paths.cache.templates'],
            $this->app['pt.prototype.paths.cache.assets'],
            $this->app['pt.prototype.paths.cache.data'],
            $this->app['pt.prototype.paths.cache.requests'],
            $this->app['pt.prototype.paths.cache.exports'],
        ) as $path) {
            if ( ! file_exists($path) ) {
                mkdir($path, 0771, true);
            }
        }
    }
    
}
