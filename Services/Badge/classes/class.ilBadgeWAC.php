<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Class ilBadgeHandler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeWAC implements ilWACCheckingClass
{
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        if (strpos($ilWACPath->getPath(), '..') !== false) {
            return false;
        }

        if (preg_match('@ilBadge\/badge(.*?)\/@ui', $ilWACPath->getPath())) {
            return true;
        }

        return false;
    }
}
