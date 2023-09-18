<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * ilCtrl exceptions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCtrlException extends ilException
{
    /**
     * Constructor
     *
     * @param        string $a_message message
     */
    public function __construct($a_message)
    {
        parent::__construct($a_message);
    }
}
