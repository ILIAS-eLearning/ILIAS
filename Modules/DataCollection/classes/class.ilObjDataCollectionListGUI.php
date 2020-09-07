<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjDataCollectionListGUI
 *
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
 *
 * @extends ilObjectListGUI
 */
class ilObjDataCollectionListGUI extends ilObjectListGUI
{

    /**
     * initialisation
     */
    public function init()
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "dcl";
        $this->gui_class_name = "ilobjdatacollectiongui";

        // general commands array
        include_once('./Modules/DataCollection/classes/class.ilObjDataCollectionAccess.php');
        $this->commands = ilObjDataCollectionAccess::_getCommands();
    }


    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                    "alert" (boolean) => display as an alert property (usually in red)
     *                    "property" (string) => property name
     *                    "value" (string) => property value
     */
    public function getProperties()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $props = array();
        include_once("./Modules/DataCollection/classes/class.ilObjDataCollectionAccess.php");

        if (!ilObjDataCollectionAccess::_lookupOnline($this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"),
            );
        }

        return $props;
    }
}
