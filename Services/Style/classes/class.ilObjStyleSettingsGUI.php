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

/**
 * Style settings GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjStyleSettingsGUI: ilPermissionGUI, ilSystemStyleMainGUI, ilContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjStyleSettingsGUI: ilPageLayoutAdministrationGUI
 */
class ilObjStyleSettingsGUI extends ilObjectGUI
{
    /**
     * Constructor
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        $this->type = "stys";

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("style");
    }

    /**
     * Execute command
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if ($next_class == "" && in_array($cmd, array("view", ""))) {
            $this->ctrl->redirectByClass("ilSystemStyleMainGUI", "");
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab("perm_settings");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsystemstylemaingui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab("system_styles");
                $gui = new ilSystemStyleMainGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilpagelayoutadministrationgui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab("page_layouts");
                $gui = new ilPageLayoutAdministrationGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilcontentstylesettingsgui':
                $gui = new ilContentStyleSettingsGUI($this);
                $this->ctrl->forwardCommand($gui);
                if ($this->ctrl->getCmdClass() == "ilcontentstylesettingsgui") {
                    $this->tabs_gui->activateTab("content_styles");
                }
                break;

            default:
                $this->prepareOutput();
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    /**
     * ???
     * Save object
     */
    /*	function saveObject()
        {
            global $rbacadmin;

            // create and insert forum in objecttree
            $newObj = parent::saveObject();

            // put here object specific stuff

            // always send a message
            ilUtil::sendInfo($this->lng->txt("object_added"),true);

            ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"","",false,false)));
        }*/


    public function getAdminTabs() : void
    {
        $this->getTabs();
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "system_styles",
                $this->lng->txt("system_styles"),
                $this->ctrl->getLinkTargetByClass("ilsystemstylemaingui")
            );

            $this->tabs_gui->addTab(
                "content_styles",
                $this->lng->txt("content_styles"),
                $this->ctrl->getLinkTargetByClass("ilcontentstylesettingsgui", "edit")
            );

            $this->tabs_gui->addTab(
                "page_layouts",
                $this->lng->txt("page_layouts"),
                $this->ctrl->getLinkTargetByClass("ilpagelayoutadministrationgui", "")
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }
}
