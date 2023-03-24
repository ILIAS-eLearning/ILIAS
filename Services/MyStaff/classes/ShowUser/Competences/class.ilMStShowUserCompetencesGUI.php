<?php

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserCompetencesGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStShowUserCompetencesGUI: ilMStShowUserGUI
 */
class ilMStShowUserCompetencesGUI
{
    const CMD_SHOW_SKILLS = 'showSkills';
    const CMD_INDEX = self::CMD_SHOW_SKILLS;
    const SUB_TAB_SKILLS = 'skills';
    /**
     * @var int
     */
    protected $usr_id;
    /**
     * @var ilTable2GUI
     */
    protected $table;
    /**
     * @var ilMyStaffAccess
     */
    protected $access;
    /**
     * @var Container
     */
    private $dic;


    /**
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->access = ilMyStaffAccess::getInstance();

        $this->usr_id = $this->dic->http()->request()->getQueryParams()['usr_id'];
        $this->dic->ctrl()->setParameter($this, 'usr_id', $this->usr_id);
    }


    /**
     *
     */
    protected function checkAccessOrFail()
    {
        if (!$this->usr_id) {
            ilUtil::sendFailure($this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToUser($this->usr_id)
            && $this->access->hasCurrentUserAccessToCompetences()
        ) {
            return;
        } else {
            ilUtil::sendFailure($this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }


    /**
     *
     */
    public function executeCommand()
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


    /**
     * @param string $active_sub_tab
     */
    protected function addSubTabs(string $active_sub_tab)
    {
        $this->dic->language()->loadLanguageModule('skmg');
        $this->dic->tabs()->addSubTab(
            self::SUB_TAB_SKILLS,
            $this->dic->language()->txt('skmg_selected_skills'),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_SHOW_SKILLS)
        );

        $this->dic->tabs()->activateSubTab($active_sub_tab);
    }


    /**
     *
     */
    protected function showSkills()
    {
        $skills_gui = new ilPersonalSkillsGUI();
        $skills = ilPersonalSkill::getSelectedUserSkills($this->usr_id);
        $html = '';
        foreach ($skills as $skill) {
            $html .= $skills_gui->getSkillHTML($skill["skill_node_id"], $this->usr_id);
        }
        $this->dic->ui()->mainTemplate()->setContent($html);
    }
}
