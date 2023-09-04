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

declare(strict_types=1);

use ILIAS\DI\Container;

/**
 * @ilCtrl_Calls ilAchievementsGUI: ilLearningProgressGUI, ilPersonalSkillsGUI, ilBadgeProfileGUI, ilLearningHistoryGUI
 */
class ilAchievementsGUI
{
    protected readonly ilCtrl $ctrl;
    protected readonly ilAchievements $achievements;
    protected readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->achievements = new ilAchievements();
        $this->lng = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule('lhist');

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('show');

        switch ($next_class) {
            case 'illearningprogressgui':
                $this->main_tpl->setTitle($this->lng->txt('learning_progress'));
                $this->main_tpl->setTitleIcon(ilUtil::getImagePath('icon_trac.svg'));
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_PERSONAL_DESKTOP, 0);
                $this->ctrl->forwardCommand($new_gui);
                break;

            case 'illearninghistorygui':
                $this->main_tpl->setTitle($this->lng->txt('lhist_learning_history'));
                $this->main_tpl->setTitleIcon(ilUtil::getImagePath('icon_lhist.svg'));
                $lhistgui = new ilLearningHistoryGUI();
                $this->ctrl->forwardCommand($lhistgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilpersonalskillsgui':
                $this->main_tpl->setTitle($this->lng->txt('skills'));
                $this->main_tpl->setTitleIcon(ilUtil::getImagePath('icon_skmg.svg'));
                $skgui = new ilPersonalSkillsGUI();
                $this->ctrl->forwardCommand($skgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilbadgeprofilegui':
                $this->main_tpl->setTitle($this->lng->txt('obj_bdga'));
                $this->main_tpl->setTitleIcon(ilUtil::getImagePath('icon_bdga.svg'));
                $bgui = new ilBadgeProfileGUI();
                $this->ctrl->forwardCommand($bgui);
                $this->main_tpl->printToStdout();
                break;

            case 'ilusercertificategui':
                $this->main_tpl->setTitle($this->lng->txt('obj_cert'));
                $this->main_tpl->setTitleIcon(ilUtil::getImagePath('icon_cert.svg'));
                $cgui = new ilUserCertificateGUI();
                $this->ctrl->forwardCommand($cgui);
                $this->main_tpl->printToStdout();
                break;

            default:
                if ($cmd === 'show') {
                    $this->show();
                }
                $this->main_tpl->printToStdout();
                break;
        }
    }

    protected function show(): void
    {
        $gui_classes = $this->getGUIClasses();
        $first_service = current($this->achievements->getActiveServices());
        if ($first_service) {
            $this->ctrl->redirectByClass(['ildashboardgui', 'ilachievementsgui', $gui_classes[$first_service]]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function getGUIClasses(): array
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
