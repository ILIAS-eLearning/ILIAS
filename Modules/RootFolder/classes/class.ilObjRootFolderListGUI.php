<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjRootFolderListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjRootFolderListGUI extends ilObjectListGUI
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->type = "root";
        $this->gui_class_name = "ilobjrootfoldergui";

        // general commands array
        $this->commands = ilObjRootFolderAccess::_getCommands();
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
        global $ilCtrl;

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

        return $cmd_link;
    }
} // END class.ilObjRootFolderGUI
