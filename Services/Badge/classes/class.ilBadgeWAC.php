<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeWAC implements ilWACCheckingClass
{
    public function canBeDelivered(ilWACPath $ilWACPath): bool
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
