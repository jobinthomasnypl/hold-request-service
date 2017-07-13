<?php
namespace NYPL\Services;

use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
use NYPL\Starter\JobManager;
use NYPL\Starter\CacheModel\BaseJob\Job;
use NYPL\Starter\CacheModel\JobNotice\JobNoticeCreated;
use NYPL\Starter\CacheModel\JobStatus;
use NYPL\Starter\JobClient;
use NYPL\Starter\JobStatus\JobStatusSuccess;
use Ramsey\Uuid\Uuid;

/**
 * Class JobService
 *
 * @package NYPL\Services
 */
class JobService
{
    const JOB_SUCCESS_MESSAGE = 'Job finished successfully for hold request.';

    const JOB_FAILURE_MESSAGE = 'Job finished unsuccessfully for hold request.';

    /**
     * @var string
     */
    public static $jobId;

    /**
     * @var JobClient
     */
    public static $jobClient;

    /**
     * @var JobStatus
     */
    public static $jobStatus;

    /**
     * @var JobStatusSuccess
     */
    public static $jobStatusSuccess;

    /**
     * @var JobNoticeCreated
     */
    public static $jobNotice;

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
     * @return JobClient
     */
    public static function getJobClient()
    {
        return self::$jobClient;
    }

    /**
     * @param JobClient $jobClient
     */
    public static function setJobClient($jobClient)
    {
        self::$jobClient = $jobClient;
    }

    /**
     * @return JobStatus
     */
    public static function getJobStatus()
    {
        return self::$jobStatus;
    }

    /**
     * @param JobStatus $jobStatus
     */
    public static function setJobStatus(JobStatus $jobStatus)
    {
        self::$jobStatus = $jobStatus;
    }

    /**
     * @return JobStatusSuccess
     */
    public static function getJobStatusSuccess()
    {
        return self::$jobStatusSuccess;
    }

    /**
     * @param JobStatusSuccess $jobStatusSuccess
     */
    public static function setJobStatusSuccess(JobStatusSuccess $jobStatusSuccess)
    {
        self::$jobStatusSuccess = $jobStatusSuccess;
    }

    /**
     * @return JobNoticeCreated
     */
    public static function getJobNotice()
    {
        return self::$jobNotice;
    }

    /**
     * @param JobNoticeCreated $jobNotice
     */
    public static function setJobNotice(JobNoticeCreated $jobNotice)
    {
        self::$jobNotice = $jobNotice;
    }

    /**
     * @param bool $useJobManager
     * @throws \NYPL\Starter\APIException
     * @return string
     */
    public static function generateJobId(bool $useJobManager = true): string
    {
        if ($useJobManager) {
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
     * Provide a UUID in lieu of creating a job service object.
     */
    protected static function generateRandomId()
    {
        self::setJobId(Uuid::uuid4()->toString());
    }

    /**
     * Instantiate requisite job service elements.
     */
    protected static function initializeJobClient()
    {
        self::setJobClient(new JobClient());
        self::setJobStatus(new JobStatus());
        self::setJobStatusSuccess(new JobStatusSuccess());
    }

    /**
     * @param HoldRequest $holdRequest
     * @param string|null $message
     */
    public static function beginJob(HoldRequest $holdRequest, $message = null)
    {
        self::initializeJobClient();
        self::buildJobNotice($holdRequest->getRawData(), $message);
        self::getJobStatus()->setNotice(self::getJobNotice());

        self::getJobClient()->startJob(
            new Job(['id' => $holdRequest->getJobId()]),
            self::getJobStatus()
        );
    }

    /**
     * @param HoldRequest $holdRequest
     */
    public static function finishJob(HoldRequest $holdRequest)
    {
        self::initializeJobClient();
        $data = $holdRequest->getRawData();

        try {
            if ($holdRequest->isSuccess()) {
                self::buildJobNotice($data, self::JOB_SUCCESS_MESSAGE);
                self::getJobStatusSuccess()->setNotice(self::getJobNotice());

                self::getJobClient()->success(
                    new Job(['id' => $holdRequest->getJobId()]),
                    self::getJobStatusSuccess()
                );
            } else {
                self::buildJobNotice($data, self::JOB_FAILURE_MESSAGE);
                self::getJobStatus()->setNotice(self::getJobNotice());

                self::getJobClient()->failure(
                    new Job(['id' => $holdRequest->getJobId()]),
                    self::getJobStatus()
                );
            }
        } catch (\Exception $exception) {
            APILogger::addInfo('Job threw an exception. ' . $exception->getMessage());
        }
    }

    /**
     * @param array       $data
     * @param string|null $notice
     */
    protected static function buildJobNotice(array $data, $notice = null)
    {
        $jobNotice = new JobNoticeCreated();
        $jobNotice->setData($data);
        $jobNotice->setText($notice);

        self::setJobNotice($jobNotice);
    }
}
