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
* Abstract class ilLPStatus for all learning progress modes
* E.g  ilLPStatusManual, ilLPStatusObjectives ...
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

define('LP_STATUS_NOT_ATTEMPTED','trac_no_attempted');
define('LP_STATUS_IN_PROGRESS','trac_in_progress');
define('LP_STATUS_COMPLETED','trac_completed');

// Stati for events
define('LP_STATUS_REGISTERED','trac_registered');
define('LP_STATUS_NOT_REGISTERED','trac_not_registered');
define('LP_STATUS_PARTICIPATED','trac_participated');
define('LP_STATUS_NOT_PARTICIPATED','trac_not_participated');




class ilLPStatus
{
	var $obj_id = null;

	var $db = null;

	function ilLPStatus($a_obj_id)
	{
		global $ilDB;

		$this->obj_id = $a_obj_id;
		$this->db =& $ilDB;
	}

	function _getCountNotAttempted($a_obj_id)
	{
		return 0;
	}

	function _getNotAttempted($a_obj_id)
	{
		return array();
	}
	
	function _getCountInProgress($a_obj_id)
	{
		return 0;
	}
	function _getInProgress($a_obj_id)
	{
		return array();
	}

	function _getCountCompleted($a_obj_id)
	{
		return 0;
	}
	function _getCompleted($a_obj_id)
	{
		return array();
	}
	function _getStatusInfo($a_obj_id)
	{
		return array();
	}
	function _getTypicalLearningTime($a_obj_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDEducational.php';
		return ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);
	}

}	
?>