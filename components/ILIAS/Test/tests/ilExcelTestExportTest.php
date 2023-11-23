<?php

class ilExcelTestExportTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilExcelTestExport = new ilExcelTestExport(
            $this->createMock(ilObjTest::class),
            '',
            '',
            false,
            true,
            null,
        );
        $this->assertInstanceOf(ilExcelTestExport::class, $ilExcelTestExport);
    }

    public function testWithResultsPage(): void
    {
        $this->markTestSkipped();
    }

    public function testGetContent(): void
    {
        $this->markTestSkipped();
    }

    public function testWithUserPages(): void
    {
        $this->markTestSkipped();
    }

    public function testWithAggregatedResultsPage(): void
    {
        $this->markTestSkipped();
    }

    public function testDeliver(): void
    {
        $this->markTestSkipped();
    }
}