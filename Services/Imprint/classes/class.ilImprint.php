<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilImprint
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilImprint extends ilPageObject
{
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "impr";
    }

    public static function isActive()
    {
        return self::_lookupActive(1, "impr");
    }
}
