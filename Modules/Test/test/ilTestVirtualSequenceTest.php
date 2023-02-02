<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTestVirtualSequenceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVirtualSequenceTest extends ilTestBaseTestCase
{
    private ilTestVirtualSequence $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestVirtualSequence(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestSequenceFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestVirtualSequence::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(12);
        $this->assertEquals(12, $this->testObj->getActiveId());
    }
}
