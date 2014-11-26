php-debugbar provider for silex.

![debugBar](debug-bar.jpg)

##Install

`composer require dongww/silex-debugbar *`

##Usage

~~~ .php
$app->register(new Dongww\Silex\Provider\DebugBarServiceProvider());
~~~

The JS and CSS file will be loaded automatically.
If you want change the route for JS and CSS files, you can set the option 'debug_bar.path',
default is '/debugbar'. example:

~~~ .php
$app->register(new Dongww\Silex\Provider\DebugBarServiceProvider(), [
    'debug_bar.path' => '/debug', //Optional, Default is '/debugbar'.
]);
~~~

##Example

~~~ .php
<?php
use Silex\Application;
use Dongww\Silex\Provider\DebugBarServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app          = new Application();
$app['debug'] = true;

if ($app['debug']) {
    $app->register(new DebugBarServiceProvider(), [
        'debug_bar.path' => '/debug', //Optional, default is '/debugbar'.
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
~~~