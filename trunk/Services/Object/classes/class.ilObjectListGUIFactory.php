<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjectListGUIFactory
*
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
*/
class ilObjectListGUIFactory
{
	function &_getListGUIByType($a_type)
	{
		global $objDefinition;
		
		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class."ListGUI";
		if(@include_once($location."/class.".$full_class.".php"))
		{
			return new $full_class();
		}

		include_once './Services/Object/classes/class.ilObjectListGUI.php';
		return new ilObjectListGUI($a_type);
	}
	
} // END class.ilObjectListGUIFactory
?>
