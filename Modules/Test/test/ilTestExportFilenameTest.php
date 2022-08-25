<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExportFilenameTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportFilenameTest extends ilTestBaseTestCase
{
    private ilTestExportFilename $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestExportFilename($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExportFilename::class, $this->testObj);
    }
}
