<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjPollListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPollListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "poll";
        $this->gui_class_name = "ilobjpollgui";

        // general commands array
        $this->commands = ilObjPollAccess::_getCommands();
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

        // BEGIN ChangeEvent: Get parent properties
        $props = parent::getProperties();
        // END ChangeEvent: Get parent properties


        return $props;
    }
}
