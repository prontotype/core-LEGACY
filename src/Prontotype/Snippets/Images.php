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
        if ( isset($opts['size'])) {
            $opts['width'] = $opts['size'];
            $opts['height'] = $opts['size'];
        }
        
        if ( isset($opts['color']) ) {
            $opts['color'] = str_replace('#','',$opts['color']);
        }
        if ( isset($opts['bgcolor']) ) {
            $opts['bgcolor'] = str_replace('#','',$opts['bgcolor']);
        }
        
        $opts = $this->mergeOpts(array(
            'width'    => $this->config['placeholder']['width'],
            'height'   => $this->config['placeholder']['height'],
            'bgcolor'  => $this->config['placeholder']['bgcolor'],
            'color'    => $this->config['placeholder']['color'],
            'category' => $this->config['placeholder']['category'],
            'service'  => $this->config['placeholder']['service'],
            'host'     => $this->app['pt.request']->getSchemeAndHttpHost(),
            'ptpath'   => $this->app['pt.config']->get('triggers.assets') . '/placeholder',
            'service'  => $this->config['placeholder']['service'],
        ), $opts);
                
        if ( isset($this->placeholderUrls[$opts['service']]) ) {
            $attrs['src'] = rtrim($this->app['twig.stringloader']->render($this->placeholderUrls[$opts['service']], $opts),'/');
        } else {
            return '';
        }
        
        return $this->image($attrs);
    }
    
    public function image($attrs)
    {
        return $this->renderTemplate('image.html', array(
            'attrs' => $attrs
        ));
    }
    
}