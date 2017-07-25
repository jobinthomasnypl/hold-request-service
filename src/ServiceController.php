<?php
namespace NYPL\Services;

use Firebase\JWT\JWT;
use NYPL\Services\Model\Response\HoldRequestErrorResponse;
use NYPL\Starter\Controller;
use Slim\Container;

/**
 * Class ServiceController
 *
 * @package NYPL\Services
 */
class ServiceController extends Controller
{
    const READ_REQUEST_SCOPE = 'read:hold_request';

    const WRITE_REQUEST_SCOPE = 'write:hold_request';

    const GLOBAL_REQUEST_SCOPE = 'readwrite:hold_request';

    /**
     * @var bool
     */
    public $useJobService;

    /**
     * @var Container
     */
    public $container;

    /**
     * Controller constructor.
     *
     * @param \Slim\Container $container
     * @param int $cacheSeconds
     */
    public function __construct(Container $container, int $cacheSeconds = 0)
    {
        $this->setResponse($container->get('response'));
        $this->setRequest($container->get('request'));

        $this->addCacheHeader($cacheSeconds);

        $this->initializeContentType();

        $this->initializeIdentityHeader();

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
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return boolean
     */
    public function isUseJobService(): bool
    {
        return $this->useJobService;
    }

    /**
     * @param boolean $useJobService
     */
    public function setUseJobService(bool $useJobService)
    {
        $this->useJobService = $useJobService;
    }

    public function patronIsAuthorized()
    {
        $requestIdentity = $this->getPatronFromRequest();
        $tokenIdentity = $this->getPatronFromToken();

        return strcmp($requestIdentity, $tokenIdentity) === 0;
    }

    protected function hasPatronIdentifier()
    {
        $params = $this->getRequest()->getQueryParams();

        return isset($params['patron']);
    }

    public function isRequestAuthorized()
    {
        if ($this->getRequest()->getMethod() === 'GET') {
            $hasScopeAccess = $this->hasReadRequestScope();
        } else {
            $hasScopeAccess = $this->hasWriteRequestScope();
        }

        return $hasScopeAccess || $this->patronIsAuthorized();
    }

    /**
     * @return bool
     */
    public function hasReadRequestScope(): bool
    {
        return (bool) in_array(
            self::READ_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        )
        || $this->hasGlobalRequestScope();
    }

    /**
     * @return bool
     */
    public function hasWriteRequestScope(): bool
    {
        return (bool) in_array(
            self::WRITE_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        )
        || $this->hasGlobalRequestScope();
    }

    /**
     * @return bool
     */
    protected function hasGlobalRequestScope(): bool
    {
        return (bool) in_array(
            self::GLOBAL_REQUEST_SCOPE,
            (array) $this->getIdentityHeader()->getScopes()
        );
    }

    protected function getPatronFromRequest()
    {
        $payload = json_decode($this->getRequest()->getBody());
        return $payload->patron;
    }

    protected function getPublicKey()
    {
        return file_get_contents(__DIR__ . '/../config/pubkey.pem');
    }

    protected function getPatronFromToken()
    {
        $token = $this->getIdentityHeader()->getToken();
        $decoded = JWT::decode($token, $this->getPublicKey(), array('RS256'));

        return $decoded->sub;
    }

    /**
     * @return \Slim\Http\Response
     */
    public function invalidScopeResponse()
    {
        return $this->getResponse()->withJson(
            new HoldRequestErrorResponse(
                '403',
                'invalid-scope',
                'Client does not have sufficient privileges.'
            )
        )->withStatus(403);
    }

    /**
     * @return \Slim\Http\Response
     */
    public function invalidRequestResponse()
    {
        return $this->getResponse()->withJson(
            new HoldRequestErrorResponse(
                '403',
                'invalid-request',
                'Client does not have sufficient privileges.'
            )
        )->withStatus(403);
    }
}
