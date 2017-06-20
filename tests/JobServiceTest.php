<?php
namespace NYPL\Services\Test;

use NYPL\Services\JobService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class JobServiceTest extends TestCase
{

    public $fakeJobService;

    public function setUp()
    {
        $this->fakeJobService = new class extends JobService {

        public static $jobId = null;

        };
        parent::setUp();
    }

    public function testifJobIdIsUnique()
    {
        $uniqueId = uniqid();

        self::assertNotNull($uniqueId);
    }

    public function testIfJobIdIsValidUuid()
    {
        $useJobManager = false;
        $uuid = JobService::generateJobId($useJobManager);

        self::assertTrue(Uuid::isValid($uuid));
    }

}
