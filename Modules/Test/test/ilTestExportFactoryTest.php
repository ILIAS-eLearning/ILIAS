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
 * Class ilTestExportFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportFactoryTest extends ilTestBaseTestCase
{
    private ilTestExportFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestExportFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExportFactory::class, $this->testObj);
    }

    public function testGetExporter(): void
    {
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $objTest = new ilObjTest();

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
        $testObj = new ilTestExportFactory($objTest);
        $this->assertInstanceOf(ilTestExportFixedQuestionSet::class, $testObj->getExporter());

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
        $testObj = new ilTestExportFactory($objTest);
        $this->assertInstanceOf(ilTestExportRandomQuestionSet::class, $testObj->getExporter());
    }
}
