<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerVerificationListGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilObjLTIConsumerVerificationListGUI extends ilObjectListGUI
{
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = "ltiv";
        $this->gui_class_name = ilObjLTIConsumerVerificationGUI::class;
        
        // general commands array
        $this->commands = ilObjLTIConsumerVerificationAccess::_getCommands();
    }
    
    public function getProperties()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return array(
            array("alert" => false, "property" => $DIC->language()->txt("type"),
                "value" => $lng->txt("wsp_list_ltiv"))
        );
    }
}
