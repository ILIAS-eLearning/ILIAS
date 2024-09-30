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
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilObjTestSettingsScoringResultsGUI: ilPropertyFormGUI, ilConfirmationGUI
 */
class ilObjTestSettingsScoringResultsGUI extends ilTestSettingsGUI
{
    /**
     * command constants
     */
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SAVE_FORM = 'saveForm';
    public const CMD_CONFIRMED_RECALC = 'saveFormAndRecalc';
    public const CMD_CANCEL_RECALC = 'cancelSaveForm';
    private const F_CONFIRM_SETTINGS = 'f_settings';

    public function __construct(
        protected ilCtrlInterface $ctrl,
        protected ilAccessHandler $access,
        protected ilLanguage $lng,
        protected ilTree $tree,
        protected ilDBInterface $db,
        protected ilComponentRepository $component_repository,
        protected ilObjTestGUI $test_gui,
        protected \ilGlobalTemplateInterface $tpl,
        protected ilTabsGUI $tabs,
        protected ScoreSettingsRepository $score_settings_repo,
        protected int $test_id,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected Refinery $refinery,
        protected Request $request,
        protected ilObjUser $active_user
    ) {
        parent::__construct($test_gui->getObject());

        $template_id = $this->test_object->getTemplate();

        if ($template_id) {
            $this->settingsTemplate = new ilSettingsTemplate(
                (int) $template_id,
                ilObjAssessmentFolderGUI::getSettingsTemplateConfig()
            );
        }
    }

    protected function loadScoreSettings(): ilObjTestScoreSettings
    {
        return $this->score_settings_repo->getFor($this->test_id);
    }
    protected function storeScoreSettings(ilObjTestScoreSettings $score_settings): void
    {
        $this->score_settings_repo->store($score_settings);
    }

    /**
     * Command Execution
     */
    public function executeCommand()
    {
        if (!$this->access->checkAccess('write', '', $this->test_gui->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirect($this->test_gui, 'infoScreen');
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM);

                switch ($cmd) {
                    case self::CMD_SHOW_FORM:
                        $this->showForm();
                        break;
                    case self::CMD_SAVE_FORM:
                        $this->saveForm();
                        break;
                    case self::CMD_CONFIRMED_RECALC:
                        $this->saveForm();
                        $settings = $this->buildForm()
                            ->withRequest($this->getRelayedRequest())
                            ->getData();
                        $this->storeScoreSettings($settings);
                        $this->test_object->recalculateScores(true);
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_score_settings_modified_and_recalc"), true);
                        $this->ctrl->redirect($this, self::CMD_SHOW_FORM);
                        break;
                    case self::CMD_CANCEL_RECALC:
                        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_score_settings_not_modified"), true);
                        $form = $this->buildForm()->withRequest($this->getRelayedRequest());
                        $this->showForm($form);
                        break;
                    default:
                        throw new Exception('unknown command: ' . $cmd);
                }
        }
    }

    private function showForm(Form $form = null): void
    {
        if ($form === null) {
            $form = $this->buildForm();
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    private function saveForm(): void
    {
        $form = $this->buildForm()
            ->withRequest($this->request);

        $settings = $form->getData();

        if (is_null($settings)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showForm($form);
            return;
        }

        if ($this->isScoreRecalculationRequired(
            $settings->getScoringSettings(),
            $this->loadScoreSettings()->getScoringSettings()
        )) {
            $this->showConfirmation($this->request);
            return;
        }

        $this->storeScoreSettings($settings);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_FORM);
    }

    private function getRelayedRequest(): Request
    {
        return unserialize(
            base64_decode(
                $this->request->getParsedBody()[self::F_CONFIRM_SETTINGS]
            )
        );
    }

    private function buildForm(): Form
    {
        $ui_pack = [
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        ];


        $environment = [];
        $environment['user_date_format'] = (new \ILIAS\Data\Factory())->dateFormat()->withTime24(
            $this->active_user->getDateFormat()
        );
        $environment['user_time_zone'] = $this->active_user->getTimeZone();

        $anonymity_flag = (bool) $this->test_object->getAnonymity();
        $disabled_flag = ($this->areScoringSettingsWritable() === false);

        $settings = $this->loadScoreSettings();
        $sections = [
            'scoring' => $settings->getScoringSettings()->toForm(...$ui_pack)
                ->withDisabled($disabled_flag),
            'summary' => $settings->getResultSummarySettings()->toForm(...array_merge($ui_pack, [$environment])),
            'details' => $settings->getResultDetailsSettings()->toForm(
                ...array_merge($ui_pack, [['taxonomy_options' => $this->getTaxonomyOptions()]])
            ),
            'gameification' => $settings->getGamificationSettings()->toForm(...$ui_pack)
        ];

        $action = $this->ctrl->getFormAction($this, self::CMD_SAVE_FORM);
        $form = $this->ui_factory->input()->container()->form()
            ->standard($action, $sections)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($v) use ($settings) {
                        return $settings
                            ->withScoringSettings($v['scoring'])
                            ->withResultSummarySettings($v['summary'])
                            ->withResultDetailsSettings($v['details'])
                            ->withGamificationSettings($v['gameification'])
                        ;
                    }
                )
            );
        return $form;
    }

    private function isScoreReportingAvailable(): bool
    {
        if (!$this->test_object->getScoreReporting()) {
            return false;
        }

        if ($this->testOBJ->getScoreReporting() == ilObjTest::SCORE_REPORTING_DATE) {
            $reporting_date = $this->testOBJ->getScoreSettings()->getResultSummarySettings()->getReportingDate();
            return $reporting_date <= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }

        return true;
    }

    private function areScoringSettingsWritable(): bool
    {
        if (!$this->test_object->participantDataExist()) {
            return true;
        }

        if (!$this->isScoreReportingAvailable()) {
            return true;
        }

        return false;
    }

    protected function getTaxonomyOptions(): array
    {
        $available_taxonomy_ids = ilObjTaxonomy::getUsageOfObject($this->test_object->getId());
        $taxononmy_translator = new ilTestQuestionFilterLabelTranslater($this->db, $this->lng);
        $taxononmy_translator->loadLabelsFromTaxonomyIds($available_taxonomy_ids);

        $taxonomy_options = [];
        foreach ($available_taxonomy_ids as $tax_id) {
            $taxonomy_options[$tax_id] = $taxononmy_translator->getTaxonomyTreeLabel($tax_id);
        }
        return $taxonomy_options;
    }

    protected function isScoreRecalculationRequired(
        ilObjTestSettingsScoring $new_settings,
        ilObjTestSettingsScoring $old_settings
    ): bool {
        $settings_changed = (
            $new_settings->getCountSystem() !== $old_settings->getCountSystem() ||
            $new_settings->getScoreCutting() !== $old_settings->getScoreCutting() ||
            $new_settings->getPassScoring() !== $old_settings->getPassScoring()
        );

        return
            $this->test_object->participantDataExist() &&
            $this->areScoringSettingsWritable() &&
            $settings_changed;
    }


    private function showConfirmation(Request $request)
    {
        $confirmation = new ilConfirmationGUI();
        $confirmation->setHeaderText($this->lng->txt('tst_trigger_result_refreshing'));
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->lng->txt('cancel'), self::CMD_CANCEL_RECALC);
        $confirmation->setConfirm($this->lng->txt('confirm'), self::CMD_CONFIRMED_RECALC);
        $confirmation->addHiddenItem(self::F_CONFIRM_SETTINGS, base64_encode(serialize($request)));
        $this->tpl->setContent($this->ctrl->getHTML($confirmation));
    }
}
