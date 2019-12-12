<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiVerficationListGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiVerificationListGUI extends ilObjectListGUI
{
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = "cmxv";
        $this->gui_class_name = ilObjCmiXapiVerificationGUI::class;
        
        // general commands array
        $this->commands = ilObjCmiXapiVerificationAccess::_getCommands();
    }
    
    public function getProperties()
    {
        global $lng;
        
        return array(
            array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("wsp_list_cmxv"))
        );
    }
}
