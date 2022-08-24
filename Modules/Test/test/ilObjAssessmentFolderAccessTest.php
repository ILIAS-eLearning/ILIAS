<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjAssessmentFolderAccessTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjAssessmentFolderAccessTest extends ilTestBaseTestCase
{
    private ilObjAssessmentFolderAccess $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilObjAssessmentFolderAccess();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjAssessmentFolderAccess::class, $this->testObj);
    }
}
