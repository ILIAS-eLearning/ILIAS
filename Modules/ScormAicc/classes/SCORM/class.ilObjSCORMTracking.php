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
* Class ilObjSCORMTracking
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMTracking
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjSCORMTracking()
	{
		global $ilias;

	}

	function extractData()
	{
		$this->insert = array();
		if (is_array($_GET["iL"]))
		{
			foreach($_GET["iL"] as $key => $value)
			{
				$this->insert[] = array("left" => $value, "right" => $_GET["iR"][$key]);
			}
		}
		if (is_array($_POST["iL"]))
		{
			foreach($_POST["iL"] as $key => $value)
			{
				$this->insert[] = array("left" => $value, "right" => $_POST["iR"][$key]);
			}
		}

		$this->update = array();
		if (is_array($_GET["uL"]))
		{
			foreach($_GET["uL"] as $key => $value)
			{
				$this->update[] = array("left" => $value, "right" => $_GET["uR"][$key]);
			}
		}
		if (is_array($_POST["uL"]))
		{
			foreach($_POST["uL"] as $key => $value)
			{
				$this->update[] = array("left" => $value, "right" => $_POST["uR"][$key]);
			}
		}
	}

	function store($obj_id=0, $sahs_id=0, $extractData=1)
	{
		global $ilDB, $ilUser;

		if (empty($obj_id))
		{
			$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		}
		
		if (empty($sahs_id))
			$sahs_id = ($_GET["sahs_id"] != "")	? $_GET["sahs_id"] : $_POST["sahs_id"];
			
		if ($extractData==1)
			$this->extractData();

		if (is_object($ilUser))
		{
			$user_id = $ilUser->getId();
		}

		// writing to scorm test log
		$f = fopen("./Modules/ScormAicc/log/scorm.log", "a");
		fwrite($f, "\nCALLING SCORM store()\n");
		if ($obj_id <= 1)
		{
			fwrite($f, "Error: No obj_id given.\n");
		}
		else
		{
			foreach($this->insert as $insert)
			{		
				$set = $ilDB->queryF('
				SELECT * FROM scorm_tracking 
				WHERE user_id = %s
				AND sco_id =  %s
				AND lvalue =  %s
				AND obj_id = %s',
				array('integer','integer','text','integer'), 
				array($user_id,$sahs_id,$insert["left"],$obj_id));
				if ($rec = $ilDB->fetchAssoc($set))
				{
					fwrite($f, "Error Insert, left value already exists. L:".$insert["left"].",R:".
						$insert["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
				else
				{
					$ilDB->insert('scorm_tracking', array(
						'obj_id'		=> array('integer', $obj_id),
						'user_id'		=> array('integer', $user_id),
						'sco_id'		=> array('integer', $sahs_id),
						'lvalue'		=> array('text', $insert["left"]),
						'rvalue'		=> array('clob', $insert["right"]),
						'c_timestamp'	=> array('timestamp', ilUtil::now())
					));
										
					fwrite($f, "Insert - L:".$insert["left"].",R:".
						$insert["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
			}
			foreach($this->update as $update)
			{

				$set = $ilDB->queryF('
				SELECT * FROM scorm_tracking 
				WHERE user_id = %s
				AND sco_id =  %s
				AND lvalue =  %s
				AND obj_id = %s',
				array('integer','integer','text','integer'), 
				array($user_id,$sahs_id,$update["left"],$obj_id));
				
				if ($rec = $ilDB->fetchAssoc($set))
				{
					$ilDB->update('scorm_tracking',
						array(
							'rvalue'		=> array('clob', $update["right"]),
							'c_timestamp'	=> array('timestamp', ilUtil::now())
						),
						array(
							'user_id'		=> array('integer', $user_id),
							'sco_id'		=> array('integer', $sahs_id),
							'lvalue'		=> array('text', $update["left"]),
							'obj_id'		=> array('integer', $obj_id)
						)
					);
				}
				else
				{
					fwrite($f, "ERROR Update, left value does not exist. L:".$update["left"].",R:".
						$update["right"].",sahs_id:".$sahs_id.",user_id:".$user_id."\n");
				}
				
			}
		}
		fclose($f);
	}

	function _insertTrackData($a_sahs_id, $a_lval, $a_rval, $a_obj_id)
	{
		global $ilDB, $ilUser;

		$ilDB->insert('scorm_tracking', array(
			'obj_id'		=> array('integer', $a_obj_id),
			'user_id'		=> array('integer', $ilUser->getId()),
			'sco_id'		=> array('integer', $a_sahs_id),
			'lvalue'		=> array('text', $a_lval),
			'rvalue'		=> array('clob', $a_rval),
			'c_timestamp'	=> array('timestamp', ilUtil::now())
		));
	}


	function _getInProgress($scorm_item_id,$a_obj_id)
	{
		global $ilDB;
		
		if(is_array($scorm_item_id))
		{
			$in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
			
			$res = $ilDB->queryF('SELECT user_id,sco_id FROM scorm_tracking
			WHERE '.$in.'
			AND obj_id = %s 
			GROUP BY user_id, sco_id',
			array('integer'),array($a_obj_id));
			   
		}
		else
		{
			$res = $ilDB->queryF('SELECT user_id,sco_id FROM scorm_tracking			
			WHERE sco_id = %s 
			AND obj_id = %s',
			array('integer','integer'),array($scorm_item_id,$a_obj_id)
			);
		}
		
		while($row = $ilDB->fetchObject($res))
		{
			$in_progress[$row->sco_id][] = $row->user_id;
		}
		return is_array($in_progress) ? $in_progress : array();
	}

	function _getCompleted($scorm_item_id,$a_obj_id)
	{
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
			
			$res = $ilDB->queryF('SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE '.$in.'
			AND obj_id = %s
			AND lvalue = %s 
			AND ('.$ilDB->like('rvalue', 'clob', 'completed').' OR '.$ilDB->like('rvalue', 'clob', 'passed').')',
			array('integer','text'), 
			array($a_obj_id,'cmi.core.lesson_status'));
		}
		else
		{	
			$res = $ilDB->queryF('SELECT DISTINCT(user_id) FROM scorm_tracking 
			WHERE sco_id = %s
			AND obj_id = %s
			AND lvalue = %s 
			AND ('.$ilDB->like('rvalue', 'clob', 'completed').' OR '.$ilDB->like('rvalue', 'clob', 'passed').')',
			array('integer','integer','text'), 
			array($scorm_item_id,$a_obj_id,'cmi.core.lesson_status'));
		}
		
		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _getFailed($scorm_item_id,$a_obj_id)
	{
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$in = $ilDB->in('sco_id', $scorm_item_id, false, 'integer');
			
			$res = $ilDB->queryF('
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE '.$in.'
				AND obj_id = %s
				AND lvalue =  %s
				AND '.$ilDB->like('rvalue', 'clob', 'failed').' ',
			array('integer','text'),
			array($a_obj_id,'cmi.core.lesson_status'));				
		}
		else
		{
			
			$res = $ilDB->queryF('
				SELECT DISTINCT(user_id) FROM scorm_tracking 
				WHERE sco_id = %s
				AND obj_id = %s
				AND lvalue =  %s
				AND '.$ilDB->like('rvalue', 'clob', 'failed').' ',
			array('integer','integer','text'),
			array($scorm_item_id,$a_obj_id,'cmi.core.lesson_status'));
		}

		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _getCountCompletedPerUser($a_scorm_item_ids,$a_obj_id)
	{
		global $ilDB;
		
		$in = $ilDB->in('sco_id', $a_scorm_item_ids, false, 'integer');

		$res = $ilDB->queryF('
			SELECT user_id, COUNT(user_id) completed FROM scorm_tracking
			WHERE '.$in.'
			AND obj_id = %s
			AND lvalue = %s 
			AND ('.$ilDB->like('rvalue', 'clob', 'completed').' OR '.$ilDB->like('rvalue', 'clob', 'passed').')
			GROUP BY user_id',
			array('integer', 'text'),
			array($a_obj_id, 'cmi.core.lesson_status')
		);
		
		while($row = $ilDB->fetchObject($res))
		{
			$users[$row->user_id] = $row->completed;
		}

		return $users ? $users : array();
	}

	function _getProgressInfo($sco_item_ids,$a_obj_id)
	{
		global $ilDB;
		
		$in = $ilDB->in('sco_id', $sco_item_ids, false, 'integer');

		$res = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE '.$in.'
		AND obj_id = %s 
		AND lvalue = %s ',
		array('integer','text'), 
		array($a_obj_id,'cmi.core.lesson_status'));

		$info['completed'] = array();
		$info['failed'] = array();
		
		while($row = $ilDB->fetchObject($res))
		{
			switch($row->rvalue)
			{
				case 'completed':
				case 'passed':
					$info['completed'][$row->sco_id][] = $row->user_id;
					break;

				case 'failed':
					$info['failed'][$row->sco_id][] = $row->user_id;
					break;
			}
		}
		$info['in_progress'] = ilObjSCORMTracking::_getInProgress($sco_item_ids,$a_obj_id);

		return $info;
	}
			

} // END class.ilObjSCORMTracking
?>
