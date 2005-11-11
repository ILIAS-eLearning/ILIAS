<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilLPStatusFactory
* Creates status class instances for learning progress modes of an object.
* E.g obj_id of course returns an instance of ilLPStatusManual, ilLPStatusObjectives ...
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/


class ilLPStatusFactory()
{
	function _getClassById($a_obj_id)
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		
		switch(ilLPObjSettings::_lookupMode($a_obj_id))
		{
			case LP_MODE_COLLECTION:
				return 'ilLPStatusCollection';

			case LP_MODE_TLT:
				return 'ilLPStatusTypicalLearningTime';

			default:
				return 'ilLPStatusManual';
		}
	}

	function &_getInstance()
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		
		switch(ilLPObjSettings::_lookupMode($a_obj_id))
		{
			case LP_MODE_COLLECTION:
				return new ilLPStatusCollection($a_obj_id);

			case LP_MODE_TLT:
				include_once 'Services/Tracking/classes/class.ilLPStatusTypicalLearningTime.php';

				return new ilLPStatusTypicalLearningTime($a_obj_id);

			default:
				include_once 'Services/Tracking/classes/class.ilLPStatusManual.php';

				return new ilLPStatusManual($a_obj_id);
		}
	}
}	
?>