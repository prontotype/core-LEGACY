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
        'pt.pages'         => '\Prontotype\PageTree\Manager',
        'pt.auth'          => '\Prontotype\Auth',
        'pt.notifications' => '\Prontotype\Notifications',
        'pt.exporter'      => '\Prontotype\Exporter',
        'pt.scraper'       => '\Prontotype\Scraper\Scraper',
        'pt.utils'         => '\Prontotype\Utils',
        'pt.user_manager'  => '\Prontotype\UserManager',
        'pt.snippets'      => '\Prontotype\Snippets\Manager',
        'pt.assets_helper' => '\Prontotype\Assets\Helper',
        'pt.session'       => '\Prontotype\Session',
    );
    
    protected $srcPaths = array(
        'root'       => '/../../',
        'app_root'   => '/../../app',
        'data'       => '/../../app/data',
        'config'     => '/../../app/config',
        'assets'     => '/../../app/assets',
        'templates'  => '/../../app/templates',
    );
    
	public function __construct($paths)
    {
        $this->paths = $paths;
        $this->app = $app = new SilexApp();
        
        $this->app['pt.install.paths.root'] = $this->paths['root'];
        $this->app['pt.install.paths.cache.root'] = $this->paths['cache'];
        $this->app['pt.install.paths.vendor'] = $this->paths['vendor'];
        $this->app['pt.install.paths.config'] = $this->paths['config'];
        
        foreach($this->srcPaths as $key => $path) {
            $this->app['pt.app.paths.' . $key] = realpath(__DIR__ . $path);
        }
        
        $this->app['pt.env.clean_urls'] = file_exists($this->app['pt.install.paths.root'] . '/.htaccess');
    }
    
    public function run($host = null)
    {
        $app = $this->app;
        
        $app->register(new Service\PrototypeFinder(array(
            $app['pt.install.paths.config'],
            $app['pt.app.paths.config'],
        ), array(
            $app['pt.install.paths.root'],
            $app['pt.app.paths.app_root'],
        ), $host));
        
        $app->register(new Service\Prontotype($this->sharedServices));

        $this->doHealthCheck();
        
        $this->app->before(function() use ($app){
            // redirect if there is a trailing slash
            if ( $app['pt.request']->urlPathHasTrailingSlash() ) {
                return $app->redirect($app['pt.request']->getRawUrlPath());
                exit();
            }
            // remove index.php if clean URLs are enabled
            if ( $app['pt.env.clean_urls'] && strpos($app['pt.request']->getRawUrlPath(), '/index.php') === 0 ) {
                return $app->redirect(str_replace('/index.php', '', $app['pt.request']->getRawUrlPath()));
                exit();
            }
        });
        
        $this->app->run();
        
        return $app;
    }
    
    public function doHealthCheck()
    {
        $errors = array();
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
