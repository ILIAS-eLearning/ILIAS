<?php

require_once('Services/Exceptions/classes/class.ilException.php');

/**
 * Base Exception for Module Test
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @ingroup ModulesTest
 */
class ilTestException extends ilException
{
	/**
	 * ilTestException Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_message,$a_code = 0)
	{
	 	parent::__construct($a_message,$a_code);
	}
}

