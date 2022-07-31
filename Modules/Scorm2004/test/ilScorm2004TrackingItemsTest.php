<?php declare(strict_types=1);
/**
 * Class ilScorm2004TrackingItemsTest
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilScorm2004TrackingItemsTest extends ilScorm2004BaseTestCase
{
    private ilSCORM2004TrackingItems $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_ilErr();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_lng();

        $this->testObj = new ilSCORM2004TrackingItems();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilSCORM2004TrackingItems::class, $this->testObj);
    }

    public function test_parentObjectMethodExistsAndReturns() : void
    {
        $timeStr = '2:22:22';
        $timeInt = 8542;
        $SCORMTimeToSeconds = $this->testObj->SCORMTimeToSeconds($timeStr);

        $this->assertEquals($timeInt, $SCORMTimeToSeconds);
    }
}
