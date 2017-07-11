<?php
namespace NYPL\Services\Controller;

use NYPL\Services\JobService;
use NYPL\Services\ServiceController;
use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Services\Model\Response\HoldRequestResponse;
use NYPL\Services\Model\Response\HoldRequestErrorResponse;
use NYPL\Services\Model\Response\HoldRequestsResponse;
use NYPL\Starter\APIException;
use NYPL\Starter\Config;
use NYPL\Starter\Filter;
use NYPL\Starter\ModelSet;
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
     *     tags={"hold-requests"},
     *     operationId="createHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="NewHoldRequest",
     *         in="body",
     *         description="Request object based on the included data model",
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
        $data = $this->getRequest()->getParsedBody();

        if (strtolower($data['requestType']) === 'edd' && !$data['docDeliveryData']) {
            return $this->getResponse()->withJson(
                new HoldRequestErrorResponse(
                    500,
                    'invalid-edd',
                    'EDD request is missing all details.',
                    new APIException('An error occurred', $data)
                )
            )->withStatus(500);
        }

        $data['jobId'] = JobService::generateJobId(Config::get('USE_JOB_SERVICE'));
        $data['success'] = $data['processed'] = false;

        $holdRequest = new HoldRequest($data);

        $holdRequest->create();

        return $this->getResponse()->withJson(
            new HoldRequestResponse($holdRequest)
        );
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests",
     *     summary="Get a list of hold requests",
     *     tags={"hold-requests"},
     *     operationId="getHoldRequests",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="patron",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="ID of patron provided by ILS"
     *     ),
     *     @SWG\Parameter(
     *         name="record",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="ID of record provided by ILS"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(ref="#/definitions/HoldRequestsResponse")
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
        return  $this->getDefaultReadResponse(
            new ModelSet(new HoldRequest()),
            new HoldRequestsResponse(),
            null,
            ['patron', 'record']
        );
    }

    /**
     * @SWG\Get(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Get a single hold request",
     *     tags={"hold-requests"},
     *     operationId="getHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="string",
     *         format="string",
     *         description="ID of hold request"
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
        return  $this->getDefaultReadResponse(
            new HoldRequest(),
            new HoldRequestResponse(),
            new Filter(null, null, false, $args['id'])
        );
    }

    /**
     * @SWG\Patch(
     *     path="/v0.1/hold-requests/{id}",
     *     summary="Update a hold request",
     *     tags={"hold-requests"},
     *     operationId="updateHoldRequest",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         description="ID of hold request",
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
        $holdRequest = new HoldRequest();

        $holdRequest->addFilter(new Filter('id', $args['id']));

        $holdRequest->update(
            $this->getRequest()->getParsedBody()
        );

        return $this->getResponse()->withJson(
            new HoldRequestResponse($holdRequest)
        );
    }
}
