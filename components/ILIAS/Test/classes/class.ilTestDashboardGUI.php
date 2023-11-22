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

use ILIAS\UI\Factory as UIFactory;
use ILiAS\UI\Renderer as UIRenderer;

use ILIAS\Test\InternalRequestService;

/**
 * Class ilTestDashboardGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestDashboardGUI: ilTestParticipantsGUI
 * @ilCtrl_Calls ilTestDashboardGUI: ilTestParticipantsTimeExtensionGUI
 */
class ilTestDashboardGUI
{
    protected ilTestAccess $test_access;
    protected ilTestTabsManager $tabs_manager;
    protected ilTestObjectiveOrientedContainer $objective_parent;

    public function __construct(
        protected ilObjTest $test_obj,
        protected ilObjUser $user,
        protected ilAccess $access,
        protected ilGlobalTemplateInterface $main_tpl,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilLanguage $lng,
        protected ilDBInterface $db,
        protected ilCtrl $ctrl,
        protected ilTabsGUI $tabs,
        protected ilToolbarGUI $toolbar,
        protected ilTestQuestionSetConfig $question_set_config,
        protected InternalRequestService $testrequest
    ) {
    }

    public function getTestObj(): ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestObj(ilObjTest $test_obj)
    {
        $this->test_obj = $test_obj;
    }

    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->question_set_config;
    }

    public function setQuestionSetConfig(ilTestQuestionSetConfig $question_set_config): void
    {
        $this->question_set_config = $question_set_config;
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->test_access;
    }

    public function setTestAccess(ilTestAccess $test_access): void
    {
        $this->test_access = $test_access;
    }

    public function getTestTabs(): ilTestTabsManager
    {
        return $this->tabs_manager;
    }

    public function setTestTabs(ilTestTabsManager $tabs_manager): void
    {
        $this->tabs_manager = $tabs_manager;
    }

    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
    {
        return $this->objective_parent;
    }

    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objective_parent)
    {
        $this->objective_parent = $objective_parent;
    }

    public function executeCommand(): void
    {
        if (!$this->getTestAccess()->checkManageParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->getTestTabs()->getDashboardSubTabs();

        switch ($this->ctrl->getNextClass()) {
            case 'iltestparticipantsgui':
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);

                $gui = new ilTestParticipantsGUI(
                    $this->getTestObj(),
                    $this->getQuestionSetConfig(),
                    $this->access,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->lng,
                    $this->ctrl,
                    $this->db,
                    $this->tabs,
                    $this->toolbar,
                    $this->testrequest
                );
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestparticipantstimeextensiongui':
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_TIME_EXTENSION);

                $gui = new ilTestParticipantsTimeExtensionGUI(
                    $this->getTestObj(),
                    $this->user,
                    $this->ctrl,
                    $this->lng,
                    $this->db,
                    $this->main_tpl,
                    new ilTestParticipantAccessFilterFactory($this->access)
                );
                $this->ctrl->forwardCommand($gui);
                break;
        }
    }
}
