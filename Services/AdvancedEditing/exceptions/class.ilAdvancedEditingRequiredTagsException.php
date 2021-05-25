<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class for advanced editing exception handling in ILIAS.
 *
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAdvancedEditingRequiredTagsException extends ilAdvancedEditingException
{
    /**
    * Constructor
    *
    * A message is not optional as in build in class Exception
    *
    * @access public
    * @param	string	$a_message message
    *
    */
    public function __construct($a_message)
    {
        parent::__construct($a_message);
    }
}
