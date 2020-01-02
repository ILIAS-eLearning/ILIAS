<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContentPageListGUI
 */
class ilObjContentPageListGUI extends \ilObjectListGUI implements \ilContentPageObjectConstants
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled      = true;
        $this->cut_enabled         = true;
        $this->copy_enabled        = true;
        $this->subscribe_enabled   = true;
        $this->link_enabled        = true;
        $this->info_screen_enabled = true;
        $this->type                = self::OBJ_TYPE;
        $this->gui_class_name      = 'ilObjContentPageGUI';

        $this->commands = ilObjContentPageAccess::_getCommands();
    }

    /**
     * @inheritdoc
     */
    public function getInfoScreenStatus()
    {
        if (\ilContainer::_lookupContainerSetting(
            $this->obj_id,
            \ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            true
        )) {
            return $this->info_screen_enabled;
        }

        return false;
    }
}
