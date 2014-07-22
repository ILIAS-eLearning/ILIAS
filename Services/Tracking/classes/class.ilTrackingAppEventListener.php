<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/** 
* Update lp data from Services/Object events 
* 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesTracking
*/
class ilTrackingAppEventListener implements ilAppEventListener
{
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	public static function handleEvent($a_component, $a_event, $a_params)
	{				
		$obj_id = $a_params['obj_id'];
		
		switch($a_component)
		{
			case 'Services/Object':				
				switch($a_event)
				{					
					case 'toTrash':
						include_once './Services/Object/classes/class.ilObjectLP.php';
						$olp = ilObjectLP::getInstance($obj_id);
						$olp->handleToTrash();
						break;
						
					case 'delete':
						// ilRepUtil will raise "delete" even if only reference was deleted!
						$all_ref = ilObject::_getAllReferences($obj_id);					
						if(!sizeof($all_ref))
						{						
							include_once './Services/Object/classes/class.ilObjectLP.php';
							$olp = ilObjectLP::getInstance($obj_id);
							$olp->handleDelete();
						}
						break;						
				}
				break;
			
			case 'Services/Tree':
				switch ($a_event)
				{
					case 'moveTree':
						if($a_params['tree'] == 'tree')
						{
							include_once './Services/Object/classes/class.ilObjectLP.php';
							ilObjectLP::handleMove($a_params['source_id']);
						}											
						break;
				}
				break;
		}
		
		return true;
	}
}

?>