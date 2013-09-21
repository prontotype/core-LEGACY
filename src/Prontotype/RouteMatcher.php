<?php

namespace Prontotype;

Class RouteMatcher {

    protected $app;
    
    protected $routeCache = array();
    
    public function __construct($app)
    {
        $this->app = $app;
        $this->routes = $this->loadRoutes();
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
                    if ( $routePage = $this->getById($matches[1]) ) {
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
                $routeSpec = trim($routeSpec,'/');
                // replace helper placeholders
                $routeSpec = str_replace(
                    array('{any}','{num}','{all}','/'),
                    array('([^/]*)','(\d*)','(.*)','\/'),
                    $routeSpec
                );

                $routeSpec = '/^' . str_replace('/','\/',ltrim($root,'/')) . $routeSpec . '$/';
                if ( preg_match( $routeSpec, $route, $matches ) ) {
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
            
            // replace and reference tokens in the route
            // '(:id=test)/hello': '$1'
            $replacementTokens = array();
            for ( $j = 0; $j < count($replacements); $j++ ) {
                $replacementTokens['$' . ($j+1)] = $replacements[$j];
            }
            $route = str_replace(array_keys($replacementTokens), array_values($replacementTokens), $route);
        
            // replace any page ID placeholders in the route itself
            if ( preg_match('/\[([^\]]*)\]/', $route, $matches) ) {
                if ( $routePage = $this->getById($matches[1]) ) {
                    if ( $root == '/' ) {
                        $route = $root . str_replace(array($matches[0],'index.php'), array($routePage->getUrlPath(),''), $route);    
                    } else {
                        $route = $root . str_replace(array($matches[0],'index.php',$root), array($routePage->getUrlPath(),'',''), $route);    
                    }                    
                }
            }
        }
        $route = trim(str_replace('//', '/', $route),'/');
        $this->routeCache[$originalRoute] = $route;
        return $route;
    }
    
    public function getUrlForRoute($routeName, $params = array())
    {
        
    }
    
    protected function getRoutes()
    {
        return $this->routes;
    }
    
    protected function loadRoutes()
    {
        $routes = array();
        $routeDefinitions = $this->app['pt.config']->get('pages.routes') ? $this->app['pt.config']->get('pages.routes') : array();
        foreach( $routeDefinitions as $name => $details ) {
            if ( isset($details['match'], $details['display']) ) {
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
    
}
