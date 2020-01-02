<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Didactic Template administration gui
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjObjectTemplateAdministrationGUI: ilPermissionGUI, ilDidacticTemplateSettingsGUI
* @ilCtrl_IsCalledBy ilObjObjectTemplateAdministrationGUI: ilAdministrationGUI
*
* @ingroup ServicesPortfolio
*/
class ilObjObjectTemplateAdministrationGUI extends ilObjectGUI
{
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = "otpl";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("didactic");
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];

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

            case 'ildidactictemplatesettingsgui':

                $ilTabs->activateTab('didactic_adm_tab');
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettingsGUI.php';
                $did = new ilDidacticTemplateSettingsGUI($this);
                $this->ctrl->forwardCommand($did);
                break;

            default:

                $ilTabs->activateTab('didactic_adm_tab');
                $this->ctrl->redirectByClass('ildidactictemplatesettingsgui');
                break;
        }
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

        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];

        if ($this->checkPermissionBool('write')) {
            $lng->loadLanguageModule('didactic');
            $ilTabs->addTarget(
                'didactic_adm_tab',
                $this->ctrl->getLinkTargetByClass('ildidactictemplatesettingsgui', 'overview')
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }
}
