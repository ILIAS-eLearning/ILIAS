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
}