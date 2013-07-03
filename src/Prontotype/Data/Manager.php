<?php

namespace Prontotype\Data;

Class Manager {

    protected $app;
    
    protected $parsers = array();
    
    protected $parsed = array();
    
    protected $loadPaths = array();
    
    protected $fallbackPath = null;
        
    protected $defaultFaker;
    
    protected $seededFakers = array();

    public function __construct($app, $parsers = array())
    {
        $this->app = $app;
        $this->defaultFaker = $this->createFaker();
        foreach( $parsers as $parser ) {
            $this->registerParser($parser);
        }
    }
    
    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }
    
    public function getLoadPaths()
    {
        $paths = $this->loadPaths;
        if ( $this->fallbackPath ) {
            $paths[] = $this->fallbackPath;
        }
        return $paths;
    }
    
    public function get($location, $replacements = null, $type = null, $dataPath = null)
    {
        if ( strpos($location, 'http') !== 0 ) {
            return $this->load($location, $replacements, $type, $dataPath);
        } else {
            return $this->fetch($location, $replacements, $type, $dataPath);
        }
    }
    
    public function faker($seed = null)
    {
        if ($seed === null) {
            return $this->defaultFaker;
        } elseif ( isset($this->seededFakers[$seed]) ) {
            return $this->seededFakers[$seed];
        }
        $faker = $this->createFaker($seed);
        $this->seededFakers[$seed] = $faker;
        return $this->seededFakers[$seed];
    }
    
    protected function load($filePath, $replacements = null, $type = null, $dataPath = null)
    {
        if ( ! $replacements ) {
            $replacements = array();
        }
        if ( isset($this->parsed[$filePath]) ) {
            $data = $this->parsed[$filePath];
        } else {
            try {
                $parts = pathinfo($filePath);
                $extension = ! $type ? $parts['extension'] : $type;
                $contents = $this->app['twig.dataloader']->render($filePath, $replacements);
                $data = $this->parse($contents, $extension);
            } catch( \InvalidArgumentException $e ) {
                return null;
            } catch ( \Exception $e ) {
                throw new \Exception(sprintf('Error parsing data file %s', $filePath));
            }
            $this->parsed[$filePath] = $data;
        }
        return $this->find($data, $dataPath);
    }
    
    protected function fetch($url, $replacements = null, $type = null, $dataPath = null)
    {
        if ( isset($this->parsed[$url]) ) {
            $data = $this->parsed[$url];
        } else {
            $data = $this->app['pt.utils']->fetchFromUrl($url);
            if ( !empty($data['body']) ) {
                if ( ! $type ) {
                    $type = $this->getExtensionFromMimeType($data['mime']);
                }
                $data = $this->parse($data['body'], $type);
            } else {
                $data = null;
            }
            $this->parsed[$url] = $data;
        }
        return $this->find($data, $dataPath);
    }
        
    public function registerParser(Parser $parser)
    {
        foreach( $parser->getHandledExtensions() as $extension ) {
            $extension = strtolower($extension);
            if ( ! isset($this->parsers[$extension]) ) {
                $this->parsers[$extension] = array();
            }
            $this->parsers[$extension][] = $parser;
        }
    }
    
    protected function createFaker($seed = null)
    {
        $faker = \Faker\Factory::create($this->app['pt.config']->get('data.faker.locale'));
        if ( $seed === null ) {
            $seed = $this->app['pt.config']->get('data.faker.seed');
            if ( empty($seed) ) {
                $seed = null;
            }
        }
        if ( $seed ) {
            $faker->seed($seed); 
        }
        $faker->addProvider(new \Prontotype\Faker\Prontotype($faker));
        return $faker;
    }
    
    protected function find($data, $path)
    {
        if ( empty($data) ) {
            return null;
        }
        if ( empty($path) ) {
            return $data;
        }
        $pathparts = explode( '.', trim( $path, '.') );
        if ( count( $pathparts) ) {
            foreach ( $pathparts as $key ) {
                if ( isset( $data[$key] ) ) {
                    $data = $data[$key];
                } else {
                    $data = null;
                    break;
                }
            }
        }
        return $data;
    }
    
    protected function parse($contents, $extension)
    {
        $extension = strtolower($extension);
        if ( ! isset($this->parsers[$extension]) ) {
            return $contents;
        }
        foreach( $this->parsers[$extension] as $parser ) {
            try {
                $contents = $parser->parse($contents);
            } catch ( \Exception $e ) {
                throw new \Exception(sprintf('Error parsing file'));
            }
        }
        return $contents;
    }
    
    protected function merge($old, $new)
    {
        if ( gettype($old) !== gettype($new) ) {
            throw new \Exception('Could not merge data');
        }
        if ( is_array($old) ) {
            return array_merge($new,$old);    
        }
        if ( is_string($old) ) {
            return $old . $new;
        }
        throw new \Exception('Could not merge data');
    }
    
    protected function getExtensionFromMimeType($mime)
    {
        if ( strpos($mime, 'json') !== false ) {
            return 'json';
        }
        if ( strpos($mime, 'html') !== false ) {
            return 'html';
        }
        if ( strpos($mime, 'csv') !== false ) {
            return 'csv';
        }
        if ( strpos($mime, 'yml') !== false ) {
            return 'yml';
        }
        if ( strpos($mime, 'xml') !== false ) {
            return 'xml';
        }
        return 'txt';
    }
    
}
