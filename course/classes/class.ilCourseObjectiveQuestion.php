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
* class ilcourseobjectiveQuestion
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseObjectiveQuestion
{
	var $db = null;

	var $objective_id = null;
	var $questions;

	function ilCourseObjectiveQuestion($a_objective_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
	
		$this->objective_id = $a_objective_id;

		$this->__read();
	}

	function getQuestions()
	{
		return $this->questions ? $this->questions : array();
	}
	
	function getQuestion($question_id)
	{
		return $this->questions[$question_id] ? $this->questions[$question_id] : array();
	}

	function getObjectiveId()
	{
		return $this->objective_id;
	}

	function setTestRefId($a_ref_id)
	{
		$this->tst_ref_id = $a_ref_id;
	}
	function getTestRefId()
	{
		return $this->tst_ref_id ? $this->tst_ref_id : 0;
	}
	function setTestObjId($a_obj_id)
	{
		$this->tst_obj_id = $a_obj_id;
	}
	function getTestObjId()
	{
		return $this->tst_obj_id ? $this->tst_obj_id : 0;
	}
	function setQuestionId($a_question_id)
	{
		$this->question_id = $a_question_id;
	}
	function getQuestionId()
	{
		return $this->question_id;
	}

	function add()
	{
		$query = "INSERT INTO crs_objective_qst ".
			"SET objective_id = '".$this->getObjectiveId()."', ".
			"ref_id = '".$this->getTestRefId()."', ".
			"obj_id = '".$this->getTestObjId()."', ".
			"question_id = '".$this->getQuestionId()."'";

		$this->db->query($query);

		return true;
	}
	function delete($qst_id)
	{
		if(!$qst_id)
		{
			return false;
		}

		$query = "DELETE FROM crs_objective_qst ".
			"WHERE qst_ass_id = '".$qst_id."'";

		$this->db->query($query);

		return true;
	}

	// PRIVATE
	function __read()
	{
		$this->questions = array();
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE objective_id = '".$this->getObjectiveId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$qst['ref_id'] = $row->ref_id;
			$qst['obj_id'] = $row->obj_id;
			$qst['question_id'] = $row->question_id;
			$qst['qst_ass_id'] = $row->qst_ass_id;

			$this->questions[$row->qst_ass_id] = $qst;
		}
		return true;
	}

	// STATIC
	function _isAssigned($a_crs_id,$a_tst_ref_id,$a_question_id)
	{
		global $ilDB;

		$query = "SELECT crs_qst.objective_id as objective_id FROM crs_objective_qst as crs_qst, crs_objectives as crs_obj ".
			"WHERE crs_qst.objective_id = crs_obj.objective_id ".
			"AND crs_id = '".$a_crs_id ."' ".
			"AND ref_id = '".$a_tst_ref_id."' ".
			"AND question_id = '".$a_question_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objective_id = $row->objective_id;
		}
		
		return $objective_id ? $objective_id : 0;
	}

}
?>