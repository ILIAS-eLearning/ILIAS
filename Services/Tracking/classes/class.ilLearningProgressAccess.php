<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Learning progress access checks
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesTracking
*/
class ilLearningProgressAccess
{
	/**
	 * check access to learning progress
	 * 
	 * @param int $a_ref_id reference ifd of object
	 * @return
	 * @static
	 */
	public static function checkAccess($a_ref_id)
	{
		global $ilUser,$ilAccess;
		
		if($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			return false;
		}

		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(!ilObjUserTracking::_enabledLearningProgress())
		{
			return false;
		}

		if($ilAccess->checkAccess('edit_learning_progress','',$a_ref_id))
		{
			return true;
		}
		
		include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
		if(ilLPObjSettings::_lookupMode(ilObject::_lookupObjId($a_ref_id)) == LP_MODE_DEACTIVATED)
		{
			return false;
		}
		
		if(!$ilAccess->checkAccess('read','',$a_ref_id))
		{
			return false;
		}
		
		return true;
	}
}
?>
