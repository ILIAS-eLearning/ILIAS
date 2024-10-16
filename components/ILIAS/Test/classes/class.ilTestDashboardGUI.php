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

use ILIAS\Test\ExportImport\Factory as ExportImportFactory;
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\ResponseHandler;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

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
    public function __construct(
        protected ilObjTest $test_obj,
        protected readonly ilObjUser $user,
        protected readonly ilAccess $access,
        protected readonly ilTestAccess $test_access,
        protected ilGlobalTemplateInterface $main_tpl,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly ilUIService $ui_service,
        protected readonly DataFactory $data_factory,
        protected readonly ilLanguage $lng,
        protected readonly Refinery $refinery,
        protected readonly ilDBInterface $db,
        protected readonly ilCtrl $ctrl,
        protected ilTabsGUI $tabs,
        protected TabsManager $tabs_manager,
        protected ilToolbarGUI $toolbar,
        protected readonly \ilComponentFactory $component_factory,
        protected readonly ExportImportFactory $export_factory,
        protected readonly RequestDataCollector $testrequest,
        protected readonly ResponseHandler $response_handler,
        protected readonly ParticipantRepository $participant_repository,
        protected ilTestQuestionSetConfig $question_set_config,
        protected ilTestObjectiveOrientedContainer $objective_parent
    ) {
    }

    public function executeCommand(): void
    {
        if (!$this->test_access->checkManageParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->tabs_manager->activateTab(TabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->tabs_manager->getDashboardSubTabs();

        switch ($this->ctrl->getNextClass()) {
            case 'iltestparticipantsgui':
                $this->tabs_manager->activateSubTab(TabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);

                $gui = new ilTestParticipantsGUI(
                    $this->test_obj,
                    $this->user,
                    $this->objective_parent,
                    $this->question_set_config,
                    $this->access,
                    $this->test_access,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->ui_service,
                    $this->data_factory,
                    $this->lng,
                    $this->ctrl,
                    $this->refinery,
                    $this->db,
                    $this->tabs,
                    $this->toolbar,
                    $this->component_factory,
                    $this->export_factory,
                    $this->testrequest,
                    $this->response_handler,
                    $this->participant_repository
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestparticipantstimeextensiongui':
                $this->tabs_manager->activateSubTab(TabsManager::SUBTAB_ID_TIME_EXTENSION);

                $gui = new ilTestParticipantsTimeExtensionGUI(
                    $this->test_obj,
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
