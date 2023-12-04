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
class ilAssQuestionSkillAssignmentsGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionSkillAssignmentsGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $ctrl = $this->createMock(ilCtrl::class);
        $access = $this->createMock(ilAccessHandler::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $db = $this->createMock(ilDBInterface::class);

        $this->object = new ilAssQuestionSkillAssignmentsGUI($ctrl, $access, $tpl, $lng, $db);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionSkillAssignmentsGUI::class, $this->object);
    }
}