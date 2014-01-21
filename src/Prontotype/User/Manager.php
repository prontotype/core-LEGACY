<?php

namespace Prontotype\User;

use Prontotype\User\User;

Class Manager {

    protected $app;
    
    protected $users;
    
    protected $identifyBy;
    
    protected $authBy;
    
    protected $currentUser = null;
    
    protected $userCookieName = 'user';

    public function __construct($app)
    {
        $this->app = $app;        
        $this->users = $this->app['pt.data']->get($this->app['pt.config']->get('user.config_file')) ?: array();
        $this->identifyBy = $this->app['pt.config']->get('user.identify');
        $authConf = $this->app['pt.config']->get('user.auth');
        $this->authBy = ! empty($authConf) ? $authConf : null;
    }
    
    public function userIsLoggedIn()
    {
        return !! $this->getCurrentUser();
    }
    
    public function attemptLogin($identity, $auth = null)
    {
        $authByKey = $this->authBy;
        $idByKey = $this->identifyBy;
        if ( $user = $this->getUserBy($this->identifyBy, $identity) ) {
            if ( ! $this->authBy || $auth == @$user->$authByKey ) {
                $this->app['pt.store']->set($this->userCookieName, $user->$idByKey);
                return true;
            }
        }
        $this->app['pt.notifications']->setFlash('error', $this->app['pt.config']->get('user.login.error'));
        return false;
    }
    
    public function logoutUser()
    {
        $this->currentUser = null;
        $this->app['pt.store']->clear($this->userCookieName);
    }
    
    public function getCurrentUser()
    {
        if ( $this->currentUser === null ) {
            $idByVal = $this->app['pt.store']->get($this->userCookieName);
            if ( $this->currentUser = $this->getUserBy($this->identifyBy, $idByVal) ) {
                return $this->currentUser;
            } else {
                $this->logoutUser();
            }
        }
        return null;
    }
    
    public function getUserBy($key, $val)
    {
        foreach( $this->users as $userData ) {
            if ( @$userData[$key] == $val ) {
                return $this->createUser($userData);
            }
        }
        return null;
    }
    
    public function getLoginRedirectUrlPath($override = null)
    {
        if ( $override ) {
            return $override;
        }
        $rdir = $this->app['pt.config']->get('user.login.redirect');
        $rdir = ! empty($rdir) ? $rdir : '/';
        return $this->app['pt.utils']->makePrefixedUrl($rdir);
    }
    
    public function getLogoutRedirectUrlPath($override = null)
    {
        if ( $override ) {
            return $override;
        }
        $rdir = $this->app['pt.config']->get('user.logout.redirect');
        $rdir = ! empty($rdir) ? $rdir : '/';
        return $this->app['pt.utils']->makePrefixedUrl($rdir);
    }
    
    protected function createUser($data)
    {
        return new User($data);
    }
}