<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");

/**
 * Style settings GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjStyleSettingsGUI: ilPermissionGUI, ilSystemStyleMainGUI, ilContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjStyleSettingsGUI: ilPageLayoutAdministrationGUI
 *
 * @ingroup	ServicesStyle
 */
class ilObjStyleSettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    //page_layout editing
    public $pg_id = null;

    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    public $lng;

    /**
     * @var ilTemplate
     */
    public $tpl;

    /**
     * Constructor
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();

        $this->type = "stys";

        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("style");
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if ($next_class == "" && in_array($cmd, array("view", ""))) {
            $this->ctrl->redirectByClass("ilSystemStyleMainGUI", "");
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs->activateTab("perm_settings");
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsystemstylemaingui':
                $this->prepareOutput();
                $this->tabs->activateTab("system_styles");
                include_once("./Services/Style/System/classes/class.ilSystemStyleMainGUI.php");
                $gui = new ilSystemStyleMainGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilpagelayoutadministrationgui':
                $this->prepareOutput();
                $this->tabs->activateTab("page_layouts");
                include_once("./Services/COPage/Layout/classes/class.ilPageLayoutAdministrationGUI.php");
                $gui = new ilPageLayoutAdministrationGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilcontentstylesettingsgui':
                include_once("./Services/Style/Content/classes/class.ilContentStyleSettingsGUI.php");
                $gui = new ilContentStyleSettingsGUI($this);
                $this->ctrl->forwardCommand($gui);
                if ($this->ctrl->getCmdClass() == "ilcontentstylesettingsgui") {
                    $this->tabs->activateTab("content_styles");
                }
                break;

            default:
                $this->prepareOutput();
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
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


    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
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

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }
}
