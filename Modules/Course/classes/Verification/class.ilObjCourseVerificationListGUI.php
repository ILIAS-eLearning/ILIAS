<?php declare(strict_types=0);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjCourseVerificationListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjCourseVerificationListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = 'crsv';
        $this->gui_class_name = ilObjCourseVerificationGUI::class;

        $this->commands = ilObjCourseVerificationAccess::_getCommands();
    }

    public function getProperties() : array
    {
        global $DIC;

        $lng = $DIC->language();

        return [
            [
                'alert' => false,
                'property' => $lng->txt('type'),
                'value' => $lng->txt('wsp_list_crsv')
            ]
        ];
    }
}
