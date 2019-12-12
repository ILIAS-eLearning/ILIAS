<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
 * Class ilObjContactAdministrationGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjContactAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjContactAdministrationGUI: ilAdministrationGUI
 */
class ilObjContactAdministrationGUI extends ilObject2GUI
{
    /**
     * @param int $a_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->lng->loadLanguageModule('buddysystem');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'cadm';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminTabs()
    {
        if ($this->checkPermissionBool('read')) {
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'showConfigurationForm'), array('', 'view', 'showConfigurationForm', 'saveConfigurationForm'), __CLASS__);
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == '' || $cmd == 'view') {
                    $cmd = 'showConfigurationForm';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getConfigurationForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('settings'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveConfigurationForm'));

        $enabled = new ilCheckboxInputGUI($this->lng->txt('buddy_enable'), 'enable');
        $enabled->setValue(1);
        $enabled->setInfo($this->lng->txt('buddy_enable_info'));
        $enabled->setDisabled(!$this->checkPermissionBool('write'));

        $notification = new ilCheckboxInputGUI($this->lng->txt('buddy_use_osd'), 'use_osd');
        $notification->setValue(1);
        $notification->setInfo($this->lng->txt('buddy_use_osd_info'));
        $notification->setDisabled(!$this->checkPermissionBool('write'));
        $enabled->addSubItem($notification);

        $form->addItem($enabled);

        if ($this->checkPermissionBool('write')) {
            $form->addCommandButton('saveConfigurationForm', $this->lng->txt('save'));
        }

        return $form;
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    protected function showConfigurationForm(ilPropertyFormGUI $form = null)
    {
        $this->checkPermission('read');

        if (!($form instanceof ilPropertyFormGUI)) {
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            $cfg = ilNotificationDatabaseHandler::loadUserConfig(-1);

            $form = $this->getConfigurationForm();
            $form->setValuesByArray(array(
                'enable'  => (bool) ilBuddySystem::getInstance()->getSetting('enabled', 0),
                'use_osd' => isset($cfg['buddysystem_request']) && array_search('osd', $cfg['buddysystem_request']) !== false
            ));
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveConfigurationForm()
    {
        $this->checkPermission('write');

        $form = $this->getConfigurationForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showConfigurationForm($form);
            return;
        }

        ilBuddySystem::getInstance()->setSetting('enabled', (bool) $form->getInput('enable') ? 1 : 0);

        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        $cfg = ilNotificationDatabaseHandler::loadUserConfig(-1);

        $new_cfg = array();
        foreach ($cfg as $type => $channels) {
            $new_cfg[$type] = array();
            foreach ($channels as $channel) {
                $new_cfg[$type][$channel] = true;
            }
        }

        if (!isset($new_cfg['buddysystem_request']) || !is_array($new_cfg['buddysystem_request'])) {
            $new_cfg['buddysystem_request'] = array();
        }

        if ((bool) $form->getInput('use_osd') && !array_key_exists('osd', $new_cfg['buddysystem_request'])) {
            $new_cfg['buddysystem_request']['osd'] = true;
        } elseif (!(bool) $form->getInput('use_osd') && array_key_exists('osd', $new_cfg['buddysystem_request'])) {
            $new_cfg['buddysystem_request']['osd'] = false;
        }

        ilNotificationDatabaseHandler::setUserConfig(-1, $new_cfg);

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}
