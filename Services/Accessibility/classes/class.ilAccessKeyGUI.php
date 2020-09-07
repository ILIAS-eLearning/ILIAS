<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Accessibility/classes/class.ilAccessKey.php");

/**
 * Access key user interface handling
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessibility
 */
class ilAccessKeyGUI
{
    /**
     * Get accesskey HTML attribute
     * @static
     * @param int $a_func_id
     * @return string
     */
    public static function getAttribute($a_func_id)
    {
        $key = ilAccessKey::getKey($a_func_id);

        if ($key != "") {
            return 'accesskey="' . $key . '"';
        }

        return "";
    }
}
