<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestImporterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestImporterTest extends ilTestBaseTestCase
{
    private ilTestImporter $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestImporter();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestImporter::class, $this->testObj);
    }
}
