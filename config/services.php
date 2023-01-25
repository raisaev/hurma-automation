<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Hurma\Client as Hurma;
use App\Kernel;
use App\LoggerFactory;
use App\Web\Controller\IndexController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver;
use App\Hurma\SheetRecordFactory;

return static function (ContainerConfigurator $configurator): void {
    $configurator->parameters()
        ->set('project.name', env('APP_NAME')->string())
        ->set('project.root_dir', dirname(__DIR__) . '/');

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->instanceof(Command::class)->tag('console.command');
    $services->instanceof(IndexController::class)->tag('controller.service_arguments');

    $services->load('App\\', '../src/*');

    // ----------------------------------------

    $services->get(\App\Web\Application::class)->public();
    //$services->get(\App\Console\Application::class)->public();

    // ----------------------------------------

    $services->get(Kernel::class)
        ->arg('$rootDir', param('project.root_dir'));

    $services->get(IndexController::class)
        ->arg('$defaultSpreadsheetId', env('GOOGLE_DEFAULT_SPREADSHEET_ID')->string())
        ->arg('$defaultSheetName', env('GOOGLE_DEFAULT_SHEET_NAME')->string())
        ->arg('$defaultRange', env('GOOGLE_DEFAULT_SHEET_RANGE')->string());

    $services->get(Hurma::class)
        ->arg('$url', env('HURMA_URL')->string())
        ->arg('$login', env('HURMA_LOGIN'))
        ->arg('$password', env('HURMA_PASSWORD'))
        ->arg('$webDriverUrl', env('CHROME_WEB_DRIVER_URL'));

    $services->get(SheetRecordFactory::class)
        ->arg('$from', env('FIELD_FROM')->string())
        ->arg('$name', env('FIELD_NAME')->string())
        ->arg('$source', env('FIELD_SOURCE')->string())
        ->arg('$coins', env('FIELD_COINS')->string())
        ->arg('$comment', env('FIELD_COMMENT')->string())
        ->arg('$status', env('FIELD_STATUS')->string());

    $services->set(LoggerInterface::class)
        ->factory([LoggerFactory::class, 'create'])
            ->arg('$name', param('project.name'))
            ->arg('$logLevel', env('LOG_LEVEL'))
            ->arg('$logStream', env('LOG_STREAM'));

    $services->alias('logger', LoggerInterface::class)->public();
    $services->set('session', Session::class)->public();

    // ----------------------------------------

    $services
        ->set('argument_resolver.service', ServiceValueResolver::class)
        ->args([
            abstract_arg('service locator, set in RegisterControllerArgumentLocatorsPass'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => -50]);
};
