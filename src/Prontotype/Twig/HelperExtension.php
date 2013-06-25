<?php

namespace Prontotype\Twig;

class HelperExtension extends \Twig_Extension
{
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getName()
    {
        return 'helper';
    }
    
    public function getGlobals()
    {
        $app = $this->app;
        return array(
            'pt' => array(
                'VERSION'       => \Prontotype\Application::VERSION,
                'config'        => $this->app['pt.config'],      
                'request'       => $this->app['pt.request'],
                'pages'         => $this->app['pt.pagetree'],
                'page'          => $this->app['pt.pagetree']->getCurrent(),
                'user'          => $this->app['pt.user_manager']->getCurrentUser(),
                'data'          => $this->app['pt.data'],
                'store'         => $this->app['pt.store'],
                'assets'        => $this->app['pt.assets_helper'],
                'notifications' => $this->app['pt.notifications'],
                'snippets'      => $this->app['pt.snippets'],
                'scraper'       => $this->app['pt.scraper'],
                'urls' => array(
                    'user' => array(
                        'login' => $this->app['pt.utils']->generateUrlPath('user.login'),
                        'logout' => $this->app['pt.utils']->generateUrlPath('user.logout')
                    ),
                    'auth' => array(
                        'form' => $this->app['pt.utils']->generateUrlPath('auth.login'),
                        'login' => $this->app['pt.utils']->generateUrlPath('auth.check'),
                        'logout' => $this->app['pt.utils']->generateUrlPath('auth.logout')
                    ),
                    'export' => array(
                        'overview' => $this->app['pt.utils']->generateUrlPath('export.overview'),
                        'fetch' => $this->app['pt.utils']->generateUrlPath('export.run'),
                        'clear' => $this->app['pt.utils']->generateUrlPath('export.clear'),
                        'download' => $this->app['pt.utils']->generateUrlPath('export.download')
                    ),
                    'docs' => array(
                        'root' => $this->app['pt.utils']->generateUrlPath('docs.view')
                    ),
                )
            )
        );
    }
}
