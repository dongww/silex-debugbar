<?php
/**
 * User: dongww
 * Date: 2014-11-26
 * Time: 12:41
 */
use Silex\Application;
use Dongww\Silex\Provider\DebugBarServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app          = new Application();
$app['debug'] = true;

if ($app['debug']) {
    $app->register(new DebugBarServiceProvider(), [
//        'debug_bar.auto_res' => false, //Optional, default is true
//        'debug_bar.path'     => '/debugbar', //Optional, default is null.
    ]);
}

$app->get('/', function (Application $app) {
    $app['debug_bar']['messages']->addMessage("Hello DebugBar!");
    $app['debug_bar']['messages']->addMessage([
        'a' => 1,
        'b' => 2,
        'c' => 3,
    ]);

    return '<body><h1>This is an example for silex_debugbar provider.</h1></body>';
});

$app->run();
