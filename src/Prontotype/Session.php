<?php

namespace Prontotype;

Class Session {

    protected $app;
    
    protected $session;

    public function __construct($app)
    {
        $this->app = $app;
        $this->session = $app['session'];
    }
        
    public function setFlash($name, $value)
    {
        return $this->session->getFlashBag()->set($name, $value);
    }
    
    public function getFlash($name)
    {
        $result = $this->session->getFlashBag()->get($name);
        if ( count($result) ) {
            return $result;
        }
        return null;
    }
    
    public function __call($name, $args)
    {
        if ( method_exists( $this->session, $name ) ) {
            return call_user_func_array(array($this->session, $name), $args);
        }
    }
    
}
