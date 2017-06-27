<?php
namespace NYPL\Services\Test\Mocks;

use NYPL\Services\ServiceContainer;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class MockService
 *
 * @package NYPL\Services\Test\Mocks
 */
class MockService
{
    public static $mockEnvironment;
    public static $mockContainer;

    /**
     * Initialize mock Slim request.
     *
     * @param array $serverParams
     * @param array $data
     */
    public static function setMockEnvironment(array $serverParams = [], array $data = [])
    {
        if (empty($serverParams)) {
            $serverParams = [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/',
            ];
        }

        self::$mockEnvironment = Environment::mock($serverParams);

        if (!empty($data)) {
            $_POST = $data;
        }

        self::$mockContainer['request'] = Request::createFromEnvironment(self::$mockEnvironment);
        self::$mockContainer['response'] = new Response();
    }

    /**
     * Set a concrete Container class to pass to controllers.
     *
     * TODO create true mock class.
     */
    public static function setMockContainer()
    {
        self::$mockContainer = new ServiceContainer();
    }

    /**
     * @return mixed
     */
    public static function getMockContainer()
    {
        return self::$mockContainer;
    }
}
