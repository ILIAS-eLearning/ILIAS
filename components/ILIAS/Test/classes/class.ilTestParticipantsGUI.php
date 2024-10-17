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

use ILIAS\Test\ResponseHandler;
use ILIAS\Test\Participants\ParticipantTable;
use ILIAS\Test\ExportImport\Factory as ExportImportFactory;
use ILIAS\Test\ExportImport\Types as ExportImportTypes;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Results\Data\Factory as ResultsDataFactory;
use ILIAS\Test\Results\Presentation\Factory as ResultsPresentationFactory;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Participants\ParticipantTableModalActions;
use ILIAS\Test\Participants\ParticipantTableIpRangeAction;
use ILIAS\Test\Participants\ParticipantTableExtraTimeAction;
use ILIAS\Test\Participants\ParticipantTableFinishTestAction;
use ILIAS\Test\Participants\ParticipantTableDeleteResultsAction;
use ILIAS\Test\Participants\ParticipantTableShowResultsAction;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

/**
 * Class ilTestParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestParticipantsTableGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestEvaluationGUI
 */
class ilTestParticipantsGUI
{
    public const CMD_SHOW = 'show';

    public const CALLBACK_ADD_PARTICIPANT = 'addParticipants';

    private const EXPORT_TYPE_PARAMETER = 'export_type';
    private const EXPORT_PLUGIN_TYPE_PARAMETER = 'export_plugin_type';

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;

