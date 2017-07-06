<?php
namespace NYPL\Services;

use NYPL\Starter\Service;
use NYPL\Starter\Config;
use NYPL\Starter\ErrorHandler;
use NYPL\Services\Controller\HoldRequestController;

require __DIR__ . '/vendor/autoload.php';

try {
    Config::initialize(__DIR__);

    $container = new ServiceContainer();

    $service = new Service($container);

    $service->get('/docs/hold-requests', Swagger::class);

    $service->post('/api/v0.1/hold-requests', HoldRequestController::class . ':createHoldRequest');

    $service->get('/api/v0.1/hold-requests', HoldRequestController::class . ':getHoldRequests');

    $service->get('/api/v0.1/hold-requests/{id}', HoldRequestController::class . ':getHoldRequest');

    $service->patch('/api/v0.1/hold-requests/{id}', HoldRequestController::class . ':updateHoldRequest');

    $service->run();
} catch (\Exception $exception) {
    ErrorHandler::processShutdownError($exception->getMessage(), $exception);
}
