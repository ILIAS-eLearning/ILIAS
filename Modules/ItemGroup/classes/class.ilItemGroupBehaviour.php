<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Different types of behaviour for item groups
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesItemGroup
 */
class ilItemGroupBehaviour
{
    const ALWAYS_OPEN = 0;
    const EXPANDABLE_CLOSED = 1;
    const EXPANDABLE_OPEN = 2;

    /**
     * Get all behaviours
     *
     * @return array
     */
    public static function getAll()
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
