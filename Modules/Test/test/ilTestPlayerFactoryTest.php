<?php declare(strict_types=1);

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
 * Class ilTestPlayerFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerFactoryTest extends ilTestBaseTestCase
{
    private ilTestPlayerFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerFactory::class, $this->testObj);
    }

    public function testGetPlayerGUI() : void
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
        $this->addGlobal_tpl();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_refinery();

        $objTest = new ilObjTest();

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
        $testObj = new ilTestPlayerFactory($objTest);
        $this->assertInstanceOf(ilTestPlayerFixedQuestionSetGUI::class, $testObj->getPlayerGUI());

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
        $testObj = new ilTestPlayerFactory($objTest);
        $this->assertInstanceOf(ilTestPlayerRandomQuestionSetGUI::class, $testObj->getPlayerGUI());
    }
}
