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
}