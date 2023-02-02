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
 * Class ilTestInfoScreenToolbarFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestInfoScreenToolbarFactoryTest extends ilTestBaseTestCase
{
    private ilTestInfoScreenToolbarFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestInfoScreenToolbarFactory();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestInfoScreenToolbarFactory::class, $this->testObj);
    }

    public function testTestRefId(): void
    {
        $this->testObj->setTestRefId(125);
        $this->assertEquals(125, $this->testObj->getTestRefId());
    }

    public function testTestOBJ(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestOBJ($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestOBJ());
    }
}
