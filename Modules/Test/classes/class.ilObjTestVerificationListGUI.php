<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestVerificationListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjTestVerificationListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = 'tstv';
        $this->gui_class_name = ilObjTestVerificationGUI::class;

        $this->commands = ilObjTestVerificationAccess::_getCommands();
    }

    public function getProperties(): array
    {
        global $DIC;
        $lng = $DIC['lng'];

        return [
            [
                'alert' => false,
                'property' => $lng->txt('type'),
                'value' => $lng->txt('wsp_list_tstv')
            ]
        ];
    }
}
