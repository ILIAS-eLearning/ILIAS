<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectGUI.php';

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
    const SECTION_SETTINGS = 'settings';
    const SUB_SECTION_MAIN = 'log_general_settings';
    const SUB_SECTION_COMPONENTS = 'log_components';
    const SUB_SECTION_ERROR = 'log_error_settings';
    
    
    public $tpl;
    public $lng;
    public $ctrl;
    protected $tabs_gui;
    protected $form;
    protected $settings;
    
    
    protected $log;
    
    

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        
        $this->type = 'logs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng = $lng;

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;

        $this->initSettings();
        $this->initErrorSettings();
        $this->lng->loadLanguageModule('logging');
        $this->lng->loadLanguageModule('log');
        
        include_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
        $this->log = ilLoggerFactory::getLogger('log');
    }
    
    /**
     *
     * @return ilLogger
     */
    public function getLogger()
    {
        return $this->log;
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $this->$cmd();

                break;
        }
        return true;
    }
    

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        if ($ilAccess->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                static::SECTION_SETTINGS,
                $this->ctrl->getLinkTargetByClass('ilobjloggingsettingsgui', "settings")
            );
        }
        if ($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
    
    public function setSubTabs($a_section)
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
        include_once("Services/Logging/classes/class.ilLoggingDBSettings.php");
        $this->settings = ilLoggingDBSettings::getInstance();
    }
    
    /**
     * Get log settings
     * @return ilLogSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Show settings
     * @access	public
     */
    public function settings(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
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

    /**
     * Save settings
     * @access	public
     */
    public function updateSettings()
    {
        include_once 'Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getSettings()->setLevel($form->getInput('level'));
            $this->getSettings()->enableCaching($form->getInput('cache'));
            $this->getSettings()->setCacheLevel($form->getInput('cache_level'));
            $this->getSettings()->enableMemoryUsage($form->getInput('memory'));
            $this->getSettings()->enableBrowserLog($form->getInput('browser'));
            $this->getSettings()->setBrowserUsers($form->getInput('browser_users'));
            
            $this->getLogger()->info(print_r($form->getInput('browser_users'), true));
            
            $this->getSettings()->update();
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
            return true;
        }
        
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->settings($form);

        return true;
    }

    /**
     * Init settings form
     *
     */
    protected function initFormSettings()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];
        $ilAccess = $DIC['ilAccess'];

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('logs_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('updateSettings', $this->lng->txt('save'));
        }

        $level = new ilSelectInputGUI($this->lng->txt('log_log_level'), 'level');
        $level->setOptions(ilLogLevel::getLevelOptions());
        $level->setValue($this->getSettings()->getLevel());
        $form->addItem($level);
        
        $cache = new ilCheckboxInputGUI($this->lng->txt('log_cache_'), 'cache');
        $cache->setInfo($this->lng->txt('log_cache_info'));
        $cache->setValue(1);
        $cache->setChecked($this->getSettings()->isCacheEnabled());
        $form->addItem($cache);
        
        $cache_level = new ilSelectInputGUI($this->lng->txt('log_cache_level'), 'cache_level');
        $cache_level->setOptions(ilLogLevel::getLevelOptions());
        $cache_level->setValue($this->getSettings()->getCacheLevel());
        $cache->addSubItem($cache_level);
        
        $memory = new ilCheckboxInputGUI($this->lng->txt('log_memory'), 'memory');
        $memory->setValue(1);
        $memory->setChecked($this->getSettings()->isMemoryUsageEnabled());
        $form->addItem($memory);
        
        // Browser handler
        $browser = new ilCheckboxInputGUI($this->lng->txt('log_browser'), 'browser');
        $browser->setValue(1);
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
    protected function components()
    {
        $this->tabs_gui->activateTab(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_COMPONENTS);
        
        include_once './Services/Logging/classes/class.ilLogComponentTableGUI.php';
        $table = new ilLogComponentTableGUI($this, 'components');
        $table->setEditable($this->checkPermissionBool('write'));
        $table->init();
        $table->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
    }
    
    /**
     * Save form
     */
    protected function saveComponentLevels()
    {
        $this->checkPermission('write');
        
        foreach ($_POST['level'] as $component_id => $value) {
            ilLoggerFactory::getLogger('log')->debug($component_id);
            ilLoggerFactory::getLogger('log')->debug($value);
            include_once './Services/Logging/classes/class.ilLogComponentLevel.php';
            $level = new ilLogComponentLevel($component_id, $value);
            $level->update();
        }
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }
    
    protected function resetComponentLevels()
    {
        $this->checkPermission('write');
        
        foreach (ilLogComponentLevels::getInstance()->getLogComponents() as $component) {
            $component->setLevel(null);
            $component->update();
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'components');
    }

    protected function errorSettings()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
        }

        $this->tabs_gui->setTabActive(static::SECTION_SETTINGS);
        $this->setSubTabs(static::SUB_SECTION_ERROR);

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormErrorSettings();
        }
        $this->tpl->setContent($form->getHTML());

        $this->getLogger()->debug('Currrent level is ' . $this->getSettings()->getLevel());
    }

    protected function updateErrorSettings()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('write', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $form = $this->initFormErrorSettings();
        if ($form->checkInput()) {
            $this->getErrorSettings()->setMail($form->getInput('error_mail'));
            $this->getErrorSettings()->update();

            ilUtil::sendSuccess($this->lng->txt('error_settings_saved'), true);
            $this->ctrl->redirect($this, 'errorSettings');
        }

        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->errorSettings($form);
    }

    protected function initFormErrorSettings()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];
        $ilAccess = $DIC['ilAccess'];

        require_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        require_once './Services/Search/classes/class.ilSearchSettings.php';

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('logs_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
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

    protected function initErrorSettings()
    {
        require_once("Services/Logging/classes/error/class.ilLoggingErrorSettings.php");
        $this->error_settings = ilLoggingErrorSettings::getInstance();
    }

    protected function getErrorSettings()
    {
        return $this->error_settings;
    }
}
