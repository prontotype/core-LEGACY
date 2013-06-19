<?php

namespace Prontotype\Snippets;

Class Images extends Base {
    
    protected $templatePath = '_system/snippets/images';
    
    protected $configKey = 'images';
    
    protected $placeholderUrls = array();
    
    public function __construct($app)
    {
        parent::__construct($app);
        $this->placeholderUrls = $app['pt.config']->get('system.snippets.images.placeholder.services');
    }
    
    public function placeholder($opts = array(), $attrs = array())
    {
        $opts = $this->mergeOpts(array(
            'width'    => $this->config['placeholder']['width'],
            'height'   => $this->config['placeholder']['height'],
            'service'  => $this->config['placeholder']['service'],
        ), $opts);
        
        if ( isset($this->placeholderUrls[$this->config['placeholder']['service']]) ) {
            $attrs['src'] = $this->app['twig.stringloader']->render($this->placeholderUrls[$this->config['placeholder']['service']], $opts);
        } else {
            return '';
        }
        
        return $this->image($attrs);
    }
    
    public function image($attrs)
    {
        return $this->renderTemplate('image.twig', array(
            'attrs' => $attrs
        ));
    }
    
}