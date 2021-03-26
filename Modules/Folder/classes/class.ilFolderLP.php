<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Folder to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilFolderLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED
        );
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }
    
    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }
}
