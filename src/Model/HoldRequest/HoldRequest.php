<?php
namespace NYPL\Services\Model\HoldRequest;

use NYPL\Services\Model\ElectronicDocumentData;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
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
     * @SWG\Property(example="2018-01-07T02:32:51Z", type="string")
     * @var LocalDateTime
     */
    public $createdDate;

    /**
     * @SWG\Property(example="2018-01-07T02:32:51Z", type="string")
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
     * Returns a valid Avro 1.8.1 schema structure.
     *
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
                ["name" => "createdDate", "type" => ["null", "string"]],
                ["name" => "updatedDate", "type" => ["null", "string"]],
                ["name" => "success", "type" => "boolean"],
                ["name" => "processed", "type" => "boolean"],
                ["name" => "requestType", "type" => ["null", "string"]],
                ["name" => "recordType", "type" => "string"],
                ["name" => "record", "type" => "string"],
                ["name" => "pickupLocation", "type" => ["null", "string"]],
                ["name" => "neededBy", "type" => ["null", "string"]],
                ["name" => "numberOfCopies", "type" => ["null", "int"]],
                ["name" => "deliveryLocation", "type" => ["null", "string"]],
                ["name" => "docDeliveryData", "default" => null, "type" => [
                    "null",
                    ["name" => "docDeliveryData", "type" => "record", "fields" => [
                        ["name" => "emailAddress", "type" => "string"],
                        ["name" => "chapterTitle", "type" => "string"],
                        ["name" => "startPage", "type" => "string"],
                        ["name" => "endPage", "type" => "string"],
                        ["name" => "author", "type" => ["null", "string"]],
                        ["name" => "volume", "type" => ["null", "string"]],
                        ["name" => "issue", "type" => ["null", "string"]],
                        ["name" => "requestNotes", "type" => ["null", "string"]],
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
     * @param int|string $id
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
     * @param string $jobId
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
    public function setSuccess(bool $success)
    {
        $this->success = $success;
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
    public function setProcessed(bool $processed)
    {
        $this->processed = $processed;
    }

    /**
     * @throws \NYPL\Starter\APIException
     */
    public function validateData()
    {
        APILogger::addDebug('Validating data for hold request.', $this->getRawData());

        if ($this->getRequestType() != 'edd' && (!$this->getPickupLocation() && !$this->getDeliveryLocation())) {
            APILogger::addDebug('No pickup/delivery location provided.', $this->getRawData());
            throw new APIException('Missing pickupLocation and deliveryLocation values. One or both must be set.');
        }

        if ($this->getRequestType() === 'edd'
            && !$this->docDeliveryData instanceof ElectronicDocumentData) {
            APILogger::addDebug('EDD object not instantiated.', $this->getRawData());
            throw new APIException('EDD request is missing all details.');
        }
    }
}
