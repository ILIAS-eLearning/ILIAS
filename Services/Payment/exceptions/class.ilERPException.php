<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Exceptions/classes/class.ilException.php'; 

/** 
* Class for ERP related exception handling in ILIAS. 
* 
* @author Jesper Gødvad <jesper@ilias.dk>
* @version $Id$ 
* 
*/
class ilERPException extends ilException
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
?>