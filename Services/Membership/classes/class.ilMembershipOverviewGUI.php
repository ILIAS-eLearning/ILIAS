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
 * Membership overview
 * @ilCtrl_Calls ilMembershipOverviewGUI: ilPDMembershipBlockGUI
 * @author       killing@leifos.de
 */
class ilMembershipOverviewGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        $this->main_tpl->setTitle($this->lng->txt("my_courses_groups"));
        switch ($next_class) {
            case "ilpdmembershipblockgui":
                $ctrl->setReturn($this, "show");
                $block = new ilPDMembershipBlockGUI(true);
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret != "") {
                    $this->main_tpl->setContent($ret);
                }
                break;

            default:
                if ($cmd === "show") {
                    $this->$cmd();
                }
        }
        $this->main_tpl->printToStdout();
    }

    protected function show(): void
    {
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;

        $main_tpl->setTitle($lng->txt("my_courses_groups"));

        $block = new ilPDMembershipBlockGUI(true);
        $main_tpl->setContent($block->getHTML());
    }
}
