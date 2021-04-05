<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Base class for ILIAS Exception handling. Any Exception class should inherit from it
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilException extends Exception
{
    /**
     * A message isn't optional as in build in class Exception
     *
     * @access public
     *
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
