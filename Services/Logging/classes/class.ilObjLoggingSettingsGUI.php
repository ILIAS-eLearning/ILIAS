<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as Services;

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
    protected const SECTION_SETTINGS = 'settings';
    protected const SUB_SECTION_MAIN = 'log_general_settings';
    protected const SUB_SECTION_COMPONENTS = 'log_components';
    protected const SUB_SECTION_ERROR = 'log_error_settings';

    protected ilLoggingDBSettings $log_settings;
    protected ilLogger $log;
    protected ilLoggingErrorSettings $error_settings;
    protected Refinery $refinery;
    protected Services $http;

    /**
     *
     * @param mixed $a_data
     * @param boolean $a_prepare_output
     */
    public function __construct($a_data, int $a_id, bool $a_call_by_reference, bool $a_prepare_output = true)
    {
        global $DIC;
        
        $this->type = 'logs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng = $DIC->language();

        $this->initSettings();
        $this->initErrorSettings();
        $this->lng->loadLanguageModule('logging');
        $this->lng->loadLanguageModule('log');
        $this->log = ilLoggerFactory::getLogger('log');

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }
    
    public function getLogger() : ilLogger
    {
        return $this->log;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $this->$cmd();

                break;
        }
    }
    

    public function getAdminTabs() : void
    {
        if ($this->access->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                static::SECTION_SETTINGS,
                $this->ctrl->getLinkTargetByClass('ilobjloggingsettingsgui', "settings")
            );
        }
        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
    
    public function setSubTabs(string $a_section) : void
    {
        $this->tabs_gui->addSubTab(
            static::SUB_SECTION_MAIN,
            $this->lng->txt(static::SUB_SECTION_MAIN),
            $this->ctrl->getLinkTarget($this, 'settings')
        );
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

    protected function initSettings()
    {
        $this->log_settings = ilLoggingDBSettings::getInstance();
    }
    
    public function getSettings() : ilLoggingDBSettings
    {
        return $this->log_settings;
    }

    public function settings(ilPropertyFormGUI $form = null)
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        
        $this->tabs_gui->setTabActive(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_MAIN);
        
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->tpl->setContent($form->getHTML());
        $this->getLogger()->debug('Currrent level is ' . $this->getSettings()->getLevel());
        return true;
    }

    public function updateSettings() : void
    {
        if (!$this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getSettings()->setLevel((int) $form->getInput('level'));
            $this->getSettings()->enableCaching((bool) $form->getInput('cache'));
            $this->getSettings()->setCacheLevel((int) $form->getInput('cache_level'));
            $this->getSettings()->enableMemoryUsage((bool) $form->getInput('memory'));
            $this->getSettings()->enableBrowserLog((bool) $form->getInput('browser'));
            $this->getSettings()->setBrowserUsers($form->getInput('browser_users'));
            
            $this->getLogger()->info(print_r($form->getInput('browser_users'), true));
            
            $this->getSettings()->update();
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
            return;
        }
        
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->settings($form);
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('logs_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        }

        $level = new ilSelectInputGUI($this->lng->txt('log_log_level'), 'level');
        $level->setOptions(ilLogLevel::getLevelOptions());
        $level->setValue($this->getSettings()->getLevel());
        $form->addItem($level);
        
        $cache = new ilCheckboxInputGUI($this->lng->txt('log_cache_'), 'cache');
        $cache->setInfo($this->lng->txt('log_cache_info'));
        $cache->setValue('1');
        $cache->setChecked($this->getSettings()->isCacheEnabled());
        $form->addItem($cache);
        
        $cache_level = new ilSelectInputGUI($this->lng->txt('log_cache_level'), 'cache_level');
        $cache_level->setOptions(ilLogLevel::getLevelOptions());
        $cache_level->setValue($this->getSettings()->getCacheLevel());
        $cache->addSubItem($cache_level);
        
        $memory = new ilCheckboxInputGUI($this->lng->txt('log_memory'), 'memory');
        $memory->setValue('1');
        $memory->setChecked($this->getSettings()->isMemoryUsageEnabled());
        $form->addItem($memory);
        
        // Browser handler
        $browser = new ilCheckboxInputGUI($this->lng->txt('log_browser'), 'browser');
        $browser->setValue('1');
        $browser->setChecked($this->getSettings()->isBrowserLogEnabled());
        $form->addItem($browser);
        
        // users
        $users = new ilTextInputGUI($this->lng->txt('log_browser_users'), 'browser_users');
        $users->setValue(current($this->getSettings()->getBrowserLogUsers()));
        $users->setMulti(true);
        $users->setMultiValues($this->getSettings()->getBrowserLogUsers());
        $this->getLogger()->debug(print_r($this->getSettings()->getBrowserLogUsers(), true));
        $browser->addSubItem($users);
        return $form;
    }
    
    
    /**
     * Show components
     */
    protected function components() : void
    {
        $this->tabs_gui->activateTab(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_COMPONENTS);
        
        $table = new ilLogComponentTableGUI($this, 'components');
        $table->setEditable($this->checkPermissionBool('write'));
        $table->init();
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Save form
     */
    protected function saveComponentLevels() : void
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
                                static function ($k, $v) : array {
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
            $level = new ilLogComponentLevel($component_id, $value);
            $level->update();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }
    
    protected function resetComponentLevels() : void
    {
        $this->checkPermission('write');
        foreach (ilLogComponentLevels::getInstance()->getLogComponents() as $component) {
            $component->setLevel(null);
            $component->update();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }

    protected function errorSettings(?ilPropertyFormGUI $form = null) : void
    {
        $this->checkPermission('read');
        $this->tabs_gui->setTabActive(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_ERROR);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormErrorSettings();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateErrorSettings() : void
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

    protected function initFormErrorSettings() : ilPropertyFormGUI
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

    protected function initErrorSettings() : void
    {
        $this->error_settings = ilLoggingErrorSettings::getInstance();
    }

    protected function getErrorSettings() : ilLoggingErrorSettings
    {
        return $this->error_settings;
    }
}