    public function __construct(
        protected ilObjTest $test_obj,
        protected readonly ilObjUser $current_user,
        protected readonly ilTestObjectiveOrientedContainer $objective_parent,
        protected readonly ilTestQuestionSetConfig $question_set_config,
        protected ilAccess $access,
        protected ilTestAccess $test_access,
        protected ilGlobalTemplateInterface $main_tpl,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilUIService $ui_service,
        protected DataFactory $data_factory,
        protected ilLanguage $lng,
        protected ilCtrlInterface $ctrl,
        protected Refinery $refinery,
        protected ilDBInterface $db,
        protected ilTabsGUI $tabs,
        protected ilToolbarGUI $toolbar,
        protected ilComponentFactory $component_factory,
        protected ExportImportFactory $export_factory,
        protected RequestDataCollector $testrequest,
        protected ResponseHandler $response_handler,
        protected ParticipantRepository $participant_repository,
        protected readonly ResultsDataFactory $results_data_factory,
        protected readonly ResultsPresentationFactory $results_presentation_factory
    ) {
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($access);
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilrepositorysearchgui':
                $gui = new ilRepositorySearchGUI();
                $gui->setCallback($this, self::CALLBACK_ADD_PARTICIPANT, []);

                $gui->addUserAccessFilterCallable($this->participant_access_filter->getManageParticipantsUserFilter(
                    $this->test_obj->getRefId()
                ));


                $this->ctrl->setReturnByClass(self::class, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);

                break;

            case 'iltestevaluationgui':
                $gui = new ilTestEvaluationGUI($this->test_obj);
                $gui->setObjectiveOrientedContainer($this->objective_parent);
                $gui->setTestAccess($this->test_access);
                $this->tabs->clearTargets();
                $this->tabs->clearSubTabs();

                $this->ctrl->forwardCommand($gui);

                break;

            default:
                $command = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';
                $this->{$command}();
        }
    }

    public function showCmd(): void
    {
        $this->addUserSearchControls($this->toolbar);

        if ($this->test_obj->evalTotalPersons() > 0) {
            $this->addExportDropdown($this->toolbar);
        }

        $components = $this->getParticipantTable()->getComponents(
            $this->getTableActionUrlBuilder(),
            $this->ctrl->getLinkTargetByClass(self::class, 'show')
        );

        $this->main_tpl->setContent(
            $this->ui_renderer->render($components)
        );
    }

    public function executeTableActionCmd(): void
    {
        $this->getParticipantTable()->execute($this->getTableActionUrlBuilder());
        $this->showCmd();
    }

    private function getParticipantTable(): ParticipantTable
    {
        return new ParticipantTable(
            $this->ui_factory,
            $this->ui_service,
            $this->lng,
            $this->test_access,
            $this->data_factory,
            $this->testrequest,
            $this->participant_access_filter,
            $this->participant_repository,
            $this->results_data_factory,
            $this->results_presentation_factory->getAttemptResultsSettings(
                $this->test_obj,
                false
            ),
            $this->test_obj,
            $this->buildParticipantTableActions()
        );
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        $uri = $this->ctrl->getLinkTargetByClass(self::class, 'executeTableAction', '', true);
        return new URLBuilder($this->data_factory->uri(ILIAS_HTTP_PATH . '/' . $uri));
    }

    protected function addUserSearchControls(ilToolbarGUI $toolbar): void
    {
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $toolbar,
            [
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add')
            ]
        );
        $toolbar->addSeparator();

        $search_btn = $this->ui_factory->button()->standard(
            $this->lng->txt('tst_search_users'),
            $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', 'start')
        );
        $toolbar->addComponent($search_btn);
    }

    private function addExportDropdown(ilToolbarGUI $toolbar): void
    {
        $toolbar->addSeparator();

        if ($this->test_obj->getAnonymity()) {
            $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, 'all_test_runs_a');
            $options = [
                $this->ui_factory->button()->shy(
                    $this->lng->txt('exp_scored_test_attempt'),
                    $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
                )
            ];
        } else {
            $options = $this->buildOptionsForTestWithNames();
        }

        $options = $this->addPluginExportsToOptions($options);

        $this->ctrl->clearParameterByClass(self::class, 'export_type');
        $toolbar->addComponent(
            $this->ui_factory->dropdown()->standard($options)->withLabel($this->lng->txt('exp_eval_data'))
        );
    }

    /**
     * @return array<\ILIAS\UI\Component\Button\Shy>
     */
    private function buildOptionsForTestWithNames(): array
    {
        $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, ExportImportTypes::SCORED_RUN->value);
        $options = [
            $this->ui_factory->button()->shy(
                $this->lng->txt('exp_scored_test_attempt'),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
            )
        ];
        $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, ExportImportTypes::ALL_RUNS->value);
        $options[] = $this->ui_factory->button()->shy(
            $this->lng->txt('exp_all_test_runs'),
            $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
        );
        return $this->addCertificateExportToOptions($options);
    }

    /**
     * @param array<\ILIAS\UI\Component\Button\Shy> $options
     * @return array<\ILIAS\UI\Component\Button\Shy>
     */
    private function addCertificateExportToOptions(array $options): array
    {
        try {
            if ((new ilCertificateActiveValidator())->validate()) {
                $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, ExportImportTypes::CERTIFICATE_ARCHIVE->value);
                $options[] = $this->ui_factory->button()->shy(
                    $this->lng->txt('exp_grammar_as') . ' ' . $this->lng->txt('exp_type_certificate'),
                    $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
                );
            }
        } catch (ilException $e) {
        }
        return $options;
    }

    /**
     * @param array<\ILIAS\UI\Component\Button\Shy> $options
     * @return array<\ILIAS\UI\Component\Button\Shy>
     */
    private function addPluginExportsToOptions(array $options): array
    {
        foreach ($this->component_factory->getActivePluginsInSlot('texp') as $plugin) {
            $plugin->setTest($this->test_obj);
            $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, ExportImportTypes::PLUGIN->value);
            $this->ctrl->setParameterByClass(self::class, self::EXPORT_PLUGIN_TYPE_PARAMETER, $plugin->getFormat());
            $options[] = $this->ui_factory->button()->shy(
                $plugin->getFormatLabel(),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
            );
        }
        $this->ctrl->clearParameterByClass(self::class, self::EXPORT_PLUGIN_TYPE_PARAMETER);
        return $options;
    }

    public function exportResultsCmd(): void
    {
        $export_type = ExportImportTypes::tryFrom(
            $this->testrequest->strVal(self::EXPORT_TYPE_PARAMETER)
        );

        if ($export_type === null) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('failure'));
            $this->showCmd();
            return;
        }

        $plugin_type = null;
        if ($export_type === ExportImportTypes::PLUGIN) {
            $plugin_type = $this->testrequest->strVal(self::EXPORT_PLUGIN_TYPE_PARAMETER);
        }

        $this->export_factory->getExporter(
            $this->test_obj,
            $export_type,
            $plugin_type
        )->deliver();
        $this->showCmd();
    }

    protected function saveClientIpCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->test_obj->getRefId());
        $selected_users = $filter_closure($this->testrequest->raw('chbUser') ?? []);

        if ($selected_users === []) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        foreach ($selected_users as $user_id) {
            $this->test_obj->setClientIP($user_id, $_POST["clientip_" . $user_id]);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function removeParticipantsCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->test_obj->getRefId());
        $a_user_ids = $filter_closure((array) $_POST["chbUser"]);

        if (is_array($a_user_ids)) {
            foreach ($a_user_ids as $user_id) {
                $this->test_obj->disinviteUser($user_id);
            }
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function buildParticipantTableActions(): ParticipantTableModalActions
    {
        return new ParticipantTableModalActions(
            $this->ctrl,
            $this->lng,
            $this->main_tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->testrequest,
            $this->response_handler,
            $this->participant_repository,
            $this->test_obj,
            [
                ParticipantTableIpRangeAction::ACTION_ID => new ParticipantTableIpRangeAction(
                    $this->lng,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->refinery,
                    $this->participant_repository
                ),
                ParticipantTableExtraTimeAction::ACTION_ID => new ParticipantTableExtraTimeAction(
                    $this->lng,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->test_obj
                ),
                ParticipantTableFinishTestAction::ACTION_ID => new ParticipantTableFinishTestAction(
                    $this->lng,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->db,
                    new \ilTestProcessLockerFactory(
                        new \ilSetting('assessment'),
                        $this->db
                    ),
                    $this->current_user,
                    $this->test_obj
                ),
                ParticipantTableDeleteResultsAction::ACTION_ID => new ParticipantTableDeleteResultsAction(
                    $this->lng,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->test_access,
                    $this->test_obj
                ),
                ParticipantTableShowResultsAction::ACTION_ID => new ParticipantTableShowResultsAction(
                    $this->lng,
                    $this->ui_factory,
                    $this->test_access,
                    $this->ctrl,
                    $this->test_obj
                )
            ]
        );
    }
}
