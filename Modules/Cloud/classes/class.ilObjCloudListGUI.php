<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * Class ilObjCloudListGUI
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * $Id:
 *
 * @extends ilObjectListGUI
 */
class ilObjCloudListGUI extends ilObjectListGUI
{
    /**
     * initialisation
     */
    public function init()
    {
        global $lng;

        $this->copy_enabled        = false;
        $this->delete_enabled      = true;
        $this->cut_enabled         = false;
        $this->subscribe_enabled   = true;
        $this->link_enabled        = false;
        $this->payment_enabled     = false;
        $this->info_screen_enabled = true;
        $this->timings_enabled     = true;
        $this->type                = "cld";
        $this->gui_class_name      = "ilobjcloudgui";

        // general commands array
        include_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        $this->commands = ilObjCloudAccess::_getCommands();
        $lng->loadLanguageModule("cld");
    }


    /**
     * @return array
     */
    function getProperties()
    {
        global $lng;

        $props = array();
        include_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        if (!ilObjCloudAccess::checkAuthStatus($this->obj_id))
        {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                             "value" => $lng->txt("cld_not_authenticated_offline"));
        }
        else if (!ilObjCloudAccess::checkOnline($this->obj_id))
        {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                             "value" => $lng->txt("offline"));
        }

        return $props;
    }
}
?>
