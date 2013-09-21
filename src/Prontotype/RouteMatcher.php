<?php

namespace Prontotype;

use Symfony\Component\Yaml\Yaml;

Class RouteMatcher {

    protected $app;
    
    protected $routeCache = array();
    
    protected $loadPaths = array();
    
    protected $tokens = array(
        '{any}' => '([^/]*)',
        '{num}' => '(\d*)',
        '{all}' => '(.*)'
    );
    
    public function __construct($app, $loadPaths = array())
    {
        $this->app = $app;
        foreach($loadPaths as $path) {
            $this->addLoadPath($path);
        }
        $this->routes = $this->mergeRoutes();
        echo '<pre>';
        print_r($this->routes);
        echo '</pre>';
    }
    
    public function convertRoute($route)
    {
        $route = trim($route,'/');
        $originalRoute = $route;
        
        if ( isset($this->routeCache[$route]) ) {
            return $this->routeCache[$route];
        }
        
        // lets see if we need to do any checking of custom routes
        $routes = $this->getRoutes();
        $root = $this->app['pt.prototype.path'] . '/';
        $replacements = array();
        if ( count($routes) ) {
            foreach( $routes as $name => $details ) {
                $routeSpec = $details['match'];
                // see if there are any page ID placeholders that need parsing out
                $replacements = array();
                if ( preg_match('/\[([^\]]*)\]/', $routeSpec, $matches) ) {
                    if ( $routePage = $this->app['pt.pages']->getById($matches[1]) ) {
                        $replacements[] = trim($routePage->getUnPrefixedUrlPath(),'/');
                        if ( $root == '/' ) {
                            $routeSpec = str_replace(
                                array($matches[0],'index.php'),
                                array($routePage->getUrlPath(),''),
                                $routeSpec
                            );                            
                        } else {
                            $routeSpec = str_replace(
                                array($matches[0],'index.php', $root),
                                array($routePage->getUrlPath(),'', ''),
                                $routeSpec
                            );
                        }
                    } else {
                        continue;
                    }
                }
                
                $routeSpec = ltrim($root,'/') . trim($routeSpec,'/');
                
                // replace helper placeholders
                if ( preg_match( $this->makeRegexp($routeSpec, $this->tokens), $route, $matches ) ) {
                    // we have a match!
                    for( $i = 0; $i < count($matches); $i++ ) {
                        if ( $i !== 0) {
                            $replacements[] = $matches[$i];
                        }
                    }
                    $route = $details['display'];
                    break;
                }
            }
            
            // replace reference tokens in the route
            $replacementTokens = array();
            for ( $j = 0; $j < count($replacements); $j++ ) {
                $replacementTokens['$' . ($j+1)] = $replacements[$j];
            }
            $route = str_replace(array_keys($replacementTokens), array_values($replacementTokens), $route);

            // replace any page ID placeholders in the route itself
            $route = $this->replaceIds($route);
        }
        $route = trim(str_replace('//', '/', $route),'/');
        $this->routeCache[$originalRoute] = $route;
        return $route;
    }
    
    public function getUrlForRoute($routeName, $params = array())
    {
        if ( $urlPath = $this->getUrlPathForRoute($routeName, $params) ) {
            return $this->app['pt.request']->getUriForPath($urlPath);
        }
    }
    
    public function getUrlPathForRoute($routeName, $params = array())
    {
        $routes = $this->getRoutes();
        if ( ! isset($routes[$routeName], $routes[$routeName]['display']) ) {
            return null;
        }
        $route = $routes[$routeName];
        if ( strpos($route['display'], '[') === false && strpos($route['display'], '$') === false ) {
            return $route['display']; // no tokens in the route to display, so can just return as-is
        }
        
        $route = $route['match'];
        
        // check we've got enough params
        preg_match_all('/\{([^\}]*)\}/', $route, $matches);
        
        if ( count($matches[0]) !== count($params) ) {
            return null;
        }
        foreach ( $params as $param ) {
            $route = preg_replace('/\{([^\}]*)\}/', $param, $route, 1);
        }
        $route = $this->replaceIds($route);
        $route = '/' . trim($route,'/');
        return $this->prefixRoute($route);
    }
    
    protected function makeRegexp($route, $tokens)
    {
        $route = str_replace(
            array_merge(array_keys($tokens), array('/')),
            array_merge(array_values($tokens), array('\/')),
            $route
        );
        return '/^' . $route . '$/';
    }
    
    protected function replaceIds($route)
    {
        $root = $this->app['pt.prototype.path'] . '/';
        if ( preg_match('/\[([^\]]*)\]/', $route, $matches) ) {
            if ( $routePage = $this->app['pt.pages']->getById($matches[1]) ) {
                if ( $root == '/' ) {
                    $route = $root . str_replace(
                        array($matches[0],'index.php'),
                        array($routePage->getUrlPath(),''),
                        $route
                    );    
                } else {
                    $route = $root . str_replace(
                        array($matches[0],'index.php',$root),
                        array($routePage->getUrlPath(),'',''),
                        $route
                    );    
                }                    
            }
        }
        return $route;
    }
    
    protected function getRoutes()
    {
        return $this->routes;
    }
    
    protected function prefixRoute($route)
    {
        $prefix = '';
        if ( ! $this->app['pt.env.clean_urls'] ) {
            $prefix = '/index.php';
        }
        return $prefix . $route;
    }
    
    public function addLoadPath($path)
    {
        if ( file_exists($path) ) {
            $this->loadPaths[] = $path . '/routes.yml';
        }
    }
    
    public function getLoadPaths()
    {
        return array_reverse($this->loadPaths);
    }
    
    protected function mergeRoutes()
    {
        $routes = array();
        $loadPaths = $this->getLoadPaths();
        foreach($loadPaths as $loadPath) {
            if ( file_exists($loadPath)) {
                if ( trim(file_get_contents($loadPath)) !== '' ) {
                    $parsed = Yaml::parse($loadPath);
                    if ($parsed !== null) {
                        $routes = $this->merge($routes, $parsed);
                    }
                }
            }
        }
        $configRoutes = $this->app['pt.config']->get('pages.routes') ? $this->app['pt.config']->get('pages.routes') : array();
        $routes = $this->merge($routes, $configRoutes);
        return $this->cleanRoutes($routes);
    }
    
    protected function cleanRoutes($rawRoutes)
    {
        $routes = array();
        foreach( $rawRoutes as $name => $details ) {
            if ( isset($details['match'], $details['display']) ) {
                if ( is_array($details['display']) ) {
                    // should be string, probably forgot to quote an id in square brackets in the yml config
                    $details['display'] = '[' . $details['display'][0] . ']';
                }
                if ( is_array($details['match']) ) {
                    // should be string, probably forgot to quote an id in square brackets in the yml config
                    $details['match'] = '[' . $details['match'][0] . ']';
                }
                $match = ltrim($details['match'], '/');
                $display = $this->app['pt.prototype.path'] . '/' . ltrim($details['display'], '/');
                $routes[$name] = array(
                    'match'   => $match,
                    'display' => $display,
                    'name'    => $name
                );
            }
        }
        return $routes;
    }
    
    protected function merge( array &$array1, array &$array2 )
    {
        $merged = $array1;
        foreach ( $array2 as $key => &$value ) {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
                $merged [$key] = $this->merge( $merged [$key], $value );
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }
    
}
