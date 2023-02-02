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

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserCompetencesGUI
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStShowUserCompetencesGUI: ilMStShowUserGUI
 */
class ilMStShowUserCompetencesGUI
{
    public const CMD_SHOW_SKILLS = 'showSkills';
    public const CMD_INDEX = self::CMD_SHOW_SKILLS;
    public const SUB_TAB_SKILLS = 'skills';
    private int $usr_id;
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private Container $dic;
    private \ilGlobalTemplateInterface $main_tpl;
    protected \ILIAS\Skill\Service\SkillPersonalService $skill_personal_service;

    public function __construct(Container $dic)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->dic = $dic;
        $this->access = ilMyStaffAccess::getInstance();
        $this->skill_personal_service = $DIC->skills()->personal();

        $this->usr_id = $this->dic->http()->request()->getQueryParams()['usr_id'];
        $this->dic->ctrl()->setParameter($this, 'usr_id', $this->usr_id);
    }

    protected function checkAccessOrFail(): void
    {
        if (!$this->usr_id) {
            $this->main_tpl->setOnScreenMessage('failure', $this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToMyStaff()
            && $this->access->hasCurrentUserAccessToUser($this->usr_id)
        ) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand(): void
    {
        $this->checkAccessOrFail();

        $cmd = $this->dic->ctrl()->getCmd();
        $next_class = $this->dic->ctrl()->getNextClass();

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::CMD_INDEX:
                    case self::CMD_SHOW_SKILLS:
                    default:
                        $this->addSubTabs(self::SUB_TAB_SKILLS);
                        $this->showSkills();
                        break;
                }
        }
    }

    protected function addSubTabs(string $active_sub_tab): void
    {
        $this->dic->language()->loadLanguageModule('skmg');
        $this->dic->tabs()->addSubTab(
            self::SUB_TAB_SKILLS,
            $this->dic->language()->txt('skmg_selected_skills'),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_SHOW_SKILLS)
        );

        $this->dic->tabs()->activateSubTab($active_sub_tab);
    }

    protected function showSkills(): void
    {
        $skills_gui = new ilPersonalSkillsGUI();
        $skills = $this->skill_personal_service->getSelectedUserSkills($this->usr_id);
        $html = '';
        foreach ($skills as $skill) {
            $html .= $skills_gui->getSkillHTML($skill->getSkillNodeId(), $this->usr_id);
        }
        $this->dic->ui()->mainTemplate()->setContent($html);
    }
}
