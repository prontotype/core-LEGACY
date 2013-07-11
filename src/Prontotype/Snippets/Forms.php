<?php

namespace Prontotype\Snippets;

Class Forms extends Base {
    
    protected $templatePath = '_system/snippets/forms';
    
    protected $configKey = 'forms';
    
    public function open($attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'method' => 'get',
        ), $attrs);
        
        return $this->renderTemplate('open.twig', array(
            'attrs' => $attrs
        ));
    }
    
    public function close()
    {
        return $this->renderTemplate('close.twig');
    }
    
    public function label($text = 'Label', $target = null, $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'for'   => $target
        ), $attrs);
        
        return $this->renderTemplate('label.twig', array(
            'text'   => $text,
            'attrs'  => $attrs,
        ));
    }
    
    public function button($text = 'Submit', $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'type' => 'submit'
        ), $attrs);
        
        return $this->renderTemplate('button.twig', array(
            'text'   => $text,
            'attrs'  => $attrs,
        ));
    }
    
    public function input($name = null, $type = 'text', $value = null, $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'name'  => $name,
            'type'  => $type,
            'value' => $value,
            'id'    => $name
        ), $attrs);
        
        return $this->renderTemplate('input.twig', array(
            'attrs'  => $attrs,
        ));
    }
    
    public function text($name = null, $value = null, $attrs = array())
    {
        return $this->input($name, 'text', $value, $attrs);
    }
    
    public function password($name = null, $value = null, $attrs = array())
    {
        return $this->input($name, 'password', $value, $attrs);
    }
    
    public function email($name = null, $value = null, $attrs = array())
    {
        return $this->input($name, 'email', $value, $attrs);
    }
    
    public function hidden($name = null, $value = null, $attrs = array())
    {
        return $this->input($name, 'hidden', $value, $attrs);
    }
    
    public function select($name = null, $value = null, $opts = array(), $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'name' => $name,
        ), $attrs);
        
        return $this->renderTemplate('select.twig', array(
            'value' => $value,
            'opts'  => $opts,
            'attrs' => $attrs,
        ));
    }
    
    public function textarea($name = null, $value = null, $attrs = array())
    {
        $attrs = $this->mergeOpts(array(
            'name' => $name,
        ), $attrs);
        
        return $this->renderTemplate('textarea.twig', array(
            'value' => $value,
            'attrs' => $attrs,
        ));
    }
    
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
    
    // public function control($type = 'input', $label = null, $name = null, $value = null, $opts = array(), $attrs = array())
    // {
    //     return $this->renderTemplate('control.twig', array(
    //         'type'  => $type,
    //         'label' => $label,
    //         'name'  => $name,
    //         'value' => $value,
    //         'opts'  => $opts,
    //         'attrs' => $attrs,
    //     ));
    // }
    
}