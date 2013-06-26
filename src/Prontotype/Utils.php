<?php

namespace Prontotype;

Class Utils {

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function generateUrlPath($route, $params = array())
    {
        try {
            if ( $route == 'home' ) {
                $url = ! empty($this->app['pt.prototype.path']) ? $this->app['pt.prototype.path'] : '/';
                if ( ! $this->app['pt.env.clean_urls'] ) {
                    $url = '/index.php' . $url;
                }
            } else {
                $url = $this->app['url_generator']->generate($route, $params);
                if ( ! $this->app['pt.env.clean_urls'] && strpos( $url, 'index.php' ) === false ) {
                    $url = '/index.php' . $url;
                }                
            }
            return $url;            
        } catch (\Exception $e) {
            return '#';
        }
    }
    
    public function fetchFromUrl($url, $ignoreCache = false)
    {
        if ( ! $ignoreCache ) {
            $data = $this->app['pt.cache']->get(Cache::CACHE_TYPE_REQUESTS, $url);
            if ( $data ) return $data;
        }
        
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
        
        $this->app['pt.cache']->set(Cache::CACHE_TYPE_REQUESTS, $url, $info);
        
        return $info;
    }
    
    public function templateExists($templatePath)
    {
        return file_exists($this->app['pt.prototype.paths.templates'] . '/' . $templatePath);
    }
    
    public function forcefileContents($path, $contents)
    {
        $file = basename($path);
        $dir = dirname($path);
        
        if ( ! is_dir($dir) ) {
            mkdir($dir, 0771, true);
        }
        
        file_put_contents($path, $contents);
        chmod($path, 0644);
    }

    public function forceRemoveDir($dir, $includeParent = true)
    {
        if ( ! empty($dir) && $dir !== '/' ) {
            foreach ( glob($dir . '/*') as $file ) {
                if ( is_dir($file) ) {
                    $this->forceRemoveDir( $file, true );
                } else {
                    unlink($file);
                }
            }
            if ( $includeParent ) {
                rmdir($dir);
            }
        }
    }
    
    function zipDir($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) {
                    continue;                    
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
    
}
