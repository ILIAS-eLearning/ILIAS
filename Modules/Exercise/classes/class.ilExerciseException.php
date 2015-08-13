<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Exceptions/classes/class.ilException.php';

/**
*
* Class to report exception
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id: class.ilExerciseException.php 12992 2007-01-25 10:04:26Z rkuester $
* @ingroup ModulesExercise
*/
class ilExerciseException extends ilException
{
    public static $ID_MISMATCH = 0;
    public static $ID_DEFLATE_METHOD_MISMATCH = 1;
    /**
	 * A message isn't optional as in build in class Exception
	 *
	 * @access public
	 *
	 */
	public function __construct($a_message,$a_code = 0)
	{
	 	parent::__construct($a_message,$a_code);
	}
}

?>
