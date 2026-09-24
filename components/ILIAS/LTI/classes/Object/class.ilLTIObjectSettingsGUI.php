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

use ILIAS\ILIASObject\Properties\CoreProperties\Online;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Input\Field\OptionalGroup;

/**
 * Settings of an LTI object: what it is called, whether it is online, how it launches its tool and
 * the credentials it launches with. The tool itself is not changed here, only how this object uses it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_Calls ilLTIObjectSettingsGUI: ilCertificateGUI
 */
class ilLTIObjectSettingsGUI
{
    public const string CMD_SHOW = 'showSettings';
    public const string CMD_SAVE = 'saveSettings';

    private const string SUBTAB_OBJECT = 'subtab_object_settings';
    private const string SUBTAB_TOOL = 'subtab_provider_settings';
    private const string SUBTAB_CERTIFICATE = 'certificate';

    private readonly ILIAS\DI\Container $dic;

    public function __construct(private readonly ilObjLTITool $object)
    {
        global $DIC;

        $this->dic = $DIC;
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->addSubTabs();

        if ($this->dic->ctrl()->getNextClass($this) === strtolower(ilCertificateGUI::class)) {
            $this->forwardToCertificate();
            return;
        }

        match ($this->dic->ctrl()->getCmd(self::CMD_SHOW)) {
            self::CMD_SAVE => $this->save(),
            default => $this->show(),
        };
    }

