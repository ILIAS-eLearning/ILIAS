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
 * Class ilTestSkillAdministrationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillAdministrationGUITest extends ilTestBaseTestCase
{
    private ilTestSkillAdministrationGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillAdministrationGUI(
            $this->getIliasMock(),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(ilObjTest::class),
            201
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillAdministrationGUI::class, $this->testObj);
    }
}
