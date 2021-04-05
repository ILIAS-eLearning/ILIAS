<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * SaxParserException thrown by ilSaxParser if property throwException is set.
 *
 * @author Roland Küstermann <rku@aifb.uka.de>
 */
class ilSaxParserException extends ilException
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
