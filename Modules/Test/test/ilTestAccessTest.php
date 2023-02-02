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
 * Class ilTestAccessTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestAccessTest extends ilTestBaseTestCase
{
    private ilTestAccess $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilAccess();

        $this->testObj = new ilTestAccess(0, 0);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestAccess::class, $this->testObj);
    }

    public function testAccess(): void
    {
        $accessHandler_mock = $this->createMock(ilAccessHandler::class);
        $this->testObj->setAccess($accessHandler_mock);

        $this->assertEquals($accessHandler_mock, $this->testObj->getAccess());
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(120);

        $this->assertEquals(120, $this->testObj->getRefId());
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(120);

        $this->assertEquals(120, $this->testObj->getTestId());
    }
}
