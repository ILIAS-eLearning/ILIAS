<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjWorkspaceRootFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjRootFolderListGUI.php 23764 2010-05-06 15:11:30Z smeyer $
*
* @extends ilObjectListGUI
*/
class ilObjWorkspaceRootFolderListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = false;
        $this->delete_enabled = false;
        $this->cut_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->type = "root";
        $this->gui_class_name = "ilobjworkspacerootfoldergui";

        // general commands array
        include_once('./Modules/WorkspaceRootFolder/classes/class.ilObjWorkspaceRootFolderAccess.php');
        $this->commands = ilObjWorkspaceRootFolderAccess::_getCommands();
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
        $ilCtrl = $this->ctrl;

        // does this make any sense!?
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

        return $cmd_link;
    }
} // END class.ilObjWorkspaceRootFolderGUI
