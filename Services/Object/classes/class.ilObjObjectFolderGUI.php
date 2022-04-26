<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjObjectFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrlStructureCalls(
 *		children={
 *			"ilPermissionGUI",
 *		}
 * )
 */
class ilObjObjectFolderGUI extends ilObjectGUI
{
    /**
    * @param	array	basic object data
    * @param	integer	ref_id or obj_id (depends on referenced-flag)
    * @param	boolean	true: treat id as ref_id; false: treat id as obj_id
    */
    public function __construct($data, int $id, bool $call_by_reference)
    {
        $this->type = "objf";
        parent::__construct($data, $id, $call_by_reference, false);
    }

    /**
    * list children of current object
    */
    public function viewObject() : void
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        //prepare objectlist
        $this->data = [];
        $this->data["data"] = [];
        $this->data["ctrl"] = [];
        $this->data["cols"] = ["type","title","last_change"];

        $this->maxcount = count($this->data["data"]);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = [
                "ref_id" => $this->id,
                "obj_id" => $val["obj_id"],
                "type" => $val["type"],
            ];

            unset($this->data["data"][$key]["obj_id"]);
            $this->data["data"][$key]["last_change"] = ilDatePresentation::formatDate(
                new ilDateTime($this->data["data"][$key]["last_change"], IL_CAL_DATETIME)
            );
        }

        // TODO: method 'displayList' is undefined
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
                $this->ctrl->forwardCommand($perm_gui);
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

    protected function getTabs() : void
    {
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                ["view",""]
            );

            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass([get_class($this),'ilpermissiongui'], "perm"),
                ["perm","info","owner"],
                'ilpermissiongui'
            );
        }
    }
}
