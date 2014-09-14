<?php
/**
 * User: dongww
 * Date: 14-4-4
 * Time: 下午1:53
 */

namespace Dongww\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use DebugBar\StandardDebugBar;
use DebugBar\Bridge\DoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;

/**
 * DebugBar Provider
 *
 * Class DebugBarProvider
 * @package Dongww\SilexBase\Provider
 */
class DebugBarServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        if (!isset($app['debug_bar'])) {
            $app['debug_bar'] = $app->share(function () {
                return new StandardDebugBar();
            });

            if (isset($app['db'])) {
                $debugStack = new DebugStack();
                $app['db']->getConfiguration()->setSQLLogger($debugStack);
                $app['debug_bar']->addCollector(new DoctrineCollector($debugStack));
            }
        }

        $app['debug_bar.path'] = isset($app['debug_bar.path']) ? $this->app['debug_bar.path'] : '/debugbar';
    }

    /**
     * 输出debugBar，只有当页面有</body>标签时有效。
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return;
        }
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($response->isRedirection()
            || ($response->headers->has('Content-Type') &&
                false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        $baseUrl = $event->getRequest()->getBaseUrl();

        $render = $this->app['debug_bar']->getJavascriptRenderer($baseUrl . $this->app['debug_bar.path']);
        ob_start();
        echo $render->renderHead();
        echo $render->render();
        $debugContent = ob_get_contents();
        ob_end_clean();

        $content = $response->getContent();
        $content = str_replace("</body>", $debugContent . '</body>', $content);
        $event->getResponse()->setContent($content);
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::RESPONSE, [$this, 'onKernelResponse'], -1000);

        $app->get($app['debug_bar.path'] . '/{path}', function ($path) use ($app) {
            return $app->sendFile(
                $app['debug_bar']->getJavascriptRenderer()->getBasePath() . '/' . $path,
                200,
                ['Content-Type' => 'text/css']
            );
        })->assert('path', '.+');
    }
}
