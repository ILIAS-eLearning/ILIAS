<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjPortfolioAccess
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPortfolioAccess
{
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        $t_arr = explode("_", $a_target);
        
        return ilSharedResourceGUI::hasAccess($t_arr[1], true);
    }
}
