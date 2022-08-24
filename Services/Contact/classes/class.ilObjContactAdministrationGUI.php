<?php

declare(strict_types=1);

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

use ILIAS\Notifications\ilNotificationDatabaseHandler;

/**
 * Class ilObjContactAdministrationGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls      ilObjContactAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjContactAdministrationGUI: ilAdministrationGUI
 */
class ilObjContactAdministrationGUI extends ilObject2GUI
{
    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function getType(): string
    {
        return 'cadm';
    }

    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool('read')) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'showConfigurationForm'),
                ['', 'view', 'showConfigurationForm', 'saveConfigurationForm'],
                self::class
            );
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm'),
                ['perm', 'info', 'owner'],
                ilPermissionGUI::class
            );
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch (strtolower($next_class)) {
            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd === '' || $cmd === 'view') {
                    $cmd = 'showConfigurationForm';
                }
                $this->$cmd();
                break;
        }
    }


    protected function getConfigurationForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('settings'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveConfigurationForm'));

        $enabled = new ilCheckboxInputGUI($this->lng->txt('buddy_enable'), 'enable');
        $enabled->setValue('1');
        $enabled->setInfo($this->lng->txt('buddy_enable_info'));
        $enabled->setDisabled(!$this->checkPermissionBool('write'));

        $notification = new ilCheckboxInputGUI($this->lng->txt('buddy_use_osd'), 'use_osd');
        $notification->setValue('1');
        $notification->setInfo($this->lng->txt('buddy_use_osd_info'));
        $notification->setDisabled(!$this->checkPermissionBool('write'));
        $enabled->addSubItem($notification);

        $form->addItem($enabled);

        if ($this->checkPermissionBool('write')) {
            $form->addCommandButton('saveConfigurationForm', $this->lng->txt('save'));
        }

        return $form;
    }


    protected function showConfigurationForm(ilPropertyFormGUI $form = null): void
    {
        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        if (!($form instanceof ilPropertyFormGUI)) {
            $cfg = ilNotificationDatabaseHandler::loadUserConfig(-1);

            $form = $this->getConfigurationForm();
            $form->setValuesByArray([
                'enable' => (bool) ilBuddySystem::getInstance()->getSetting('enabled', '0'),
                'use_osd' => isset($cfg['buddysystem_request']) && in_array('osd', $cfg['buddysystem_request'], true)
            ]);
        }

        $this->tpl->setContent($form->getHTML());
    }


    protected function saveConfigurationForm(): void
    {
        $this->checkPermission('write');

        $form = $this->getConfigurationForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showConfigurationForm($form);
            return;
        }

        ilBuddySystem::getInstance()->setSetting('enabled', (string) ($form->getInput('enable') ? 1 : 0));

        $cfg = ilNotificationDatabaseHandler::loadUserConfig(-1);

        $new_cfg = [];
        foreach ($cfg as $type => $channels) {
            $new_cfg[$type] = [];
            foreach ($channels as $channel) {
                $new_cfg[$type][$channel] = true;
            }
        }

        if (!isset($new_cfg['buddysystem_request']) || !is_array($new_cfg['buddysystem_request'])) {
            $new_cfg['buddysystem_request'] = [];
        }

        if (!array_key_exists('osd', $new_cfg['buddysystem_request']) && $form->getInput('use_osd')) {
            $new_cfg['buddysystem_request']['osd'] = true;
        } elseif (array_key_exists('osd', $new_cfg['buddysystem_request']) && !(bool) $form->getInput('use_osd')) {
            $new_cfg['buddysystem_request']['osd'] = false;
        }

        ilNotificationDatabaseHandler::setUserConfig(-1, $new_cfg);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}
