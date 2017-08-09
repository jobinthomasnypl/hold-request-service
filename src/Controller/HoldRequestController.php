<?php
namespace NYPL\Services\Controller;

use NYPL\Services\Filter\DateQueryFilter;
use NYPL\Services\JobService;
use NYPL\Services\ServiceController;
use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Services\Model\Response\HoldRequestResponse;
use NYPL\Services\Model\Response\HoldRequestErrorResponse;
use NYPL\Services\Model\Response\HoldRequestsResponse;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
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
     */
    public function createHoldRequest()
    {
        try {
            $data = $this->getRequest()->getParsedBody();

            $data['jobId'] = JobService::generateJobId($this->isUseJobService());
            $data['success'] = $data['processed'] = false;

            $holdRequest = new HoldRequest($data);

            try {
                $holdRequest->validateData();
            } catch (APIException $exception) {
                return $this->invalidRequestResponse($exception);
            }

            $holdRequest->create();

            if ($this->isUseJobService()) {
                APILogger::addDebug('Initiating job via Job Service API.', ['jobID' => $holdRequest->getJobId()]);
                JobService::beginJob($holdRequest, 'Job started for hold request.');
            }

            return $this->getResponse()->withJson(
                new HoldRequestResponse($holdRequest)
            );
        } catch (\Exception $exception) {
            $errorType = 'create-hold-request-error';
            $errorMsg = 'Unable to create hold request due to a problem with dependent services.';
            return $this->processException($errorType, $errorMsg, $exception, $this->getRequest());
        }
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
     *     @SWG\Parameter(
     *         name="processed",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="Process status of a hold request."
     *     ),
     *     @SWG\Parameter(
     *         name="createdDate",
     *         in="query",
     *         required=false,
     *         type="string",
     *         description="Creation date of a hold request. (Format: YYYY-MM-DD)"
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
     */
    public function getHoldRequests()
    {
        $dateFilter = null;
        if ($this->getRequest()->getParam('createdDate')) {
            $dateFilter = new DateQueryFilter(
                'createdDate',
                $this->getRequest()->getParam('createdDate'),
                false,
                '',
                'LIKE'
            );
        }

        return  $this->getDefaultReadResponse(
            new ModelSet(new HoldRequest()),
            new HoldRequestsResponse(),
            $dateFilter,
            ['patron', 'record', 'processed']
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
     */
    public function updateHoldRequest(Request $request, Response $response, array $args)
    {
        try {
            $holdRequest = new HoldRequest();
            $holdRequest->addFilter(new Filter('id', $args['id']));
            $holdRequest->read();

            $holdRequest->update(
                $this->getRequest()->getParsedBody()
            );

            if ($this->isUseJobService()) {
                APILogger::addDebug('Updating an existing job.', ['jobID' => $holdRequest->getJobId()]);
                JobService::finishJob($holdRequest);
            }

            return $this->getResponse()->withJson(new HoldRequestResponse($holdRequest));
        } catch (\Exception $exception) {
            $errorType = 'update-hold-request-error';
            $errorMsg = 'Unable to update hold request. ' . $exception->getMessage();
            return $this->processException($errorType, $errorMsg, $exception, $request);
        }
    }

    /**
     * @param string     $errorType
     * @param string     $errorMessage
     * @param \Exception $exception
     * @param Request    $request
     * @return \Slim\Http\Response
     */
    protected function processException($errorType, $errorMessage, \Exception $exception, Request $request)
    {
        APILogger::addInfo(get_class($exception) . ': ' . $exception->getMessage(), [$request->getAttributes()]);

        $statusCode = 500;

        if ($exception instanceof APIException) {
            $statusCode = $exception->getHttpCode();
        }

        $errorResp = new HoldRequestErrorResponse(
            $statusCode,
            $errorType,
            $errorMessage,
            $exception
        );
        $errorResp->setError($errorResp->translateException($exception));
        return $this->getResponse()->withJson($errorResp)->withStatus($statusCode);
    }
}
