<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjPortfolioAccess
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjRootFolderAccess.php 15678 2008-01-06 20:40:55Z akill $
*
*/
class ilObjPortfolioAccess
{
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        $t_arr = explode("_", $a_target);
        
        include_once "Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php";
        return ilSharedResourceGUI::hasAccess($t_arr[1], true);
    }
}
