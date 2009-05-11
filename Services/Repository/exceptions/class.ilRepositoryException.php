<?php

include_once('Services/Exceptions/classes/class.ilException.php');

/**
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @ingroup ServicesRepository
*/
class ilRepositoryException extends ilException
{
	public function __construct($a_message,$a_code = 0)
	{
	 	parent::__construct($a_message,$a_code);
	}	
}

?>
