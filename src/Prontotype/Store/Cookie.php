<?php

namespace Prontotype\Store;

Class Cookie extends Base {
    
    protected $cookiePrefix = '';

    protected $cookieLifetime = '';
    
    public function __construct($app)
    {
        parent::__construct($app);
        $this->cookiePrefix = $app['pt.config']->get('storage.prefix');
        $this->cookieLifetime = $app['pt.config']->get('storage.lifetime');
    }
    
    public function get($key)
    {
        return isset($_COOKIE[$this->cookiePrefix . $key]) ? json_decode(rawurldecode(stripslashes($_COOKIE[$this->cookiePrefix . $key])), true) : NULL;
    }
    
    public function set($key, $value)
    {
        // raw url encode and set raw cookie used here to prevent issues with spaces encoded as '+'
        $value = rawurlencode(json_encode($value));
        setrawcookie( $this->cookiePrefix . $key, $value, time() + $this->cookieLifetime, '/' );
        $_COOKIE[$this->cookiePrefix . $key] = $value;
    }
    
    public function clear($key)
    {
        setcookie( $this->cookiePrefix . $key, '', time() - 3600, '/' );
        unset($_COOKIE[$this->cookiePrefix . $key]);
    }
    
}
