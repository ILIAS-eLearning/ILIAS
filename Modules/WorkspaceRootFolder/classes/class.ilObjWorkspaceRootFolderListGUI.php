<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjWorkspaceRootFolderListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
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
