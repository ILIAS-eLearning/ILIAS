<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Access key user interface handling
 * @author  Alex Killing <alex.killing@gmx.de>
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
