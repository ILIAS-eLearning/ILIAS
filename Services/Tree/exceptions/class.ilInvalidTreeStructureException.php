<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Exceptions/classes/class.ilException.php';

/**
 * Thrown if invalid tree strucutes are found
 * 
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTree
 * 
 */
class ilInvalidTreeStructureException extends ilException
{
	public function __construct($a_message, $a_code = 0)
	{
		parent::__construct($a_message, $a_code);
	}
}
?>
