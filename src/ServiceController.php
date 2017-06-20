<?php
namespace NYPL\Services;

use NYPL\Starter\Config;
use NYPL\Starter\Controller;
use Slim\Container;

/**
 * Class ServiceController
 *
 * @package NYPL\Services
 */
class ServiceController extends Controller
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @var bool
     */
    public $useJobManager;

    /**
     * Controller constructor.
     *
     * @param \Slim\Container $container
     * @param int $cacheSeconds
     */
    public function __construct(Container $container, $cacheSeconds = 0)
    {
        $this->setResponse($container->get('response'));
        $this->setRequest($container->get('request'));

        $this->addCacheHeader($cacheSeconds);

        $this->initializeContentType();

        $this->initializeIdentityHeader();

        $this->setUseJobManager(Config::get('USE_JOB_MANAGER'));

        parent::__construct($this->request, $this->response, $cacheSeconds);
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getUseJobManager()
    {
        return $this->useJobManager;
    }

    /**
     * @param mixed $useJobManager
     */
    public function setUseJobManager($useJobManager)
    {
        $this->useJobManager = $useJobManager;
    }
}
