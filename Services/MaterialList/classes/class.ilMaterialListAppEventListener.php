<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Material list listener. Listens to events of other components.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMaterialList
 */
class ilMaterialListAppEventListener
{	
	static function handleEvent($a_component, $a_event, $a_parameter)
	{		
		switch($a_component)
		{
			case "Modules/Course":
				switch($a_event)
				{
					case "delete":					
						// proper delete, NOT moving to trash
						include_once "Services/MaterialList/classes/class.ilMaterialList.php";
						ilMaterialList::deleteList($a_parameter["obj_id"]);						
						break;
				}
				break;
		}
	}
}