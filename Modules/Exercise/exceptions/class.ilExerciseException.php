<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise exceptions class
 *
 * @author Alex Killing <alex.killing@hmx.de>, Roland Küstermann <roland@kuestermann.com>
 */
class ilExerciseException extends ilException
{
    public static $ID_MISMATCH = 0;
    public static $ID_DEFLATE_METHOD_MISMATCH = 1;
    /**
     * Constructor
     *
     * @param        string $a_message message
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
