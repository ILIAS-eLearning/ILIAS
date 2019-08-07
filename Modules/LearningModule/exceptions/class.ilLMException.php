<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base exception class for learning modules
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$ 
 * 
 */
class ilLMException extends ilException
{
	/** 
	 * Constructor
	 * 
	 * A message is not optional as in build in class Exception
	 * 
	 * @param string $a_message message 
	 */
	public function __construct($a_message)
	{
	 	parent::__construct($a_message);
	}
}
?>
