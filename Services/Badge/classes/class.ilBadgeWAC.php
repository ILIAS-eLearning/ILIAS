<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilBadgeHandler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeWAC implements ilWACCheckingClass
{
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        return true;
    }
}
