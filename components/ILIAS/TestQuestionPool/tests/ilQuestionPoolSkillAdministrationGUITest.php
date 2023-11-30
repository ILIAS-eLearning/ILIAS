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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilQuestionPoolSkillAdministrationGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilQuestionPoolSkillAdministrationGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilQuestionPoolSkillAdministrationGUI(
            $this->createMock(ILIAS::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(ilObjQuestionPool::class),
        0
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQuestionPoolSkillAdministrationGUI::class, $this->object);
    }
}