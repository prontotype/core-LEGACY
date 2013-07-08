<?php

namespace Prontotype\Store;

Class Session extends Base {
    
    public function __construct($app)
    {
        parent::__construct($app);
        $app->register(new \Silex\Provider\SessionServiceProvider(), array(
            'session.storage.options' => array(
                'name' => $app['pt.config']->get('storage.prefix') . 'SESS',
                'cookie_lifetime' => $app['pt.config']->get('storage.lifetime')
            )
        ));
    }
    
    public function get($key)
    {
        return $this->app['session']->get($key);
    }
    
    public function set($key, $value)
    {
        return $this->app['session']->set($key, $value);
    }
    
    public function clear($key)
    {
        return $this->app['session']->remove($key);
    }
    
}
