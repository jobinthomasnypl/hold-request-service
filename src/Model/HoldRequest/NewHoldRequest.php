<?php
namespace NYPL\Services\Model\HoldRequest;

use NYPL\Services\Model\ElectronicDocumentData;
use NYPL\Services\Model\HoldRequestModel;
use NYPL\Starter\APIException;
use NYPL\Starter\APILogger;
use NYPL\Starter\Model\ModelTrait\TranslateTrait;

/**
 * @SWG\Definition(title="NewHoldRequest", type="object")
 *
 * @package NYPL\Services\Model\HoldRequest
 */
class NewHoldRequest extends HoldRequestModel
{
    use TranslateTrait;

    const VALID_REQUEST_TYPES = ['hold', 'edd'];

    /**
     * @return array
     */
    public function getSchema()
    {
        return [
            "name" => "NewHoldRequest",
            "type" => "record",
            "fields" => [
                ["name" => "patron", "type" => "string"],
                ["name" => "nyplSource", "type" => "string"],
                ["name" => "requestType", "type" => "string"],
                ["name" => "recordType", "type" => ["string", "null"]],
                ["name" => "record", "type" => "string"],
                ["name" => "pickupLocation", "type" => ["string", "null"]],
                ["name" => "neededBy", "type" => ["string", "null"]],
                ["name" => "numberOfCopies", "type" => ["int", "null"]],
                ["name" => "docDeliveryData", "type" => [
                    "null",
                    ["name" => "docDeliveryData", "type" => "record", "fields" => [
                        ["name" => "emailAddress", "type" => ["string", "null"]],
                        ["name" => "chapterTitle", "type" => ["string", "null"]],
                        ["name" => "volume", "type" => ["string", "null"]],
                        ["name" => "issue", "type" => ["string", "null"]],
                        ["name" => "startPage", "type" => "string"],
                        ["name" => "endPage", "type" => "string"],
                    ]]
                ]],
            ]
        ];
    }

    /**
     * @param $requestType
     * @return bool
     */
    protected function isValidRequestType($requestType)
    {
        return in_array($requestType, self::VALID_REQUEST_TYPES);
    }

    /**
     * @param string $requestType
     */
    public function setRequestType($requestType)
    {
        if ($this->isValidRequestType($requestType)) {
            $this->requestType = $requestType;
        } else {
            $this->requestType = 'hold';
            APILogger::addInfo('Invalid request type {type} sent. Default set to "hold."', ['type' => $requestType]);
        }
    }
}
