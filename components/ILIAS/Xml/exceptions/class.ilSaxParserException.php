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
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
