<?php
namespace NYPL\Services\Test;

use NYPL\Services\Controller\HoldRequestController;
use NYPL\Services\ServiceContainer;
use NYPL\Services\Test\Mocks\MockConfig;
use NYPL\Services\Test\Mocks\MockService;
use PHPUnit\Framework\TestCase;

class ServiceControllerTest extends TestCase
{
    public $mockContainer;

    public function setUp()
    {
        parent::setUp();
        MockConfig::initialize(__DIR__ . '/../');
        MockService::setMockContainer();
        $this->mockContainer = MockService::getMockContainer();

        $this->fakeHoldRequestController = new class(MockService::getMockContainer(), 0) extends HoldRequestController {

            public $container;
            public $cacheSeconds;

            public function __construct(\Slim\Container $container, $cacheSeconds)
            {
                $this->container = new ServiceContainer();
                parent::__construct($container, $cacheSeconds);
            }

            public function initializeIdentityHeader()
            {
                $request = $this->container['request'];
                $params = [
                    'X-NYPL-Identity' =>
                        '{"token":"blah","identity":{"sub":null,"scope":"openid offline_access api read:hold_request"}}'
                ];

                foreach ($params as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
                return $request;
            }
        };
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->fakeHoldRequestController);
    }

    /**
     * @covers NYPL\Services\ServiceController::invalidScopeResponse
     */
    public function testIfScopeIsValid()
    {
        $controller = $this->fakeHoldRequestController;
        $request = $controller->initializeIdentityHeader();

        $header = json_decode($request->getHeader('X-NYPL-Identity')[0]);
        $identity = $header->identity;

        self::assertContains('read:hold_request', $identity->scope);
    }

    /**
     * @covers NYPL\Services\ServiceController::invalidScopeResponse
     */
    public function testIfScopeIsInvalid()
    {
        $controller = $this->fakeHoldRequestController;
        $request = $controller->initializeIdentityHeader();

        $header = json_decode($request->getHeader('X-NYPL-Identity')[0]);
        $identity = $header->identity;

        self::assertNotContains('write:hold_request', $identity->scope);
    }
}
