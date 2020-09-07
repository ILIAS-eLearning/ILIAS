<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/AdvancedEditing/exceptions/class.ilAdvancedEditingException.php';

/**
* Class for advanced editing exception handling in ILIAS.
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
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
