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
* This class is wrapper for all ilLPStatus classes.
* It caches all function calls using the obj_id as key
* TODO: substitute all ilStatus calls with this functions
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
include_once 'Services/Tracking/classes/class.ilLPStatusFactory.php';

class ilLPStatusWrapper
{

	/**
	* Static function to read the number of user who have the status 'not_attempted'
	*/
	function _getCountNotAttempted($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getNotAttempted($a_obj_id));
	}

	/**
	* Static function to read the number of user who have the status 'not_attempted'
	*/
	function _getNotAttempted($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}

		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		$cache[$a_obj_id] = call_user_func(array($class,'_getNotAttempted'),$a_obj_id);
		
		return $cache[$a_obj_id];
	}

	/**
	* Static function to read the number of user who have the status 'in_progress'
	*/
	function _getCountInProgress($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getInProgress($a_obj_id));
	}

	/**
	* Static function to read users who have the status 'in_progress'
	*/
	function _getInProgress($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}

		global $ilBench;

		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		$cache[$a_obj_id] = call_user_func($tmp = array($class,'_getInProgress'),$a_obj_id);

		return $cache[$a_obj_id];
	}
	
	/**
	* Static function to read the number of user who have the status 'completed'
	*/
	function _getCountCompleted($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getCompleted($a_obj_id));
	}

	/**
	* Static function to read the users who have the status 'completed'
	*/
	function _getCompleted($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}
		$class = ilLPStatusFactory::_getClassById($a_obj_id);
		$cache[$a_obj_id] = call_user_func(array($class,'_getCompleted'),$a_obj_id);

		return $cache[$a_obj_id];
	}

	/**
	* Static function to read the number of user who have the status 'failed'
	*/
	function _getCountFailed($a_obj_id)
	{
		return count(ilLPStatusWrapper::_getFailed($a_obj_id));
	}

	/**
	* Static function to read the users who have the status 'completed'
	*/
	function _getFailed($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}

		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		$cache[$a_obj_id] = call_user_func(array($class,'_getFailed'),$a_obj_id);

		return $cache[$a_obj_id];
	}

	/**
	* Reads informations about the object e.g test results, tlt, number of visits
	*/
	function _getStatusInfo($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}

		$class = ilLPStatusFactory::_getClassById($a_obj_id);
		$cache[$a_obj_id] = call_user_func(array($class,'_getStatusInfo'),$a_obj_id);
		return $cache[$a_obj_id];
	}

	/**
	* Reads Typical learning time. Mode collection is recursive for all assigned items
	*/
	function _getTypicalLearningTime($a_obj_id)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id]))
		{
			return $cache[$a_obj_id];
		}

		$class = ilLPStatusFactory::_getClassById($a_obj_id);

		$cache[$a_obj_id] = call_user_func(array($class,'_getTypicalLearningTime'),$a_obj_id);

		return $cache[$a_obj_id];
	}

	//////////////////////////////////////////////////////////////////////////////
	// Special functions for 'objects' that have not an entry in object_data
	// E.g. events
	////////////////////////////////////////////////////////////////////////////

	/**
	* Static function to read the number of user who have the status 'not_attempted'
	*/
	function _getCountNotAttemptedByType($a_obj_id,$a_type)
	{
		return count(ilLPStatusWrapper::_getNotAttemptedByType($a_obj_id,$a_type));
	}
	function _getNotAttemptedByType($a_obj_id,$a_type)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id.'_'.$a_type]))
		{
			return $cache[$a_obj_id.'_'.$a_type];
		}

		$class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id,$a_type);

		$cache[$a_obj_id.'_'.$a_type] = call_user_func(array($class,'_getNotAttempted'),$a_obj_id);

		return $cache[$a_obj_id.'_'.$a_type];
	}
	function _getCountInProgressByType($a_obj_id,$a_type)
	{
		return count(ilLPStatusWrapper::_getInProgressByType($a_obj_id,$a_type));
	}
	function _getInProgressByType($a_obj_id,$a_type)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id.'_'.$a_type]))
		{
			return $cache[$a_obj_id.'_'.$a_type];
		}

		$class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id,$a_type);

		$cache[$a_obj_id.'_'.$a_type] = call_user_func(array($class,'_getInProgress'),$a_obj_id);

		return $cache[$a_obj_id.'_'.$a_type];
	}
	function _getCountCompletedByType($a_obj_id,$a_type)
	{
		return count(ilLPStatusWrapper::_getCompletedByType($a_obj_id,$a_type));
	}
	function _getCompletedByType($a_obj_id,$a_type)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id.'_'.$a_type]))
		{
			return $cache[$a_obj_id.'_'.$a_type];
		}

		$class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id,$a_type);
		$cache[$a_obj_id.'_'.$a_type] = call_user_func(array($class,'_getCompleted'),$a_obj_id);
		return $cache[$a_obj_id.'_'.$a_type];
	}
	function _getCountFailedByType($a_obj_id,$a_type)
	{
		return count(ilLPStatusWrapper::_getFailedByType($a_obj_id,$a_type));
	}
	function _getFailedByType($a_obj_id,$a_type)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id.'_'.$a_type]))
		{
			return $cache[$a_obj_id.'_'.$a_type];
		}

		$class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id,$a_type);
		$cache[$a_obj_id.'_'.$a_type] = call_user_func(array($class,'_getFailed'),$a_obj_id);
		return $cache[$a_obj_id.'_'.$a_type];
	}
	function _getStatusInfoByType($a_obj_id,$a_type)
	{
		static $cache = array();

		if(isset($cache[$a_obj_id.'_'.$a_type]))
		{
			return $cache[$a_obj_id.'_'.$a_type];
		}

		$class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id,$a_type);

		$cache[$a_obj_id.'_'.$a_type] = call_user_func(array($class,'_getStatusInfo'),$a_obj_id);

		return $cache[$a_obj_id.'_'.$a_type];
	}
}	
?>