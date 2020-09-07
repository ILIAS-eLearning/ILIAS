<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Class ilObjRootFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/


include_once "Services/Object/classes/class.ilObjectListGUI.php";

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
        include_once('./Modules/RootFolder/classes/class.ilObjRootFolderAccess.php');
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
