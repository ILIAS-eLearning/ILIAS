<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Achivements GUI
 *
 * @ilCtrl_Calls ilAchievementsGUI: ilLearningProgressGUI, ilPersonalSkillsGUI, ilBadgeProfileGUI, ilLearningHistoryGUI
 *
 * @author killing@leifos.de
 * @ingroup ServicesPersonalDesktop
 */
class ilAchievementsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAchievements
     */
    protected $achievements;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->achievements = new ilAchievements();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * Execute command
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        $main_tpl->setTitle($lng->txt("pd_achievements"));
        $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_lhist.svg"));	// needs a final decision

        switch ($next_class) {
            case "illearningprogressgui":
                $this->setTabs(ilAchievements::SERV_LEARNING_PROGRESS);
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_PERSONAL_DESKTOP, 0);
                $ctrl->forwardCommand($new_gui);
                break;

            case 'illearninghistorygui':
                $this->setTabs(ilAchievements::SERV_LEARNING_HISTORY);
                $lhistgui = new ilLearningHistoryGUI();
                $ctrl->forwardCommand($lhistgui);
                $this->main_tpl->show();
                break;

            case 'ilpersonalskillsgui':
                $this->setTabs(ilAchievements::SERV_COMPETENCES);
                include_once './Services/Skill/classes/class.ilPersonalSkillsGUI.php';
                $skgui = new ilPersonalSkillsGUI();
                $ctrl->forwardCommand($skgui);
                $this->main_tpl->show();
                break;

            case 'ilbadgeprofilegui':
                $this->setTabs(ilAchievements::SERV_BADGES);
                include_once './Services/Badge/classes/class.ilBadgeProfileGUI.php';
                $bgui = new ilBadgeProfileGUI();
                $ctrl->forwardCommand($bgui);
                $this->main_tpl->show();
                break;

            case 'ilusercertificategui':
                $this->setTabs(ilAchievements::SERV_CERTIFICATES);
                $cgui = new ilUserCertificateGUI();
                $ctrl->forwardCommand($cgui);
                $this->main_tpl->show();
                break;

            default:
                if (in_array($cmd, array("show"))) {
                    $this->$cmd();
                }
                $this->main_tpl->show();
                break;
        }
    }

    /**
     * Show (redirects to first active service)
     */
    protected function show()
    {
        $ctrl = $this->ctrl;

        $gui_classes = $this->getGUIClasses();
        $first_service = current($this->achievements->getActiveServices());
        if ($first_service) {
            $ctrl->redirectByClass(["ilpersonaldesktopgui", "ilachievementsgui", $gui_classes[$first_service]]);
        }
    }

    /**
     * Set tabs
     */
    protected function setTabs($activate)
    {
        $tabs = $this->tabs;
        $links = $this->getLinks();

        foreach ($this->achievements->getActiveServices() as $s) {
            $tabs->addTab("achieve_" . $s, $links[$s]["txt"], $links[$s]["link"]);
        }
        $tabs->activateTab("achieve_" . $activate);
    }

    /**
     * Get link
     *
     * @param
     * @return
     */
    protected function getLinks()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $lng->loadLanguageModule("lhist");
        $gui_classes = $this->getGUIClasses();

        $links = [
            ilAchievements::SERV_LEARNING_HISTORY => [
                "txt" => $lng->txt("lhist_learning_history")
            ],
            ilAchievements::SERV_COMPETENCES => [
                "txt" => $lng->txt("skills")
            ],
            ilAchievements::SERV_LEARNING_PROGRESS => [
                "txt" => $lng->txt("learning_progress")
            ],
            ilAchievements::SERV_BADGES => [
                "txt" => $lng->txt('obj_bdga')
            ],
            ilAchievements::SERV_CERTIFICATES => [
                "txt" => $lng->txt("obj_cert")
            ]
        ];

        foreach ($links as $k => $v) {
            $links[$k]["link"] = $ctrl->getLinkTargetByClass(["ilpersonaldesktopgui", "ilachievementsgui", $gui_classes[$k]]);
        }

        return $links;
    }

    /**
     * Get GUI class
     *
     * @param
     * @return
     */
    protected function getGUIClasses()
    {
        $gui_classes = [
            ilAchievements::SERV_LEARNING_HISTORY => "ilLearningHistoryGUI",
            ilAchievements::SERV_COMPETENCES => "ilpersonalskillsgui",
            ilAchievements::SERV_LEARNING_PROGRESS => "illearningprogressgui",
            ilAchievements::SERV_BADGES => "ilbadgeprofilegui",
            ilAchievements::SERV_CERTIFICATES => "ilusercertificategui"
        ];

        return $gui_classes;
    }
}
