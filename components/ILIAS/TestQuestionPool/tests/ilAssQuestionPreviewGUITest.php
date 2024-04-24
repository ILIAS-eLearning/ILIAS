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

use ILIAS\Refinery\Random\Group as RandomGroup;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilAssQuestionPreviewGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionPreviewGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilSetting();

        $ctrl = $this->createMock(ilCtrl::class);
        $rbac_system = $this->createMock(ilRbacSystem::class);
        $tabs = $this->createMock(ilTabsGUI::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $db = $this->createMock(ilDBInterface::class);
        $random_group = $this->createMock(RandomGroup::class);
        $global_screen = $this->createMock(ILIAS\GlobalScreen\Services::class);

        $this->object = new ilAssQuestionPreviewGUI(
            $ctrl,
            $rbac_system,
            $tabs,
            $tpl,
            $lng,
            $db,
            $random_group,
            $global_screen
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionPreviewGUI::class, $this->object);
    }
}
