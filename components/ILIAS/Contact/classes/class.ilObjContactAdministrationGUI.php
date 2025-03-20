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

use ILIAS\Notifications\ilNotificationDatabaseHandler;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

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
        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd() ?? '';
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

    protected function getConfigurationForm(): StandardForm
    {
        $notification = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('buddy_use_osd'),
            $this->lng->txt('buddy_use_osd_info')
        )
            ->withDisabled(!$this->checkPermissionBool('write'));

        $cfg = ilNotificationDatabaseHandler::loadUserConfig(-1);
        $checkbox = $this->ui_factory->input()->field()->optionalGroup(
            ['use_osd' => $notification],
            $this->lng->txt('buddy_enable'),
            $this->lng->txt('buddy_enable_info')
        )
            ->withValue(
                [
                    'use_osd' => isset($cfg['buddysystem_request']) &&
                        in_array('osd', $cfg['buddysystem_request'], true)
                ]
            )
            ->withDisabled(!$this->checkPermissionBool('write'));

        if (ilBuddySystem::getInstance()->getSetting('enabled', '0') === '0') {
            $checkbox = $checkbox->withValue(null);
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveConfigurationForm'),
            [
                'enable' => $checkbox,
            ]
        );
    }

    protected function showConfigurationForm(?StandardForm $form = null): void
    {
        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        if (!$form instanceof StandardForm) {
            $form = $this->getConfigurationForm();
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function saveConfigurationForm(): void
    {
        $this->checkPermission('write');

        $form = $this->getConfigurationForm();
        $form = $form->withRequest($this->request);
        if ($form->getError()) {
            $this->showConfigurationForm($form);

            return;
        }

        $data = $form->getData();

        ilBuddySystem::getInstance()->setSetting(
            'enabled',
            (string) (isset($data['enable']) && $data['enable'] ? 1 : 0)
        );

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

        if (!array_key_exists('osd', $new_cfg['buddysystem_request']) &&
            isset($data['enable']['use_osd']) && $data['enable']['use_osd'] === true) {
            $new_cfg['buddysystem_request']['osd'] = true;
        } elseif (
            array_key_exists('osd', $new_cfg['buddysystem_request']) &&
            isset($data['enable']) &&
            (!isset($data['enable']['use_osd']) || $data['enable']['use_osd'] === false)
        ) {
            $new_cfg['buddysystem_request']['osd'] = false;
        }

        ilNotificationDatabaseHandler::setUserConfig(-1, $new_cfg);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this);
    }
}
