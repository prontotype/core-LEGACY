<?php

namespace Prontotype;

Class Notifications {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function setFlash($name, $content)
    {
        $this->app['pt.session']->setFlash('notifications', array(
            $name => $content
        ));
    }
    
    public function get($name)
    {
        $notifications = $this->app['pt.session']->getFlash('notifications');
        return isset($notifications[$name]) ? $notifications[$name] : null;
    }
    
    public function getAll()
    {
        $notifications = $this->app['pt.session']->getFlashBag()->get('notifications');
        return $notifications;
    }
}