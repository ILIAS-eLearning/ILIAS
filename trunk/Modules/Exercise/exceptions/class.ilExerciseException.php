<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
 
require_once 'Services/Exceptions/classes/class.ilException.php'; 
 
/** 
 * Exercise exceptions class 
 * 
 * @author Alex Killing <alex.killing@hmx.de>
 * @version $Id$ 
 * 
 */
class ilExerciseException extends ilException
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
?>
