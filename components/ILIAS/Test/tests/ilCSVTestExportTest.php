<?php

class ilCSVTestExportTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilCSVTestExport = new ilCSVTestExport(
            $this->createMock(ilObjTest::class),
            '',
            '',
            false,
            null,
        );
        $this->assertInstanceOf(ilCSVTestExport::class, $ilCSVTestExport);
    }

    public function testWithAllResults(): void
    {
        $this->markTestSkipped();
    }

    public function testWithAggregatedResults(): void
    {
        $this->markTestSkipped();
    }

    public function testDeliver(): void
    {
        $this->markTestSkipped();
    }

    public function testGetContent(): void
    {
        $this->markTestSkipped();
    }
}