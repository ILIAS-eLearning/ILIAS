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

use ILIAS\Test\TestDIC;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\Logging\LogTable;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @ilCtrl_Calls ilObjTestFolderGUI: ilPermissionGUI, ilGlobalUnitConfigurationGUI
 */
class ilObjTestFolderGUI extends ilObjectGUI
{
    private const SHOW_LOGS_CMD = 'logs';

    private RequestDataCollector $testrequest;
    private TestLogViewer $log_viewer;

    private GeneralQuestionPropertiesRepository $questionrepository;

    private DataFactory $data_factory;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $this->data_factory = new DataFactory();

        $local_dic = TestDIC::dic();
        $this->testrequest = $local_dic['request_data_collector'];
        $this->log_viewer = $local_dic['logging.viewer'];
        $this->questionrepository = $local_dic['question.general_properties.repository'];

        $this->type = 'assf';

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"), $this->ilias->error_obj->WARNING);
        }

        $this->lng->loadLanguageModule('assessment');
    }

    private function getTestFolder(): ilObjTestFolder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->object;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new \ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case 'ilglobalunitconfigurationgui':
                if (!$this->rbac_system->checkAccess('visible,read', $this->getTestFolder()->getRefId())) {
                    $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
                }

                $this->tabs_gui->setTabActive('units');

                $gui = new \ilGlobalUnitConfigurationGUI(
                    new \ilUnitConfigurationRepository(0)
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = 'showGlobalSettings';
                }
                $cmd .= 'Object';
                $this->$cmd();

                break;
        }
    }

    public function showGlobalSettingsObject(?Form $form = null): void
    {
        $this->tabs_gui->setTabActive('settings');

        if ($form === null) {
            $form = $this->buildGlobalSettingsForm();
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    private function buildGlobalSettingsForm(): Form
    {
        $inputs = $this->getTestFolder()->getGlobalSettingsRepository()->getGlobalSettings()->toForm(
            $this->ui_factory,
            $this->refinery,
            $this->lng
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'SaveGlobalSettings'),
            $inputs
        );
    }

    /**
     * Save Assessment settings
     */
    public function saveGlobalSettingsObject(): void
    {
        if (!$this->access->checkAccess('write', '', $this->getTestFolder()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            $this->showGlobalSettingsObject();
            return;
        }

        $form = $this->buildGlobalSettingsForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showGlobalSettingsObject($form);
            return;
        }

        $this->getTestFolder()->getGlobalSettingsRepository()
            ->storeGlobalSettings($data['global_settings']);

        $this->showGlobalSettingsObject($form);
    }

    public function exportLegacyLogsObject(): void
    {
        $csv_output = $this->getTestFolder()->getTestLogViewer()->getLegacyLogExportForObjId();

        ilUtil::deliverData(
            $csv_output,
            'legacy_logs.csv'
        );
    }

    protected function showLogSettingsObject(?Form $form = null): void
    {
        $this->tabs_gui->activateTab('logs');

        if ($form === null) {
            $form = $this->buildLogSettingsForm();
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function saveLogSettingsObject(): void
    {
        if (!$this->access->checkAccess('write', '', $this->getTestFolder()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            $this->showLogSettingsObject();
            return;
        }

        $form = $this->buildLogSettingsForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showLogSettingsObject($form);
            return;
        }

        $this->getTestFolder()->getGlobalSettingsRepository()
            ->storeLoggingSettings($data['logging']);

        $this->showLogSettingsObject($form);
    }

    protected function buildLogSettingsForm(): Form
    {
        $inputs = $this->getTestFolder()->getGlobalSettingsRepository()->getLoggingSettings()->toForm(
            $this->ui_factory,
            $this->refinery,
            $this->lng
        );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(self::class, 'saveLogSettings'),
            $inputs
        );
    }

    public function logsObject(): void
    {
        $this->tabs_gui->activateTab('logs');
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('export_legacy_logs'),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportLegacyLogs')
            )
        );
        $here_uri = $this->data_factory->uri(ILIAS_HTTP_PATH
            . '/' . $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_LOGS_CMD));
        list($url_builder, $action_parameter_token, $row_id_token) = (new URLBuilder($here_uri))->acquireParameters(
            LogTable::QUERY_PARAMETER_NAME_SPACE,
            LogTable::ACTION_TOKEN_STRING,
            LogTable::ENTRY_TOKEN_STRING
        );

        if ($this->request_wrapper->has($action_parameter_token->getName())) {
            $this->object->getTestLogViewer()->executeLogTableAction(
                $url_builder,
                $action_parameter_token,
                $row_id_token
            );
        }

        $table_gui = $this->log_viewer->getLogTable(
            $url_builder,
            $action_parameter_token,
            $row_id_token
        );
        $this->tpl->setVariable('ADM_CONTENT', $this->ui_renderer->render($table_gui));
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    public function getLogdataSubtabs(): void
    {
        $this->tabs_gui->addSubTabTarget(
            'settings',
            $this->ctrl->getLinkTarget($this, 'showLogSettings'),
            ['saveLogSettings', 'showLogSettings'],
            ''
        );

        // log output
        $this->tabs_gui->addSubTabTarget(
            'logs_output',
            $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_LOGS_CMD),
            [self::SHOW_LOGS_CMD],
            ''
        );
    }

    protected function getTabs(): void
    {
        if (in_array($this->ctrl->getCmd(), ['saveLogSettings', 'showLogSettings', self::SHOW_LOGS_CMD])) {
            $this->getLogdataSubtabs();
        }

        if ($this->rbac_system->checkAccess('visible,read', $this->getTestFolder()->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'showGlobalSettings'),
                ['showGlobalSettings', 'settings', '', 'view'],
                '',
                ''
            );

            $this->tabs_gui->addTarget(
                'logs',
                $this->ctrl->getLinkTarget($this, "showLogSettings"),
                ['saveLogSettings', 'showLogSettings', self::SHOW_LOGS_CMD, 'showLog', 'exportLog', 'logAdmin', 'deleteLog'],
                '',
                ''
            );

            $this->tabs_gui->addTarget(
                'units',
                $this->ctrl->getLinkTargetByClass('ilGlobalUnitConfigurationGUI', ''),
                '',
                'ilglobalunitconfigurationgui'
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->getTestFolder()->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
        }
    }
}
