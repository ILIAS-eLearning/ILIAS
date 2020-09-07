<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* SaxParserException thrown by ilSaxParser if property throwException is set.
*
* @author Roland Küstermann <rku@aifb.uka.de>
* @version $Id: class.ilSaxParser.php 12808 2006-12-08 18:04:21Z akill $
*
* @extends PEAR
*/

require_once('Services/Exceptions/classes/class.ilException.php');
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
