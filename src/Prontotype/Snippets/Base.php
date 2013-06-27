<?php

namespace Prontotype\Snippets;

Class Base {

    protected $app;
    
    protected $templatePath = '_system/snippets';
    
    protected $configKey = null;
    
    protected $config = array();

    public function __construct($app)
    {
        $this->app = $app;
        if ( $this->configKey ) {
            $snippetConfig = $this->app['pt.config']->get('snippets.' . $this->configKey);
            $this->config = ! empty($snippetConfig) ? $snippetConfig : array();
        }
    }
    
    protected function mergeOpts($defaults, $opts)
    {
        if ( ! is_array($defaults) ) $defaults = array();
        if ( ! is_array( $opts ) ) $opts = array();
        return array_merge($defaults, $opts);
    }
    
    protected function renderTemplate($filename, $data = array())
    {
        return $this->app['twig']->render($this->templatePath . '/' . $filename, $data);
    }
    
}