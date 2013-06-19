<?php

namespace Prontotype;

Class Cache {

    const CACHE_TYPE_REQUESTS = 'requests';
    const CACHE_TYPE_DATA = 'data';
    const CACHE_TYPE_ASSETS = 'assets';
    const CACHE_TYPE_EXPORTS = 'exports';

    protected $app;
    
    protected $cacheInfo;
    
    protected $cacheExt = '.cache';

    public function __construct($app, $cacheInfo = array())
    {
        $this->app = $app;
        $this->cacheInfo = $cacheInfo;
        $this->defaultCacheExpiry = $this->app['pt.config']->get('system.cache.default_expiry');
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
        return $path;
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
    
    public function delete($type, $key = null)
    {
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            throw new \Exception('Cannot delete cache data of type \'' . $type . '\'');
        }
        if ( $key ) {
            unlink($this->getCachePath($type, $key));
        } else {
            $this->app['pt.utils']->forceRemoveDir($this->cacheInfo[$type]['path'], false);
        }
    }
        
    public function setRaw($type, $key, $contents, $subDir = null)
    {        
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            return null;
        }
        
        $path = $this->getRawCachePath($type, $key, $subDir);
        
        $this->app['pt.utils']->forcefileContents($path, $contents);
        return $path;
    }
    
    public function deleteRaw($type, $key = null, $subDir = null)
    {        
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            throw new \Exception('Cannot delete cache data of type \'' . $type . '\'');
        }
        if ( $key ) {
            unlink($this->getRawCachePath($type, $key, $subDir));
        } else {
            if ( $subDir ) {
                $this->app['pt.utils']->forceRemoveDir($this->cacheInfo[$type]['path'] . '/' . $subDir);    
            } else {
                $this->app['pt.utils']->forceRemoveDir($this->cacheInfo[$type]['path'], false);
            }
        }
    }
    
    public function listContents($type)
    {
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            return array();
        }
        $list = array();
        $files = glob($this->cacheInfo[$type]['path'] . '/*' );
        foreach($files as $file) {
            $list[] = str_replace($this->cacheInfo[$type]['path'] . '/', '', $file);
        }
        return $list;
    }
    
    public function getCacheDirPath($type)
    {
        if ( ! isset($this->cacheInfo[$type]['path']) ) {
            return null;
        }
        return $this->cacheInfo[$type]['path'];
    }
    
    protected function getRawCachePath($type, $key, $subDir = null)
    {
        if ( $subDir ) {
            return $this->cacheInfo[$type]['path'] . '/' . $subDir . '/' . trim($key,'/');
        } else {
            return $this->cacheInfo[$type]['path'] . '/' . trim($key,'/');
        }
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
        return sha1(base64_encode( $key )) . $this->cacheExt;
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