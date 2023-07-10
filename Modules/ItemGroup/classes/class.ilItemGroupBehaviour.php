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
 * Different types of behaviour for item groups
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupBehaviour
{
    public const ALWAYS_OPEN = 0;
    public const EXPANDABLE_CLOSED = 1;
    public const EXPANDABLE_OPEN = 2;

    /**
     * Get all behaviours
     */
    public static function getAll(): array
    {
        global $DIC;

        $lng = $DIC->language();

        return array(
            self::ALWAYS_OPEN => $lng->txt("itgr_always_open"),
            self::EXPANDABLE_CLOSED => $lng->txt("itgr_expandable_closed"),
            self::EXPANDABLE_OPEN => $lng->txt("itgr_expandable_open")
        );
    }
}
