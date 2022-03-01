<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjObjectFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjObjectFolderGUI: ilPermissionGUI
 */
class ilObjObjectFolderGUI extends ilObjectGUI
{
    /**
    * Constructor
    *
    * @param	array	basic object data
    * @param	integer	ref_id or obj_id (depends on referenced-flag)
    * @param	boolean	true: treat id as ref_id; false: treat id as obj_id
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        $this->type = "objf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
    * list childs of current object
    *
    * @access	public
    */
    public function viewObject() : void
    {
        $rbacsystem = $this->rbac_system;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        //prepare objectlist
        $this->data = array();
        $this->data["data"] = array();
        $this->data["ctrl"] = array();

        $this->data["cols"] = array("type","title","last_change");

        $this->maxcount = count($this->data["data"]);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = array(
                                            "ref_id" => $this->id,
                                            "obj_id" => $val["obj_id"],
                                            "type" => $val["type"],
                                            );

            unset($this->data["data"][$key]["obj_id"]);
            $this->data["data"][$key]["last_change"] = ilDatePresentation::formatDate(new ilDateTime($this->data["data"][$key]["last_change"], IL_CAL_DATETIME));
        }

        $this->displayList();
    }


    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    protected function getTabs() : void
    {
        $rbacsystem = $this->rbac_system;

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                array("view",""),
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
} // END class.ilObjObjectFolderGUI
