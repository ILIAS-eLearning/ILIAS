<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestTopListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTopListTest extends ilTestBaseTestCase
{
    private ilTestTopList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilDB();

        $this->testObj = new ilTestTopList($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestTopList::class, $this->testObj);
    }
}
