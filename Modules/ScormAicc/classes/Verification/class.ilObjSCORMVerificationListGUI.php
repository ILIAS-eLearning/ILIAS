<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSCORMVerificationListGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderListGUI.php 26089 2010-10-20 08:08:05Z smeyer $
*
* @extends ilObjectListGUI
*/

include_once "Services/Object/classes/class.ilObjectListGUI.php";

class ilObjSCORMVerificationListGUI extends ilObjectListGUI
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
        $this->type = "scov";
        $this->gui_class_name = "ilobjscormverificationgui";

        // general commands array
        include_once('./Modules/ScormAicc/classes/Verification/class.ilObjSCORMVerificationAccess.php');
        $this->commands = ilObjSCORMVerificationAccess::_getCommands();
    }
    
    public function getProperties()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        return array(
            array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("wsp_list_scov"))
        );
    }
} // END class.ilObjTestVerificationListGUI
