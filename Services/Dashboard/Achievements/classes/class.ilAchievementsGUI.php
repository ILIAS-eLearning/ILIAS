<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @ilCtrl_Calls ilAchievementsGUI: ilLearningProgressGUI, ilPersonalSkillsGUI, ilBadgeProfileGUI, ilLearningHistoryGUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAchievementsGUI
{
    protected ilCtrl $ctrl;
    protected ilAchievements $achievements;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    private ilGlobalTemplateInterface $main_tpl;

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
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;

        $lng->loadLanguageModule("lhist");

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");


        switch ($next_class) {
            case "illearningprogressgui":
                $main_tpl->setTitle($lng->txt("learning_progress"));
                $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_trac.svg"));
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_PERSONAL_DESKTOP, 0);
                $ctrl->forwardCommand($new_gui);
                break;

            case 'illearninghistorygui':
                $main_tpl->setTitle($lng->txt("lhist_learning_history"));
                $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_lhist.svg"));
                $lhistgui = new ilLearningHistoryGUI();
                $ctrl->forwardCommand($lhistgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilpersonalskillsgui':
                $main_tpl->setTitle($lng->txt("skills"));
                $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg.svg"));
                $skgui = new ilPersonalSkillsGUI();
                $ctrl->forwardCommand($skgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilbadgeprofilegui':
                $main_tpl->setTitle($lng->txt("obj_bdga"));
                $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_bdga.svg"));
                $bgui = new ilBadgeProfileGUI();
                $ctrl->forwardCommand($bgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilusercertificategui':
                $main_tpl->setTitle($lng->txt("obj_cert"));
                $main_tpl->setTitleIcon(ilUtil::getImagePath("icon_cert.svg"));
                $cgui = new ilUserCertificateGUI();
                $ctrl->forwardCommand($cgui);
                $this->main_tpl->printToStdout();
                break;

            default:
                if (in_array($cmd, array("show"))) {
                    $this->$cmd();
                }
                $this->main_tpl->printToStdout();
                break;
        }
    }

    /**
     * Show (redirects to first active service)
     */
    protected function show() : void
    {
        $ctrl = $this->ctrl;

        $gui_classes = $this->getGUIClasses();
        $first_service = current($this->achievements->getActiveServices());
        if ($first_service) {
            $ctrl->redirectByClass(["ildashboardgui", "ilachievementsgui", $gui_classes[$first_service]]);
        }
    }

    protected function setTabs(string $activate) : void
    {
        $tabs = $this->tabs;
        $links = $this->getLinks();

        foreach ($this->achievements->getActiveServices() as $s) {
            $tabs->addTab("achieve_" . $s, $links[$s]["txt"], $links[$s]["link"]);
        }
        $tabs->activateTab("achieve_" . $activate);
    }

    /**
     * @return array[]
     */
    protected function getLinks() : array
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
            $links[$k]["link"] = $ctrl->getLinkTargetByClass(["ildashboardgui", "ilachievementsgui", $gui_classes[$k]]);
        }

        return $links;
    }

    /**
     * Get GUI class
     * @return string[]
     */
    protected function getGUIClasses() : array
    {
        return [
            ilAchievements::SERV_LEARNING_HISTORY => strtolower(ilLearningHistoryGUI::class),
            ilAchievements::SERV_COMPETENCES => strtolower(ilPersonalSkillsGUI::class),
            ilAchievements::SERV_LEARNING_PROGRESS => strtolower(ilLearningProgressGUI::class),
            ilAchievements::SERV_BADGES => strtolower(ilBadgeProfileGUI::class),
            ilAchievements::SERV_CERTIFICATES => strtolower(ilUserCertificateGUI::class)
        ];
    }
}
