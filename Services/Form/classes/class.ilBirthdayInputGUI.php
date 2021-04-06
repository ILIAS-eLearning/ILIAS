<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a text property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBirthdayInputGUI extends ilDateTimeInputGUI
{
    public function getStartYear()
    {
        return date("Y") - 100;
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
