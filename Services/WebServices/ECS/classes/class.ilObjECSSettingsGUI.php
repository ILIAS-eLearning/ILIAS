<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ilCtrl_Calls ilObjECSSettingsGUI: ilPermissionGUI, ilECSSettingsGUI
*/
class ilObjECSSettingsGUI extends ilObjectGUI
{
    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        $this->type = 'cals';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('jscalendar');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case 'ilecssettingsgui':
                $this->tabs_gui->setTabActive('settings');
                $settings = new ilECSSettingsGUI();
                $this->ctrl->forwardCommand($settings);
                break;
            
            default:
                $this->tabs_gui->setTabActive('settings');
                $settings = new ilECSSettingsGUI();
                $this->ctrl->setCmdClass('ilecssettingsgui');
                $this->ctrl->forwardCommand($settings);
                break;
        }
    }
    

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs() : void
    {
        if ($this->access->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTargetByClass('ilecssettingsgui', "overview"),
                array(),
                'ilecssettingsgui'
            );
        }
        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }
}
