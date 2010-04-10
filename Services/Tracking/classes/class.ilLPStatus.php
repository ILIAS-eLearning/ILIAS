<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

define('LP_STATUS_NOT_ATTEMPTED','trac_no_attempted');
define('LP_STATUS_IN_PROGRESS','trac_in_progress');
define('LP_STATUS_COMPLETED','trac_completed');
define('LP_STATUS_FAILED','trac_failed');

define('LP_STATUS_NOT_ATTEMPTED_NUM', 0);
define('LP_STATUS_IN_PROGRESS_NUM', 1);
define('LP_STATUS_COMPLETED_NUM', 2);
define('LP_STATUS_FAILED_NUM', 3);

// Stati for events
define('LP_STATUS_REGISTERED','trac_registered');
define('LP_STATUS_NOT_REGISTERED','trac_not_registered');
define('LP_STATUS_PARTICIPATED','trac_participated');
define('LP_STATUS_NOT_PARTICIPATED','trac_not_participated');

/**
 * Abstract class ilLPStatus for all learning progress modes
 * E.g  ilLPStatusManual, ilLPStatusObjectives ...
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesTracking
 *
 */
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
	function _getFailed($a_obj_id)
	{
		return array();
	}
	function _getCountFailed()
	{
		return 0;
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

	/**
	 * Write status
	 *
	 * @param
	 * @return
	 */
	function _updateStatus($a_obj_id, $a_usr_id, $a_obj = null, $a_percentage = false)
	{
		$status = $this->determineStatus($a_obj_id, $a_usr_id, $a_obj);
		include_once("./Services/Tracking/classes/class.ilTracking2.php");
		ilTracking2::writeStatus($a_obj_id, $a_usr_id, $status);
		if ($a_percentage !== false)
		{
			ilTracking2::writePercentage($a_obj_id, $a_usr_id, $a_percentage);
		}
	}
	
	/**
	 * Set object status dirty
	 *
	 * @param
	 * @return
	 */
	function _setDirty($a_obj_id)
	{
		include_once("./Services/Tracking/classes/class.ilTracking2.php");
		ilTracking2::setDirty($a_obj_id);
	}
}	
?>