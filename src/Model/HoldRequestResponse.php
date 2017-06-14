<?php
namespace NYPL\Services\Model;

use NYPL\Services\Model\HoldRequest\HoldRequest;
use NYPL\Starter\Model\Response\SuccessResponse;

/**
 * @SWG\Definition(title="HoldRequestResponse", type="object")
 */
class HoldRequestResponse extends SuccessResponse
{
    /**
     * @SWG\Property
     * @var HoldRequest
     */
    public $data;
}
