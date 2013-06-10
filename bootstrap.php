<?php

define('VERSION', '3.0');

/* Define globally available application paths */
define('DOC_ROOT', realpath(__DIR__ . '/..'));
define('APP_PATH', DOC_ROOT . '/' . $appDir);
define('VENDOR_PATH', APP_PATH . '/vendor');
define('PROTOYPES_PATH', DOC_ROOT . '/prototypes');
define('APP_TEMPLATES_PATH', APP_PATH . '/views');

if ( ! file_exists(APP_PATH . '/vendor/autoload.php') ) {
    throw new Exception("You need to install and run <a href=\"http://getcomposer.org\">Composer</a> before Prontoype will work. <a href=\"http://prontotype.allmarkedup.com/#setup\">Read the documentation for more details &rarr;</a>");
}

require_once APP_PATH . '/vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Prontotype\Service\Prototype($app));

date_default_timezone_set($app['pt.config']['timezone']);
$app['debug'] = $app['pt.config']['debug'];

$sharedServices = array(
    'pt.request'       => 'Prontotype\Request',
    'pt.pagetree'      => 'Prontotype\PageTree\Manager',
    'pt.store'         => 'Prontotype\Store',
    'pt.auth'          => 'Prontotype\Auth',
    'pt.notifications' => 'Prontotype\Notifications',
    'pt.exporter'      => 'Prontotype\Exporter',
    'pt.cache'         => 'Prontotype\Cache',
    'pt.scraper'       => 'Prontotype\Scraper\Scraper',
    'pt.utils'         => 'Prontotype\Utils',
    'pt.user_manager'  => 'Prontotype\UserManager',
    'pt.snippets'      => 'Prontotype\Snippets\Manager',
);

foreach( $sharedServices as $serviceName => $serviceClass ) {
    $app[$serviceName] = $app->share(function() use ($app, $serviceClass) {
        return new $serviceClass($app);
    });
}

$app['pt.extensions'] = $app->share(function($app) {
    $ext = new Prontotype\Extension\Manager($app);
    $ext->load($app['pt.config']['extensions']);
    return $ext;
});

$app['pt.data'] = $app->share(function($app) {
    return new Prontotype\Data\Manager($app, array(
        new Prontotype\Data\JsonParser($app),
        new Prontotype\Data\YamlParser($app),
        new Prontotype\Data\XmlParser($app),
        new Prontotype\Data\CsvParser($app)
    ));
});

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// set up twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'         => array( $app['pt.prototype.paths.templates'] . '/', APP_TEMPLATES_PATH . '/' ),
    'twig.options'      => array(
        'strict_variables'  => false,
        'cache'             => false, // TODO Enable caching
        'auto_reload'       => true,
        'debug'             => $app['pt.config']['debug'],
        'autoescape'        => false
    )
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new Twig_Extension_Debug());
    $twig->addExtension(new Prontotype\Twig\HelperExtension($app));
    return $twig;
}));

$app['twig.stringloader'] = $app->share(function($app) {
    $loader = new Twig_Loader_String();
    return new Twig_Environment($loader);
});


$app->before(function () use ($app) {
    if ( ! $app['pt.auth']->check() ) {
        return $app->redirect($app['pt.utils']->generateUrlPath('auth.login')); // not logged in, redirect to auth page
    }
    $app['pt.extensions']->before();
});

$app->after(function() use ($app) {
    $app['pt.extensions']->after();
});

$app->error(function(\Exception $e, $code) use ($app) {
    
    switch( $code ) {
        case '404':
            $template = 'pt/pages/404.twig';
            break;
        default:
            $template = 'pt/pages/error.twig';
            break;
    }
    
    return new Symfony\Component\HttpFoundation\Response($app['twig']->render($template, array(
        'message' => $e->getMessage()
    )), $code);
});

$app->mount('/' . $app['pt.config']['triggers']['auth'], new Prontotype\Controller\AuthController());
$app->mount('/' . $app['pt.config']['triggers']['data'], new Prontotype\Controller\DataController());
$app->mount('/' . $app['pt.config']['triggers']['user'], new Prontotype\Controller\UserController());
$app->mount('/' . $app['pt.config']['triggers']['assets'], new Prontotype\Controller\AssetController());
$app->mount('/' . $app['pt.config']['triggers']['shorturl'], new Prontotype\Controller\RedirectController());
$app->mount('/', new Prontotype\Controller\MainController());

return $app;


// 
// // import all PT macros
// foreach( glob(APP_PATH . '/views/pt/macros/*.twig') as $path ) {
//     $pathinfo = pathinfo($path);
//     $app['twig']->addGlobal($pathinfo['filename'], $app['twig']->loadTemplate('pt/macros/' . $pathinfo['basename']));
// }
// 
// // import all prototype macros
// if ( file_exists(TEMPLATES_PATH . '/macros') ) {
//     foreach( glob(TEMPLATES_PATH . '/macros/*.twig') as $path ) {
//         $pathinfo = pathinfo($path);
//         $app['twig']->addGlobal($pathinfo['filename'], $app['twig']->loadTemplate('/macros/' . $pathinfo['basename']));
//     }
// }
// 
