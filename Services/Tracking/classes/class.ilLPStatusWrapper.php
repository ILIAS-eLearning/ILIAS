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
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

class ilLPStatusWrapper()
{

	/**
	* Static function to read the number of user who have the status 'not_attempted'
	*/
	function _getCountNotAttempted($a_obj_id)
	{
		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		return call_user_func(array($class,'_getCountNotAttempted'),$a_obj_id);

	}

	/**
	* Static function to read the number of user who have the status 'in_progress'
	*/
	function _getCountInProgress($a_obj_id)
	{
		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		return call_user_func(array($class,'_getCountInProgress'),$a_obj_id);
	}
	
	/**
	* Static function to read the number of user who have the status 'completed'
	*/
	function _getCountCompleted($a_obj_id)
	{
		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		return call_user_func(array($class,'_getCountCompleted'),$a_obj_id);

	}

}	
?>