<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjAssessmentFolderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjAssessmentFolderTest extends ilTestBaseTestCase
{
    private ilObjAssessmentFolder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $this->testObj = new ilObjAssessmentFolder();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjAssessmentFolder::class, $this->testObj);
    }
}
