<?php

namespace Prontotype\Snippets;

Class Users extends Base {
    
    protected $templatePath = '_system/snippets/users';
    
    protected $configKey = 'users';
    
    public function login($opts = array(), $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'action' => $this->app['pt.utils']->generateUrlPath('user.login'),
            'method' => 'POST'
        ), $attrs);
        
        $opts = $this->mergeOpts(array(
            'redirect'      => null,
            'default'       => null,
            'loginUrl'      => $this->app['pt.utils']->generateUrlPath('user.login'),
            'identify'      => $this->app['pt.config']->get('user.identify'),
            'identifyLabel' => $this->app['pt.utils']->titleCase($this->app['pt.config']->get('user.identify')),
            'auth'          => $this->app['pt.config']->get('user.auth'),
            'authLabel'     => $this->app['pt.utils']->titleCase($this->app['pt.config']->get('user.auth')),
            'submitLabel'   => "Login &rarr;",
        ), $opts);
        
        return $this->renderTemplate('login.twig', array(
            'attrs' => $attrs,
            'opts' => $opts
        ));
    }
    
    public function logout($text = 'Logout', $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'href' => $this->app['pt.utils']->generateUrlPath('user.logout')
        ), $attrs);
        
        return $this->app['twig']->render('_system/snippets/navigation/link.twig', array(
            'text'  => $text,
            'attrs' => $attrs
        ));
    }
    
}