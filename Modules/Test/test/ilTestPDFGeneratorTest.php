<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPDFGeneratorTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPDFGeneratorTest extends ilTestBaseTestCase
{
    private ilTestPDFGenerator $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPDFGenerator();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPDFGenerator::class, $this->testObj);
    }
}
