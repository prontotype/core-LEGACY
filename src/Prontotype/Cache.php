<?php

namespace Prontotype;

Class Cache {

    protected $app;
    
    protected $cacheInfo;
    
    protected $cacheExt = '.cache';

    public function __construct($app, $cacheInfo = array())
    {
        $this->app = $app;
        $this->cacheInfo = $cacheInfo;
        $this->defaultCacheExpiry = $this->app['pt.config']['system']['cache']['default_expiry'];
    }
    
    public function set($type, $key, $content, $expiry = null)
    {
        if ( ! $path = $this->getCachePath($type, $key) ) {
            throw new \Exception('Cannot cache data of type \'' . $type . '\'');
        }
        $data = array(
            'stored'  => time(),
            'expiry'  => $this->getCacheTypeExpiry($type, $expiry),
            'content' => $content
        );
        file_put_contents($path, serialize($data));
    }
    
    public function get($type, $key, $newerThan = null)
    {
        if ( ! $path = $this->getCachePath($type, $key) ) {
            throw new \Exception('Cannot retrieve cache data of type \'' . $type . '\'');
        }
        if ( ! file_exists( $path ) ) {
            return null;
        }
        $data = unserialize(file_get_contents($path));
        
        if ( $data['expiry'] < time() ) {
            $this->delete($type, $key); // cached data is stale, delete
            return null;
        } else if ( $newerThan && ( $data['stored'] < $newerThan ) ) {
            $this->delete($type, $key); // cached data is stale, delete
            return null;
        }
        return $data['content'];
    }
    
    protected function getCachePath($type, $key)
    {
         if ( ! isset($this->cacheInfo[$type]['path']) ) {
             return null;
         }
         return $this->cacheInfo[$type]['path'] . '/' . $this->encodeKey($key);
    }
    
    protected function encodeKey( $key )
    {
        return base64_encode( $key );
    }
    
    public function delete($type, $key = null)
    {
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            throw new \Exception('Cannot delete cache data of type \'' . $type . '\'');
        }
        if ( $key ) {
            unlink($this->getCachePath($type, $key));
        } else {
            foreach( glob( $this->cacheInfo[$type]['path'] . '/*' ) as $file  ) {
                unlink($file);
            }
        }
    }
    
    protected function getCacheTypeExpiry($type, $override = null)
    {
        if ( $override ){
            return $override;
        }
        if ( isset($this->cacheInfo[$type]['expiry']) ) {
            return time() + $this->cacheInfo[$type]['expiry'];
        }
        return time() + $this->defaultCacheExpiry;
    }

}