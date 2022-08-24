<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExportFixedQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportFixedQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestExportFixedQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilErr();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_lng();

        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestExportFixedQuestionSet($objTest_mock);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExportFixedQuestionSet::class, $this->testObj);
    }
}
