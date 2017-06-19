<?php
namespace NYPL\Services\Model\HoldRequest;

use NYPL\Services\Model\HoldRequestModel;
use NYPL\Starter\Model\LocalDateTime;
use NYPL\Starter\Model\ModelInterface\MessageInterface;
use NYPL\Starter\Model\ModelInterface\ReadInterface;
use NYPL\Starter\Model\ModelTrait\DBCreateTrait;
use NYPL\Starter\Model\ModelTrait\DBReadTrait;
use NYPL\Starter\Model\ModelTrait\DBUpdateTrait;

/**
 * @SWG\Definition(title="HoldRequest", type="object")
 *
 * @package NYPL\Services\Model\HoldRequest
 */
class HoldRequest extends NewHoldRequest implements MessageInterface, ReadInterface
{
    use DBCreateTrait, DBReadTrait, DBUpdateTrait;

    /**
     * @SWG\Property(example="229")
     * @var int
     */
    public $id;

    /**
     * @SWG\Property(example="901bdd1d-bd8f-4310-ba31-7f13a55877fd")
     * @var string
     */
    public $jobId;

    /**
     * @SWG\Property(example="2016-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $createdDate;

    /**
     * @SWG\Property(example="2016-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $updatedDate;

    /**
     * @SWG\Property(example=true)
     * @var bool
     */
    public $success;

    /**
     * @SWG\Property(example=false)
     * @var bool
     */
    public $processed;

    /**
     * @SWG\Property(example="NW")
     * @var string
     */
    public $deliveryLocation;

    /**
     * @return array
     */
    public function getSchema()
    {
        return [
            "name" => "HoldRequest",
            "type" => "record",
            "fields" => [
                ["name" => "id", "type" => "int"],
                ["name" => "jobId", "type" => "string"],
                ["name" => "patron", "type" => "string"],
                ["name" => "nyplSource", "type" => "string"],
                ["name" => "createdDate", "type" => ["string", "null"]],
                ["name" => "updatedDate", "type" => ["string", "null"]],
                ["name" => "success", "type" => "boolean"],
                ["name" => "processed", "type" => "boolean"],
                ["name" => "requestType", "type" => "string"],
                ["name" => "recordType", "type" => "string"],
                ["name" => "record", "type" => "string"],
                ["name" => "pickupLocation", "type" => ["string", "null"]],
                ["name" => "neededBy", "type" => ["string", "null"]],
                ["name" => "numberOfCopies", "type" => ["int", "null"]],
                ["name" => "deliveryLocation", "type" => ["string", "null"]],
                ["name" => "docDeliveryData", "type" => [
                    "null",
                    ["name" => "docDeliveryData", "type" => "record", "fields" => [
                        ["name" => "emailAddress", "type" => ["string", "null"]],
                        ["name" => "chapterTitle", "type" => ["string", "null"]],
                        ["name" => "volume", "type" => ["string", "null"]],
                        ["name" => "issue", "type" => ["string", "null"]],
                        ["name" => "startPage", "type" => ["string", "null"]],
                        ["name" => "endPage", "type" => ["string", "null"]],
                    ]]
                ]],
            ]
        ];
    }

    /**
     * @return string
     */
    public function getSequenceId()
    {
        return 'hold_request_id_seq';
    }

    /**
     * @return array
     */
    public function getIdFields()
    {
        return ['id'];
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @return LocalDateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param LocalDateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @param string $createdDate
     *
     * @return LocalDateTime
     */
    public function translateCreatedDate($createdDate = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $createdDate);
    }

    /**
     * @return LocalDateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param LocalDateTime $updatedDate
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * @param string $updatedDate
     *
     * @return LocalDateTime
     */
    public function translateUpdatedDate($updatedDate = '')
    {
        return new LocalDateTime(LocalDateTime::FORMAT_DATE_TIME_RFC, $updatedDate);
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->success = boolval($success);
    }

    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @param boolean $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = boolval($processed);
    }

    /**
     * @return string
     */
    public function getDeliveryLocation()
    {
        return $this->deliveryLocation;
    }

    /**
     * @param string $deliveryLocation
     */
    public function setDeliveryLocation($deliveryLocation)
    {
        $this->deliveryLocation = $deliveryLocation;
    }
}
