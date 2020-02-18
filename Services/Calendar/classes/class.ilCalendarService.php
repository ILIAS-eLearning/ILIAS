<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilService.php");

/**
 * Class ilCronService
 *
 * @author Jesús López Reyes<lopez@leifos.com>
 * @version $Id:
 *
 * @ingroup ServicesCron
 */
class ilCalendarService extends ilService
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
