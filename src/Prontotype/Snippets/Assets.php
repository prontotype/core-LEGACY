<?php

namespace Prontotype\Snippets;

Class Assets extends Base {
    
    protected $templatePath = '_system/snippets/assets';
    
    protected $configKey = 'assets';
    
    public function __construct($app)
    {
        $this->placeholderUrls = $app['pt.config']->get('system.snippets.images.placeholder.services');
        parent::__construct($app);
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
            'ptpath'   => $this->app['pt.config']->get('triggers.assets') . '/_placeholder',
            'service'  => $this->config['placeholder']['service'],
        ), $opts);
                
        if ( isset($this->placeholderUrls[$opts['service']]) ) {
            $attrs['src'] = rtrim($this->app['twig.stringloader']->render($this->placeholderUrls[$opts['service']], $opts),'/');
        } else {
            return '';
        }
        
        return $this->image($attrs['src'], $attrs, true);
    }
    
    public function stylesheet($href, $media = 'all', $attrs = array(), $rawPath = false)
    {
        if ( ! $rawPath ) {
            $href = $this->app['pt.assets_helper']->getUrlPath($href);
        }
        
        $attrs = $this->mergeOpts(array(
            'type'  => 'text/css',
            'href'  => $href,
            'media' => $media,
        ), $attrs);
            
        return $this->renderTemplate('stylesheet.html', array(
            'attrs'  => $attrs,
        ));
    }
    
    public function js($src, $attrs = array(), $rawPath = false)
    {
        if ( ! $rawPath ) {
            $src = $this->app['pt.assets_helper']->getUrlPath($src);
        }
        
        $attrs = $this->mergeOpts(array(
            'src'  => $src,
        ), $attrs);
            
        return $this->renderTemplate('js.html', array(
            'attrs'  => $attrs,
        ));
    }
    
    public function image($src, $attrs = array(), $rawPath = false)
    {
        if ( ! $rawPath ) {
            $src = $this->app['pt.assets_helper']->getUrlPath($src);
        }
        
        $attrs = $this->mergeOpts(array(
            'src' => $src,
        ), $attrs);
            
        return $this->renderTemplate('image.html', array(
            'attrs'  => $attrs,
        ));
    }
    
}