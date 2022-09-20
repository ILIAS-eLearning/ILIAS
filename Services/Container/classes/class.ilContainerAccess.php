<?php

declare(strict_types=1);

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerAccess implements ilWACCheckingClass
{
    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        global $DIC;

        $access = $DIC->access();

        preg_match("/\\/obj_([\\d]*)\\//uim", $ilWACPath->getPath(), $results);
        foreach (ilObject2::_getAllReferences((int) $results[1]) as $ref_id) {
            if ($access->checkAccess('visible', '', $ref_id) || $access->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
