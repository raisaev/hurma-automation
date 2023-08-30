<?php

declare(strict_types=1);

namespace App;

use App\Web\Application as Web;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use App\Console\Application as Console;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;

class KernelRunner
{
    public static function boot(): ContainerBuilder
    {
        $env = new Dotenv();
        $env->usePutenv();
        $env->bootEnv(dirname(__DIR__) . '/.env');

        $containerBuilder = new ContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.php');

        try {
            $loader->load('services.local.php');
        } catch (FileLocatorFileNotFoundException) {
            //ignored
        }

        return $containerBuilder;
    }

    public static function console(): int
    {
        $container = self::boot();
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->compile(true);

        $app = new Console($container);
        $app->setCatchExceptions(false);

        return $app->run();
    }

    public static function http(): int
    {
        $container = self::boot();
        $container->addCompilerPass(new RegisterControllerArgumentLocatorsPass());
        $container->compile(true);

        /** @var Web $app */
        $app = $container->get(Web::class);
        $app->run(Request::createFromGlobals());

        return 0;
    }
}
