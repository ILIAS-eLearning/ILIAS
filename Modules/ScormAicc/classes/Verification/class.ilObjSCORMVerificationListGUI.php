<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilObjSCORMVerificationListGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjSCORMVerificationListGUI extends ilObjectListGUI
{
    /**
     * @return void
     */
    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = 'scov';
        $this->gui_class_name = ilObjSCORMVerificationGUI::class;

        $this->commands = ilObjSCORMVerificationAccess::_getCommands();
    }
    
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProperties() : array
    {
        global $DIC;
        $lng = $DIC->language();
        
        return [
            [
                'alert' => false,
                'property' => $lng->txt('type'),
                'value' => $lng->txt('wsp_list_scov')
            ]
        ];
    }
}
