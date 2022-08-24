<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExportRandomQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportRandomQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestExportRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilErr();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_lng();

        $objTest = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestExportRandomQuestionSet($objTest);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExportRandomQuestionSet::class, $this->testObj);
    }
}
