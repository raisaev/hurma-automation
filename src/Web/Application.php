<?php

declare(strict_types=1);

namespace App\Web;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\EventListener\SessionListener;

class Application
{
    public function __construct(
        private readonly ContainerInterface $containerBuilder,
        private readonly LoggerInterface $logger,
        private readonly Kernel $kernel
    ) {
    }

    // ----------------------------------------

    public function run(Request $request): void
    {
        $loader = new AnnotationDirectoryLoader(
            new FileLocator($this->kernel->getRootDir() . '/src/Web/Controller'),
            new AnnotatedRouteControllerLoader()
        );

        $matcher = new UrlMatcher($loader->load('.'), (new RequestContext())->fromRequest($request));

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));
        $dispatcher->addSubscriber(new SessionListener($this->containerBuilder));
        $dispatcher->addListener(
            KernelEvents::EXCEPTION,
            static function (ExceptionEvent $event) {
                if ($event->getThrowable() instanceof NotFoundHttpException) {
                    $event->setResponse(new Response('not found: 404.', 404));
                }

                if (!$event->hasResponse()) {
                    $event->setResponse(new Response($event->getThrowable()->getMessage(), 500));
                }
            }
        );

        $kernel = new HttpKernel(
            $dispatcher,
            new ContainerControllerResolver($this->containerBuilder, $this->logger),
            new RequestStack(),
            new ArgumentResolver()
        );

        $response = $kernel->handle($request);
        $response->send();

        $kernel->terminate($request, $response);
    }
}
