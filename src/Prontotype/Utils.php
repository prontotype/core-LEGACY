<?php

namespace Prontotype;

Class Utils {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function generateUrlPath($route)
    {
        $url = $this->app['url_generator']->generate($route);
        if ( ! $this->app['pt.config']['clean_urls'] && strpos( $url, 'index.php' ) === false ) {
            $url = '/index.php' . $url;
        }
        return $url;
    }
    
    // TODO: cache this!
    public function fetchFromUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        $info = array(
            'body' => $data,
            "mime" => curl_getinfo($ch, CURLINFO_CONTENT_TYPE)
        );
        curl_close($ch);
        return $info;
    }
    
    public function templateExists($templatePath)
    {
        return file_exists($this->app['pt.prototype.paths.templates'] . '/' . $templatePath);
    }
    
    protected function forcefileContents($path, $contents)
    {
        $file = basename($path);
        $dir = dirname($path);

        if ( ! is_dir($dir) ) {
            mkdir($dir, 0771, true);
        }
        
        file_put_contents($path, $contents);
        chmod($path, 0644);
    }

    protected function forceRemoveDir($dir)
    {
        foreach ( glob($dir . '/*') as $file ) {
            if ( is_dir($file) ) {
                $this->rrmdir( $file );
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
    
}
