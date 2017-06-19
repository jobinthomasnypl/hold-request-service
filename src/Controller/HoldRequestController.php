<?php
namespace NYPL\Services\Controller;

use NYPL\Services\ServiceController;
use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Services\Model\HoldRequestResponse;
use NYPL\Starter\APIException;
use NYPL\Starter\Filter;
use Ramsey\Uuid\Uuid;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class HoldRequestController
 *
 * @package NYPL\Services\Controller
 */
class HoldRequestController extends ServiceController
{
    /**
     * @SWG\Post(
     *     path="/v0.1/hold-requests",
     *     summary="Create new hold request",
     *     tags={"holds-service"},
     *     operationId="createHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="NewHoldRequest",
     *         in="body",
     *         description="",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/NewHoldRequest")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api"}
     *         }
     *     }
     * )
     *
     * @return Response
     * @throws APIException
     */
    public function createHoldRequest()
    {
        try {
            if (!$this->hasWriteRequestScope()) {
                return $this->invalidScopeResponse();
            }

            $data = $this->getRequest()->getParsedBody();

            $data['jobId'] = Uuid::uuid4()->toString();
            $data['success'] = $data['processed'] = false;

            $holdRequest = new HoldRequest($data);

            $holdRequest->create();

            return $this->getResponse()->withJson(
                new HoldRequestResponse($holdRequest)
            );
        } catch(\Exception $e) {
            throw new APIException(
                'An error occurred',
                $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests",
     *     summary="Get a list of hold requests",
     *     tags={"holds-service"},
     *     operationId="getHoldRequests",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="patron",
     *         in="query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="record",
     *         in="query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="processed",
     *         in="query",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="nyplSource",
     *         in="query",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api"}
     *         }
     *     }
     * )
     *
     * @return Response
     * @throws APIException
     */
    public function getHoldRequests()
    {
        try {
            if (!$this->hasReadRequestScope()) {
                return $this->invalidScopeResponse();
            }

            return  $this->getDefaultReadResponse(
                new HoldRequest(),
                new HoldRequestResponse(),
                new Filter('patron', 'processed', 'record')
            );
        } catch(\Exception $e) {
            throw new APIException(
                'An error occurred',
                $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Get a single hold request",
     *     tags={"holds-service"},
     *     operationId="getHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Parameter(
     *         name="nyplSource",
     *         in="path",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api"}
     *         }
     *     }
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws APIException
     */
    public function getHoldRequest(Request $request, Response $response, array $args)
    {
        try {
            if (!$this->hasReadRequestScope()) {
                return $this->invalidScopeResponse();
            }

            return  $this->getDefaultReadResponse(
                new HoldRequest(),
                new HoldRequestResponse(),
                new Filter(null, null, false, $args['id'])
            );
        } catch(\Exception $e) {
            throw new APIException(
                'An error occurred',
                $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @SWG\Put(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Update a hold request",
     *     tags={"holds-service"},
     *     operationId="updateHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of Hold Request",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Parameter(
     *         name="HoldRequest",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/HoldRequest")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestResponse")
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Not found",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="Generic server error",
     *         @SWG\Schema(ref="#/definitions/HoldRequestErrorResponse")
     *     ),
     *     security={
     *         {
     *             "api_auth": {"openid offline_access api"}
     *         }
     *     }
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     * @throws APIException
     */
    public function updateHoldRequest(Request $request, Response $response, array $args)
    {
        try {
            if (!$this->hasWriteRequestScope()) {
                return $this->invalidScopeResponse();
            }

            $holdRequest = new HoldRequest();

            $holdRequest->addFilter(new Filter('id', $args['id']));

            $holdRequest->update(
                $this->getRequest()->getParsedBody()
            );

            return $this->getResponse()->withJson(
                new HoldRequestResponse($holdRequest)
            );
        } catch(\Exception $e) {
            throw new APIException(
                'An error occurred',
                $e->getMessage(),
                $e->getCode()
            );
        }
    }
}
