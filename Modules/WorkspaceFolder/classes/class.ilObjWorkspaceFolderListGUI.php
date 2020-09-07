<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjWorkspaceFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderListGUI.php 26089 2010-10-20 08:08:05Z smeyer $
*
* @extends ilObjectListGUI
*/

include_once "Services/Object/classes/class.ilObjectListGUI.php";

class ilObjWorkspaceFolderListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = "wfld";
        $this->gui_class_name = "ilobjworkspacefoldergui";

        // general commands array
        include_once('./Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderAccess.php');
        $this->commands = ilObjWorkspaceFolderAccess::_getCommands();
    }
} // END class.ilObjFolderListGUI
