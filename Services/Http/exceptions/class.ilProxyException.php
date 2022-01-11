<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* Class for proxy related exception handling in ILIAS.
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*/
class ilProxyException extends ilException
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
