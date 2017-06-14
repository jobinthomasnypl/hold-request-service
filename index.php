<?php
namespace NYPL\Services;

use NYPL\Starter\Service;
use NYPL\Starter\Config;
use NYPL\Starter\ErrorHandler;
use NYPL\Services\Controller\HoldRequestController;
use NYPL\Services\Controller\HoldRequestResultController;

require __DIR__ . '/vendor/autoload.php';

try {
    Config::initialize(__DIR__);

    $container = new ServiceContainer();

    $service = new Service($container);

    $service->get('/docs', Swagger::class);

    $service->post('/api/v0.2/hold-requests', HoldRequestController::class . ':createHoldRequest');

    $service->get('/api/v0.2/hold-requests', HoldRequestController::class . ':getHoldRequests');

    $service->get('/api/v0.2/hold-requests/{id}', HoldRequestController::class . ':getHoldRequest');

    $service->put('/api/v0.2/hold-requests/{id}', HoldRequestController::class . ':updateHoldRequest');

    $service->post('/api/v0.2/hold-requests/{id}/result', HoldRequestResultController::class . ':processResult');

    $service->run();
} catch (\Exception $exception) {
    ErrorHandler::processShutdownError($exception->getMessage(), $exception);
}