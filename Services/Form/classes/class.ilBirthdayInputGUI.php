<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilBirthdayInputGUI extends ilDateTimeInputGUI
{
    public function getStartYear()
    {
        return date("Y")-100;
    }
    
    protected function parseDatePickerConfig()
    {
        $config = parent::parseDatePickerConfig();
                
        $config["viewMode"] = "years";
        $config["calendarWeeks"] = false;
        $config["showTodayButton"] = false;
        
        return $config;
    }
}
