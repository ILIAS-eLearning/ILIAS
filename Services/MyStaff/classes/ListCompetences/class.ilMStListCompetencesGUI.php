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
 ********************************************************************
 */

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\DI\Container;

/**
 * Class ilMStListCompetencesGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStListCompetencesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesSkillsGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesProfilesGUI
 */
class ilMStListCompetencesGUI
{
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_INDEX = 'index';
    public const CMD_GET_ACTIONS = "getActions";
    public const CMD_RESET_FILTER = 'resetFilter';
    public const SUB_TAB_SKILLS = 'skills';
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private Container $dic;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(Container $dic)
    {
        $this->main_tpl = $dic->ui()->mainTemplate();
        $this->access = ilMyStaffAccess::getInstance();
        $this->dic = $dic;
    }

    protected function checkAccessOrFail(): void
    {
        if ($this->access->hasCurrentUserAccessToCompetences()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand(): void
    {
        $cmd = $this->dic->ctrl()->getCmd();
        $next_class = $this->dic->ctrl()->getNextClass();
        switch ($next_class) {
            case strtolower(ilMStListCompetencesSkillsGUI::class):
                $this->addSubTabs(self::SUB_TAB_SKILLS);
                $gui = new ilMStListCompetencesSkillsGUI($this->dic);
                $this->dic->ctrl()->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_INDEX:
                        $this->$cmd();
                        break;
                    default:
                        $this->index();
                        break;
                }
                break;
        }
    }

    protected function addSubTabs(string $subtab_active): void
    {
        $this->dic->language()->loadLanguageModule('skmg');
        $this->dic->tabs()->addSubTab(
            self::SUB_TAB_SKILLS,
            $this->dic->language()->txt('skmg_selected_skills'),
            $this->dic->ctrl()->getLinkTargetByClass([
                self::class,
                ilMStListCompetencesSkillsGUI::class
            ])
        );

        $this->dic->tabs()->activateSubTab($subtab_active);
    }

    final public function index(): void
    {
        $this->dic->ctrl()->redirectByClass(ilMStListCompetencesSkillsGUI::class);
    }
}
