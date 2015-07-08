<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Factory for component tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCComponentTaskFactory
{
	
	/**
	 * Get component task
	 * @param type $a_component_id
	 */
	public static function getComponentTask($a_component_id)
	{
		switch($a_component_id)
		{
			case 'tree':
				include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
				return new ilSCTreeTasks();
		}
	}
	
}



?>