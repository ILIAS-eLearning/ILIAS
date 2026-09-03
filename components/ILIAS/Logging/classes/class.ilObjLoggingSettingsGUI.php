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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as Services;
use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfig;
use ILIAS\Logging\Config\ByComponent\ConfigInterface as ByComponentConfig;
use ILIAS\Logging\Config\ByComponent\RepositoryInterface as ComponentConfigRepo;
use ILIAS\Logging\ILIASLogLevel;
use ILIAS\Logging\Logger\LegacyInitiator;

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjLoggingSettingsGUI: ilPermissionGUI
*/
class ilObjLoggingSettingsGUI extends ilObjectGUI
{
    protected const string SECTION_SETTINGS = 'settings';
    protected const string SUB_SECTION_COMPONENTS = 'log_components';
    protected const string SUB_SECTION_ERROR = 'log_error_settings';

    protected ilLoggingErrorSettings $error_settings;
    protected Refinery $refinery;
    protected ilComponentRepository $component_repo;
    protected BasicConfig $basic_log_config;
    protected ComponentConfigRepo $component_config_repo;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference, bool $a_prepare_output = true)
    {
        global $DIC;

        $this->type = 'logs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng = $DIC->language();
        $this->component_repo = $DIC["component.repository"];

        $this->initErrorSettings();
        $this->lng->loadLanguageModule('logging');
        $this->lng->loadLanguageModule('log');

        $this->refinery = $DIC->refinery();

        $initiator = LegacyInitiator::getInstance();
        $this->basic_log_config = $initiator->basicConfig();
        $this->component_config_repo = $initiator->componentConfigRepository();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
        $this->checkPermission('read');

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "errorSettings";
                }
                $this->$cmd();

                break;
        }
    }


    public function getAdminTabs(): void
    {
        if ($this->access->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                static::SECTION_SETTINGS,
                $this->ctrl->getLinkTargetByClass('ilobjloggingsettingsgui', "errorSettings")
            );
        }
        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    public function setSubTabs(string $a_section): void
    {
        $this->tabs_gui->addSubTab(
            static::SUB_SECTION_ERROR,
            $this->lng->txt(static::SUB_SECTION_ERROR),
            $this->ctrl->getLinkTarget($this, 'errorSettings')
        );
        $this->tabs_gui->addSubTab(
            static::SUB_SECTION_COMPONENTS,
            $this->lng->txt(static::SUB_SECTION_COMPONENTS),
            $this->ctrl->getLinkTarget($this, 'components')
        );
        $this->tabs_gui->activateSubTab($a_section);
    }


    /**
     * Show components
     */
    protected function components(): void
    {
        $this->tabs_gui->activateTab(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_COMPONENTS);

        $table = new ilLogComponentTableGUI(
            $this->checkPermissionBool('write'),
            $this->component_repo,
            $this->basic_log_config,
            $this->component_config_repo,
            $this,
            'components'
        );
        $table->init();
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Save form
     */
    protected function saveComponentLevels(): void
    {
        $this->checkPermission('write');

        $levels = [];
        if ($this->http->wrapper()->post()->has('level')) {
            $levels = $this->http->wrapper()->post()->retrieve(
                'level',
                $this->refinery->custom()->transformation(
                    function ($arr) {
                        // keep keys(!), transform all values to int
                        return array_column(
                            array_map(
                                static function ($k, $v): array {
                                    return [$k, (int) $v];
                                },
                                array_keys($arr),
                                $arr
                            ),
                            1,
                            0
                        );
                    }
                )
            );
        }
        foreach ($levels as $component_id => $value) {
            if ($value === 0) {
                $this->component_config_repo->resetLevelForComponent($component_id);
            }
            $level = ILIASLogLevel::tryFrom($value);
            if ($level === null) {
                continue;
            }
            $this->component_config_repo->updateLevelForComponent($component_id, $level);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }

    protected function resetComponentLevels(): void
    {
        $this->checkPermission('write');
        $this->component_config_repo->resetLevelsForAllComponents();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }

    protected function errorSettings(?ilPropertyFormGUI $form = null): void
    {
        $this->checkPermission('read');
        $this->tabs_gui->setTabActive(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_ERROR);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormErrorSettings();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateErrorSettings(): void
    {
        $this->checkPermission('write');
        $form = $this->initFormErrorSettings();
        if ($form->checkInput()) {
            $this->getErrorSettings()->setMail($form->getInput('error_mail'));
            $this->getErrorSettings()->update();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('error_settings_saved'), true);
            $this->ctrl->redirect($this, 'errorSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->errorSettings($form);
    }

    protected function initFormErrorSettings(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('logs_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('updateErrorSettings', $this->lng->txt('save'));
        }

        $folder = new ilNonEditableValueGUI($this->lng->txt('log_error_folder'), 'error_folder');
        $folder->setValue($this->getErrorSettings()->folder());
        $form->addItem($folder);

        $mail = new ilTextInputGUI($this->lng->txt('log_error_mail'), 'error_mail');
        $mail->setValue($this->getErrorSettings()->mail());
        $form->addItem($mail);
        return $form;
    }

    protected function initErrorSettings(): void
    {
        $this->error_settings = ilLoggingErrorSettings::getInstance();
    }

    protected function getErrorSettings(): ilLoggingErrorSettings
    {
        return $this->error_settings;
    }
}
