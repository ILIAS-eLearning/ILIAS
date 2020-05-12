<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjDataCollectionListGUI
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @extends ilObjectListGUI
 */
class ilObjBibliographicListGUI extends ilObjectListGUI
{

    /**
     * initialisation
     */
    public function init()
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "bibl";
        $this->gui_class_name = "ilobjbibliographicgui";
        // general commands array
        include_once('./Modules/Bibliographic/classes/class.ilObjBibliographicAccess.php');
        $this->commands = ilObjBibliographicAccess::_getCommands();
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
        include_once("./Modules/Bibliographic/classes/class.ilObjBibliographicAccess.php");
        if (!ilObjBibliographicAccess::_lookupOnline($this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"),
            );
        }

        return $props;
    }
}
