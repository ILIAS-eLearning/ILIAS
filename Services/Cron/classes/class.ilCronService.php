<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilService.php");

/**
 * Class ilCronService
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:
 *
 * @ingroup ServicesCron
 */
class ilCronService extends ilService
{
    public function isCore()
    {
        return true;
    }
    
    public function getVersion()
    {
        return "-";
    }
}
