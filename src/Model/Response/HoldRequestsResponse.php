<?php
namespace NYPL\Services\Model\Response;

use NYPL\Starter\Model\Response\SuccessResponse;
use NYPL\Services\Model\HoldRequest\HoldRequest;

/**
 * @SWG\Definition(title="HoldRequestsResponse", type="object")
 *
 * @package NYPL\Services\Model\Response
 */
class HoldRequestsResponse extends SuccessResponse
{
    /**
     * @SWG\Property
     * @var HoldRequest[]
     */
    public $data;
}
