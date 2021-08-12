<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjExerciseVerificationListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @extends ilObjectListGUI
*/

class ilObjExerciseVerificationListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = 'excv';
        $this->gui_class_name = ilObjExerciseVerificationGUI::class;

        $this->commands = ilObjExerciseVerificationAccess::_getCommands();
    }
    
    public function getProperties() : array
    {
        $lng = $this->lng;
        
        return [
            [
                'alert' => false,
                'property' => $lng->txt('type'),
                'value' => $lng->txt('wsp_list_excv')
            ]
        ];
    }
}
