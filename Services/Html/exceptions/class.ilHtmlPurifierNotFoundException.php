<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/exceptions/class.ilHtmlException.php';

/**
* Class for html related exception handling in ILIAS.
*
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
*
*/
class ilHtmlPurifierNotFoundException extends ilHtmlException
{
    /**
    * Constructor
    *
    * A message is not optional as in build in class Exception
    *
    * @access	public
    * @param	string	$a_message message
    *
    */
    public function __construct($a_message)
    {
        parent::__construct($a_message);
    }
}
