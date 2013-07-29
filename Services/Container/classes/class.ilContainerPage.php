<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Container page object
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup ServicesContainer
 */
class ilContainerPage extends ilPageObject
{
	/**
	* Constructor
	* @access	public
	* @param	wiki page id
	*/
	function __construct($a_type, $a_id = 0, $a_old_nr = 0)
	{
		parent::__construct($a_type, $a_id, $a_old_nr);
	}

}
?>
