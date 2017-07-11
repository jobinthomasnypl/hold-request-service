<?php
namespace NYPL\Services;

use Guzzle\Http\Client;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
use NYPL\Starter\JobManager;
use Ramsey\Uuid\Uuid;

/**
 * Class JobService
 *
 * @package NYPL\Services
 */
class JobService
{
    /**
     * @var string
     */
    public static $jobId;

    /**
     * @return string|null
     */
    protected static function getJobId()
    {
        return self::$jobId;
    }

    /**
     * @param string $jobId
     */
    protected static function setJobId(string $jobId)
    {
        self::$jobId = $jobId;
    }

    /**
     * @param bool $useJobManager
     * @throws \NYPL\Starter\APIException
     * @return string
     */
    public static function generateJobId(bool $useJobManager = true): string
    {
        if ($useJobManager) {
            APILogger::addInfo('Initiating new job via Job API service.');

            try {
                $jobId = JobManager::createJob();
                self::setJobId($jobId);
            } catch (\Exception $exception) {
                APILogger::addError('Not able to communicate with the Jobs Service API.');
                throw new APIException('Jobs Service failed to generate an ID.');
            }
        }

        if (!self::getJobId()) {
            self::generateRandomId();
            APILogger::addInfo('No job started. Job ID returned as UUID.');
        }

        return self::getJobId();
    }

    /**
     * @param bool $status
     * @return \Guzzle\Http\Message\Response
     */
    public static function updateJobStatus(bool $status)
    {
        if (!$status) {
            return self::setJobAsFailure();
        }

        return self::setJobAsSuccess();
    }

    /**
     * @return \Guzzle\Http\Message\Response
     */
    public static function getJob()
    {
        $job = self::jobClient([
            'base_uri' => self::fetchJobUrl(),
            'timeout' => 10
        ]);

        $response = $job->get();

        return $response->getResponse();
    }

    protected static function generateRandomId()
    {
        self::setJobId(Uuid::uuid4()->toString());
    }

    protected static function jobClient(array $params)
    {
        return new Client($params);
    }

    /**
     * @return string
     */
    protected function fetchJobUrl()
    {
        return JobManager::getJobUrl(self::getJobId());
    }

    /**
     * On failure, set the appropriate flag in the Jobs API.
     */
    protected static function setJobAsFailure()
    {
        $request = self::jobClient()->put(self::fetchJobUrl() . '/failure');

        return $request->send();
    }

    /**
     * On success, set the appropriate flag in the Jobs API.
     */
    protected static function setJobAsSuccess()
    {
        $request = self::jobClient()->put(self::fetchJobUrl() . '/success');

        return $request->send();
    }
}
