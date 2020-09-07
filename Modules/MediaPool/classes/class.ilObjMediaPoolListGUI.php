<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjMediaPoolListGUI
*
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPoolListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = true;
        #$this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "mep";
        $this->gui_class_name = "ilobjmediapoolgui";
        
        // general commands array
        include_once('Modules/MediaPool/classes/class.ilObjMediaPoolAccess.php');
        $this->commands = ilObjMediaPoolAccess::_getCommands();
    }


    /**
    * inititialize new item
    *
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	string		$a_title		title
    * @param	string		$a_description	description
    */
    public function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
    {
        parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
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
            case "":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
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
        $ilUser = $this->user;

        $props = array();

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
        if ($a_cmd == "infoScreen") {
            $cmd = "&cmd=infoScreenFrameset";
        }

        // separate method for this line
        $cmd_link = "ilias.php?baseClass=ilMediaPoolPresentationGUI" .
            "&ref_id=" . $this->ref_id . '&cmd=' . $a_cmd;

        return $cmd_link;
    }
} // END class.ilObjTestListGUI
