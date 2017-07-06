<?php
namespace NYPL\Services\Test;

use NYPL\Services\Model\HoldRequest\HoldRequest;
use PHPUnit\Framework\TestCase;

class HoldRequestTest extends TestCase
{
    public $fakeHoldRequest;

    public function setUp()
    {
        $this->fakeHoldRequest = new class extends HoldRequest {
            public function __construct($data = ['requestType' => null])
            {
                parent::__construct($data);
            }
        };
        parent::setUp();
    }

    /**
     * @covers NYPL\Services\Model\HoldRequest\NewHoldRequest
     */
    public function testAlwaysReturnsValidRequestType()
    {
        $this->assertEquals('hold', $this->fakeHoldRequest->getRequestType());
        $this->fakeHoldRequest->setRequestType('edd');
        $this->assertEquals('edd', $this->fakeHoldRequest->getRequestType());
    }
}
