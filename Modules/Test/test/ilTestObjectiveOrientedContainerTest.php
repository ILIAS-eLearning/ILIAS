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
 * Class ilTestObjectiveOrientedContainerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestObjectiveOrientedContainerTest extends ilTestBaseTestCase
{
    private ilTestObjectiveOrientedContainer $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestObjectiveOrientedContainer();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestObjectiveOrientedContainer::class, $this->testObj);
    }

    public function testObjId(): void
    {
        $this->testObj->setObjId(125);
        $this->assertEquals(125, $this->testObj->getObjId());
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(125);
        $this->assertEquals(125, $this->testObj->getRefId());
    }

    public function testIsObjectiveOrientedPresentationRequired(): void
    {
        $this->assertFalse($this->testObj->isObjectiveOrientedPresentationRequired());

        $this->testObj->setObjId(1254);
        $this->assertTrue($this->testObj->isObjectiveOrientedPresentationRequired());
    }
}
