<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * ListGUI class for media cast objects.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaCastListGUI extends ilObjectListGUI
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
        $this->type = "mcst";
        $this->gui_class_name = "ilobjmediacastgui";
        
        // general commands array
        $this->commands = ilObjMediaCastAccess::_getCommands();
    }


    /**
    * Get command target frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        switch ($a_cmd) {
            default:
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
        }

        return $frame;
    }



    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        $lng = $this->lng;
        $props = array();

        if (!ilObjMediaCastAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }

        return $props;
    }


    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        // separate method for this line
        $cmd_link = "ilias.php?baseClass=ilMediaCastHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$a_cmd";

        return $cmd_link;
    }

    public function setChildId($a_child_id)
    {
        $this->child_id = $a_child_id;
    }
    public function getChildId()
    {
        return $this->child_id;
    }
} // END class.ilObjMediaCastListGUI
