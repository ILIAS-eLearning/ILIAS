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
 * Class ilTestPassesSelectorTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassesSelectorTest extends ilTestBaseTestCase
{
    private ilTestPassesSelector $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPassesSelector(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPassesSelector::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(125);
        $this->assertEquals(125, $this->testObj->getActiveId());
    }

    public function testLastFinishedPass(): void
    {
        $this->testObj->setLastFinishedPass(125);
        $this->assertEquals(125, $this->testObj->getLastFinishedPass());
    }
}
