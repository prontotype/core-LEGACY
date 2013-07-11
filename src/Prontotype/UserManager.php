<?php

namespace Prontotype;

Class UserManager {

    protected $app;
    
    protected $users;
    
    protected $identifyBy;
    
    protected $authBy;
    
    protected $currentUser = null;
    
    protected $userCookieName = 'user';

    public function __construct($app)
    {
        $this->app = $app;        
        $this->users = $this->app['pt.data']->get('users.yml') ? $this->app['pt.data']->get('users.yml') : array();
        $this->identifyBy = $this->app['pt.config']->get('user.identify');
        $authConf = $this->app['pt.config']->get('user.auth');
        $this->authBy = ! empty($authConf) ? $authConf : null;
    }
    
    public function userIsLoggedIn()
    {
        if ( ! $user = $this->getCurrentUser() ) {
            return false;
        }
        return $this->loggedInUserIsValid($user);
    }
    
    public function attemptLogin($identity, $auth = null)
    {
        if ( $userData = $this->getUserBy($this->identifyBy, $identity) ) {
            if ( ! $this->authBy || $auth == @$userData[$this->authBy] ) {
                $this->app['pt.store']->set($this->userCookieName, $userData);
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
            $this->currentUser = $this->app['pt.store']->get($this->userCookieName);
        }
        if ( $this->loggedInUserIsValid($this->currentUser) ) {
            return $this->currentUser;    
        } else {
            $this->logoutUser();
            return null;
        }
    }
    
    public function loggedInUserIsValid($user)
    {
        if ( $userData = $this->getUserBy($this->identifyBy, $user[$this->identifyBy]) ) {
            if ( ! $this->authBy || @$user[$this->authBy] == @$userData[$this->authBy] ) {
                return true;
            }
        }
        return false;
    }
    
    public function getUserBy($key, $val)
    {
        foreach( $this->users as $userData ) {
            if ( @$userData[$key] == $val ) {
                return $userData;
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
    
}