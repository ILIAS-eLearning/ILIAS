<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSessionFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionFactoryTest extends ilTestBaseTestCase
{
    private ilTestSessionFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSessionFactory(
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSessionFactory::class, $this->testObj);
    }
}
