<?php

namespace Prontotype\Store;

use Prontotype\Store\Cookie as CookieStore;
use Prontotype\Store\Session as SessionStore;

Class Manager {

    protected $app;
    
    protected $adapter;

    public function __construct($app, $adapter)
    {
        $this->app = $app;
        switch( $adapter ) {
            case 'session':
                $this->adapter = new SessionStore($app);
                break;
            case 'cookie':
            default:
                $this->adapter = new CookieStore($app);
                break;
        }
    }
    
    public function get($key)
    {
        return $this->adapter->get($key);
    }
    
    public function set($key, $value)
    {
        return $this->adapter->set($key, $value);
    }
    
    public function clear($key)
    {
        return $this->adapter->clear($key);
    }
    
}
