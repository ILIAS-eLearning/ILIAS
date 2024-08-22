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

use ILIAS\Test\Participants\ParticipantTable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Test\TestDIC;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

use ILIAS\Test\ExportImport\Factory as ImportExportFactory;
use ILIAS\Test\RequestDataCollector;
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

    protected ilTestObjectiveOrientedContainer $objective_parent;
    protected ilTestAccess $test_access;

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;

    public function __construct(
        protected ilObjTest $test_obj,
        protected ilTestQuestionSetConfig $question_set_config,
        protected ilAccess $access,
        protected ilGlobalTemplateInterface $main_tpl,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilUIService $ui_service,
        protected DataFactory $data_factory,
        protected ilLanguage $lng,
        protected ilCtrlInterface $ctrl,
        protected ilDBInterface $db,
        protected ilTabsGUI $tabs,
        protected ilToolbarGUI $toolbar,
        protected ilComponentFactory $component_factory,
        protected ImportExportFactory $export_factory,
        protected RequestDataCollector $testrequest
    ) {
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($access);
    }

    public function getTestObj(): ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestObj(ilObjTest $test_obj): void
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

    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
    {
        return $this->objective_parent;
    }

    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objective_parent): void
    {
        $this->objective_parent = $objective_parent;
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->test_access;
    }

    public function setTestAccess(ilTestAccess $test_access): void
    {
        $this->test_access = $test_access;
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilrepositorysearchgui':
                $gui = new ilRepositorySearchGUI();
                $gui->setCallback($this, self::CALLBACK_ADD_PARTICIPANT, []);

                $gui->addUserAccessFilterCallable($this->participant_access_filter->getManageParticipantsUserFilter(
                    $this->getTestObj()->getRefId()
                ));


                $this->ctrl->setReturnByClass(self::class, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);

                break;

            case "iltestevaluationgui":
                $gui = new ilTestEvaluationGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $gui->setTestAccess($this->getTestAccess());
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

        $components = $this->getParticipantTable()->getComponents(
            $this->getTableActionUrlBuilder(),
            $this->ctrl->getLinkTarget($this, 'show')
        );

        $this->main_tpl->setContent(
            $this->ui_renderer->render($components)
        );
    }

    public function executeTableActionCmd(): void
    {
        $this->getParticipantTable()->execute($this->getTableActionUrlBuilder());
    }

    private function getParticipantTable(): ParticipantTable
    {
        $test_dic = TestDIC::dic();

        return $test_dic['participant.table']
            ->withTableAction($test_dic['participant.action.ip_range'])
            ->withTableAction($test_dic['participant.action.extra_time'])
            ->withTableAction($test_dic['participant.action.finish_test'])
            ->withTestObject($this->getTestObj());
    }

    private function getTableActionUrlBuilder(): URLBuilder
    {
        $uri = $this->ctrl->getLinkTarget($this, 'executeTableAction', "", true);
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

    public function addParticipants($user_ids = []): ?bool
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $filtered_user_ids = $filter_closure($user_ids);

        foreach ($filtered_user_ids as $user_id) {
            $this->getTestObj()->inviteUser($user_id, "");
        }

        if (count($filtered_user_ids)) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("tst_invited_selected_users"), true);
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("tst_invited_nobody"), true);
            return false;
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
        return true;
    }

    protected function initToolbarControls(ilTestParticipantList $participant_list): void
    {
        if ($this->getTestObj()->getFixedParticipants()) {
            $this->addUserSearchControls($this->toolbar);
        }

        if ($this->getTestObj()->getFixedParticipants() && $participant_list->hasUnfinishedPasses()) {
            $this->toolbar->addSeparator();
        }

        if ($participant_list->hasUnfinishedPasses()) {
            $this->addFinishAllPassesButton($this->toolbar);
        }

        if ($this->getTestObj()->evalTotalPersons() > 0) {
            $this->addExportDropdown($this->toolbar);
        }
    }

    protected function addFinishAllPassesButton(ilToolbarGUI $toolbar): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $finish_all_user_passes_btn = $DIC->ui()->factory()->button()->standard(
            $DIC->language()->txt('finish_all_user_passes'),
            $DIC->ctrl()->getLinkTargetByClass('iltestevaluationgui', 'finishAllUserPasses')
        );
        $toolbar->addComponent($finish_all_user_passes_btn);
    }

    private function addExportDropdown(ilToolbarGUI $toolbar): void
    {
        $toolbar->setFormName('form_output_eval');
        $toolbar->setFormAction($this->ctrl->getFormActionByClass(self::class, 'exportEvaluation'));

        if ($this->getTestObj()->getAnonymity()) {
            $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, 'all_test_runs_a');
            $options = [
                $this->ui_factory->button()->shy(
                    $this->lng->txt('exp_scored_test_run'),
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
        $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, 'scored_test_run');
        $options = [
            $this->ui_factory->button()->shy(
                $this->lng->txt('exp_scored_test_run'),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
            )
        ];
        $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, 'all_test_runs');
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
                $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, 'certificate');
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
            $plugin->setTest($this->getTestObj());
            $this->ctrl->setParameterByClass(self::class, self::EXPORT_TYPE_PARAMETER, $plugin->getFormat());
            $options[] = $this->ui_factory->button()->shy(
                $plugin->getFormatLabel(),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportResults')
            );
        }
        return $options;
    }

    public function exportResultsCmd(): void
    {
        $this->export_factory->getExporter(
            $this->getTestObj(),
            $this->testrequest->strVal(self::EXPORT_TYPE_PARAMETER)
        )->deliver();
        $this->showCmd();
    }

    protected function saveClientIpCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $selected_users = $filter_closure($this->testrequest->raw('chbUser') ?? []);

        if ($selected_users === []) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        foreach ($selected_users as $user_id) {
            $this->getTestObj()->setClientIP($user_id, $_POST["clientip_" . $user_id]);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function removeParticipantsCmd(): void
    {
        $filter_closure = $this->participant_access_filter->getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $a_user_ids = $filter_closure((array) $_POST["chbUser"]);

        if (is_array($a_user_ids)) {
            foreach ($a_user_ids as $user_id) {
                $this->getTestObj()->disinviteUser($user_id);
            }
        } else {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("select_one_user"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }
}
