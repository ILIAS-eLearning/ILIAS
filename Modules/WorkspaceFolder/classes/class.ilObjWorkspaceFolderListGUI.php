<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjWorkspaceFolderListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
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
        $this->commands = ilObjWorkspaceFolderAccess::_getCommands();
    }
}