    /**
     * @throws ilCtrlException
     */
    private function show(?Form $form = null): void
    {
        $this->dic->tabs()->activateSubTab(self::SUBTAB_OBJECT);
        $this->dic->ui()->mainTemplate()->setContent(
            $this->dic->ui()->renderer()->render($form ?? $this->buildForm())
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function save(): void
    {
        $form = $this->buildForm()->withRequest($this->dic->http()->request());
        $data = $form->getData();
        if ($data === null) {
            $this->show($form);
            return;
        }

        $this->object->setTitle($data['general']['title']);
        $this->object->setDescription((string) $data['general']['description']);
        $this->object->setLaunchMethod($data['appearance']['launch_method']);
        $this->object->setCustomParams((string) $data['appearance']['custom_params']);

        if (isset($data['authentication'])) {
            $this->object->getLti1p1Credentials()->setKey($data['authentication']['key']);
            $this->object->getLti1p1Credentials()->setSecret($data['authentication']['secret']);
        }

        $previous_mastery_score = $this->object->getMasteryScore();
        if (isset($data['learning_progress'])) {
            $this->object->setMasteryScore(round((float) $data['learning_progress']['mastery_score'], 2) / 100);
        }
        if ($this->object->getTool()->getUseXapi()) {
            $this->saveXapi($data['appearance']['use_xapi']);
        }

        $this->object->update();
        $this->object->getObjectProperties()->storePropertyIsOnline(new Online((bool) $data['general']['online']));

        // a new mastery score changes who has completed the object
        if ($this->object->getMasteryScore() !== $previous_mastery_score) {
            ilLPStatusWrapper::_refreshStatus($this->object->getId());
        }

        $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->dic->language()->txt('settings_saved'), true);
        $this->dic->ctrl()->redirect($this, self::CMD_SHOW);
    }

    /**
     * @throws ilCtrlException
     */
    private function buildForm(): Form
    {
        $lng = $this->dic->language();
        $field = $this->dic->ui()->factory()->input()->field();
        $tool = $this->object->getTool();

        $sections = [
            'general' => $field->section([
                'tool' => $field->text($lng->txt('provider_info'), $tool->getDescription())
                    ->withValue($tool->getTitle())
                    ->withDisabled(true),
                'title' => $field->text($lng->txt('title'), $lng->txt('title_info'))
                    ->withRequired(true)
                    ->withMaxLength(128)
                    ->withValue($this->object->getTitle()),
                'description' => $field->textarea($lng->txt('description'), $lng->txt('description_info'))
                    ->withValue($this->object->getDescription()),
                'online' => $field->checkbox($lng->txt('online'), $lng->txt('online_info'))
                    ->withValue(!$this->object->getOfflineStatus()),
            ], $lng->txt('lti_settings_form')),
        ];

        // only an LTI 1.1 tool that leaves its credentials to the object has them here
        if ($tool->getLtiVersion() === ilLTITool::VERSION_1P1 && $tool->isKeyCustomizable()) {
            $sections['authentication'] = $field->section([
                'key' => $field->text($lng->txt('lti_con_prov_key'))
                    ->withRequired(true)
                    ->withValue($this->object->getLti1p1Credentials()->getKey()),
                'secret' => $field->text($lng->txt('lti_con_prov_secret'))
                    ->withRequired(true)
                    ->withValue($this->object->getLti1p1Credentials()->getSecret()),
            ], $lng->txt('lti_con_prov_authentication'));
        }

        // the mastery score only makes sense when the tool reports results
        if ($tool->hasOutcome()) {
            $sections['learning_progress'] = $field->section([
                'mastery_score' => $field->numeric($lng->txt('mastery_score'), $lng->txt('mastery_score_info'))
                    ->withStepSize(0.01)
                    ->withRequired(true)
                    ->withAdditionalTransformation($this->dic->refinery()->int()->isGreaterThanOrEqual(0))
                    ->withAdditionalTransformation($this->dic->refinery()->int()->isLessThanOrEqual(100))
                    ->withValue(round(100 * $this->object->getMasteryScore(), 2)),
            ], $lng->txt('learning_progress_options'));
        }

        $launch_method = $field->radio($lng->txt('launch_method'))->withRequired(true);
        foreach ([
            ilObjLTITool::LAUNCH_METHOD_OWN_WIN => 'launch_method_own_win',
            ilObjLTITool::LAUNCH_METHOD_NEW_WIN => 'launch_method_new_win',
            ilObjLTITool::LAUNCH_METHOD_EMBEDDED => 'launch_method_embedded',
        ] as $method => $txt) {
            $launch_method = $launch_method->withOption($method, $lng->txt($txt), $lng->txt($txt . '_info'));
        }

        $sections['appearance'] = $field->section([
            'launch_method' => $launch_method->withValue($this->object->getLaunchMethod()),
            'custom_params' => $field->textarea(
                $lng->txt('lti_con_prov_custom_params'),
                $lng->txt('lti_con_prov_custom_params_info')
            )->withValue($this->object->getCustomParams()),
        ] + ($tool->getUseXapi() ? ['use_xapi' => $this->buildXapiInput()] : []), $lng->txt('lti_form_section_appearance'));

        return $this->dic->ui()->factory()->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, self::CMD_SAVE),
            $sections
        );
    }

    /**
     * The reports on the xAPI statements of a tool that sends them: the statements themselves and a ranking.
     */
    private function buildXapiInput(): OptionalGroup
    {
        $lng = $this->dic->language();
        $field = $this->dic->ui()->factory()->input()->field();
        $object = $this->object;

        $mode = $field->radio($lng->txt('highscore_mode'))->withRequired(true);
        foreach ([
            ilObjLTITool::HIGHSCORE_SHOW_OWN_TABLE => 'highscore_own_table',
            ilObjLTITool::HIGHSCORE_SHOW_TOP_TABLE => 'highscore_top_table',
            ilObjLTITool::HIGHSCORE_SHOW_ALL_TABLES => 'highscore_all_tables',
        ] as $value => $txt) {
            $mode = $mode->withOption((string) $value, $lng->txt($txt), $lng->txt($txt . '_description'));
        }

        $highscore = $field->optionalGroup([
            'highscore_mode' => $mode->withValue((string) $object->getHighscoreMode()),
            'highscore_top_num' => $field->numeric(
                $lng->txt('highscore_top_num'),
                $lng->txt('highscore_top_num_description')
            )->withRequired(true)
                ->withAdditionalTransformation($this->dic->refinery()->int()->isGreaterThanOrEqual(1))
                ->withValue($object->getHighscoreTopNum()),
            'highscore_achieved_ts' => $field->checkbox(
                $lng->txt('highscore_achieved_ts'),
                $lng->txt('highscore_achieved_ts_description')
            )->withValue($object->getHighscoreAchievedTS()),
            'highscore_percentage' => $field->checkbox(
                $lng->txt('highscore_percentage'),
                $lng->txt('highscore_percentage_description')
            )->withValue($object->getHighscorePercentage()),
            'highscore_wtime' => $field->checkbox(
                $lng->txt('highscore_wtime'),
                $lng->txt('highscore_wtime_description')
            )->withValue($object->getHighscoreWTime()),
        ], $lng->txt('highscore_enabled'), $lng->txt('highscore_description'));

        $inputs = [];
        // an object only names its activity when the tool does not
        if ($object->getTool()->getXapiActivityId() === '') {
            $inputs['activity_id'] = $field->text($lng->txt('activity_id'), $lng->txt('activity_id_info'))
                ->withRequired(true)
                ->withMaxLength(128)
                ->withValue($object->getCustomActivityId());
        }
        $inputs['show_statements'] = $field->checkbox($lng->txt('show_statements'), $lng->txt('show_statements_info'))
            ->withValue($object->isStatementsReportEnabled());
        $inputs['highscore'] = $object->getHighscoreEnabled() ? $highscore : $highscore->withValue(null);

        $xapi = $field->optionalGroup($inputs, $lng->txt('use_xapi'), $lng->txt('use_xapi_info'));

        return $object->getUseXapi() ? $xapi : $xapi->withValue(null);
    }

    /**
     * Stores what the xAPI input returned, null when the object does not use xAPI. Settings of an
     * option switched off are kept for when it is switched on again.
     */
    private function saveXapi(?array $xapi): void
    {
        $this->object->setUseXapi($xapi !== null);
        if ($xapi === null) {
            return;
        }

        if (isset($xapi['activity_id'])) {
            $this->object->setCustomActivityId($xapi['activity_id']);
        }
        $this->object->setStatementsReportEnabled($xapi['show_statements']);
        $this->object->setHighscoreEnabled($xapi['highscore'] !== null);
        if ($xapi['highscore'] === null) {
            return;
        }

        $this->object->setHighscoreMode((int) $xapi['highscore']['highscore_mode']);
        $this->object->setHighscoreTopNum((int) $xapi['highscore']['highscore_top_num']);
        $this->object->setHighscoreAchievedTS($xapi['highscore']['highscore_achieved_ts']);
        $this->object->setHighscorePercentage($xapi['highscore']['highscore_percentage']);
        $this->object->setHighscoreWTime($xapi['highscore']['highscore_wtime']);
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    private function forwardToCertificate(): void
    {
        if (!new ilCertificateActiveValidator()->validate()) {
            throw new ilObjectException('certificates are not active');
        }

        $this->dic->tabs()->activateSubTab(self::SUBTAB_CERTIFICATE);
        $this->dic->ctrl()->forwardCommand(new ilCertificateGUIFactory()->create($this->object));
    }

    /**
     * The tool of an object only has settings of its own when it belongs to the user reading them.
     * ilLTIToolSettingsGUI shows the same subtabs.
     *
     * @throws ilCtrlException
     */
    public function addSubTabs(): void
    {
        $tabs = $this->dic->tabs();
        $tabs->addSubTab(
            self::SUBTAB_OBJECT,
            $this->dic->language()->txt(self::SUBTAB_OBJECT),
            $this->dic->ctrl()->getLinkTargetByClass(self::class, self::CMD_SHOW)
        );

        $tool = $this->object->getTool();
        if (!$tool->isGlobal() && $tool->getCreator() === $this->dic->user()->getId()) {
            $tabs->addSubTab(
                self::SUBTAB_TOOL,
                $this->dic->language()->txt(self::SUBTAB_TOOL),
                $this->dic->ctrl()->getLinkTargetByClass(ilLTIToolSettingsGUI::class, ilLTIToolSettingsGUI::CMD_SHOW)
            );
        }

        if (new ilCertificateActiveValidator()->validate()) {
            $tabs->addSubTab(
                self::SUBTAB_CERTIFICATE,
                $this->dic->language()->txt(self::SUBTAB_CERTIFICATE),
                $this->dic->ctrl()->getLinkTargetByClass([ilObjLTIToolGUI::class, self::class, ilCertificateGUI::class], 'certificateEditor')
            );
        }
    }
}
