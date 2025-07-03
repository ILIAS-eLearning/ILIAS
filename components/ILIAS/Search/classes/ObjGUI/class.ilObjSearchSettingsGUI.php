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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\DI\UIServices;

/**
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_Calls ilObjSearchSettingsGUI: ilPermissionGUI, ilObjSearchSettingsFormGUI, ilObjSearchLuceneSettingsFormGUI
*/
class ilObjSearchSettingsGUI extends ilObjectGUI
{
    protected UIServices $ui;
    protected ilLogger $src_logger;
    protected ilObjUser $user;

    protected bool $read_only = true;

    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->src_logger = $DIC->logger()->src();
        $this->user = $DIC->user();

        $this->type = "seas";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('search');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                return;

            case 'ilobjsearchsettingsformgui':
                $this->tabs_gui->activateTab('settings');
                $settings_gui = new ilObjSearchSettingsFormGUI(
                    $this->http,
                    $this->ctrl,
                    $this->lng,
                    $this->ui,
                    new ilObjSearchRpcClientCoordinator(
                        $this->settings,
                        $this->src_logger
                    )
                );
                $ret = $this->ctrl->forwardCommand($settings_gui);
                return;

            case 'ilobjsearchlucenesettingsformgui':
                $this->tabs_gui->activateTab('lucene_settings_tab');
                $luc_settings_gui = new ilObjSearchLuceneSettingsFormGUI(
                    $this->http,
                    $this->ctrl,
                    $this->lng,
                    $this->ui,
                    $this->refinery,
                    $this->user,
                    new ilObjSearchRpcClientCoordinator(
                        $this->settings,
                        $this->src_logger
                    )
                );
                $ret = $this->ctrl->forwardCommand($luc_settings_gui);
                return;
        }

        switch ($cmd) {
            case '':
            case 'view':
            case 'settings':
                $this->redirectToSettings();
                break;

            case 'luceneSettings':
                $this->redirectToLuceneSettings();
                break;

            default:
                $cmd .= "Object";
                $this->$cmd();
        }
    }

    protected function redirectToSettings(): void
    {
        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs_gui->setTabActive('settings');

        if (!$this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $this->ctrl->redirectByClass(
                [get_class($this),'ilobjsearchsettingsformgui'],
                'readOnly'
            );
        }

        $this->ctrl->redirectByClass(
            [get_class($this),'ilobjsearchsettingsformgui'],
            'edit'
        );
    }

    protected function redirectToLuceneSettings(): void
    {
        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs_gui->setTabActive('lucene_settings_tab');

        if (!$this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $this->ctrl->redirectByClass(
                [get_class($this),'ilobjsearchlucenesettingsformgui'],
                'readOnly'
            );
        }

        $this->ctrl->redirectByClass(
            [get_class($this),'ilobjsearchlucenesettingsformgui'],
            'edit'
        );
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'settings')
            );
        }

        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'lucene_settings_tab',
                $this->lng->txt('lucene_settings_tab'),
                $this->ctrl->getLinkTarget($this, 'luceneSettings')
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(
                    [get_class($this),'ilpermissiongui'],
                    'perm'
                ),
            );
        }
    }
}
