<?php

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

declare(strict_types=1);

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
        $active_id = 125;
        $this->testObj->setActiveId($active_id);
        $this->assertEquals($active_id, $this->testObj->getActiveId());
    }

    public function testLastFinishedPass(): void
    {
        $last_finished_pass = 125;
        $this->testObj->setLastFinishedPass($last_finished_pass);
        $this->assertEquals($last_finished_pass, $this->testObj->getLastFinishedPass());
    }
}
