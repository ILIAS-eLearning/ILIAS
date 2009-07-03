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

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");

/**
* SCORM Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMItem extends ilSCORMObject
{
	var $import_id;
	var $identifierref;
	var $isvisible;
	var $parameters;
	var $prereq_type;
	var $prerequisites;
	var $maxtimeallowed;
	var $timelimitaction;
	var $datafromlms;
	var $masteryscore;

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilSCORMItem($a_id = 0)
	{
		parent::ilSCORMObject($a_id);
		$this->setType("sit");
	}

	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}

	function getIdentifierRef()
	{
		return $this->identifierref;
	}

	function setIdentifierRef($a_id_ref)
	{
		$this->identifierref = $a_id_ref;
	}

	function getVisible()
	{
		return $this->isvisible;
	}

	function setVisible($a_visible)
	{
		$this->isvisible = $a_visible;
	}

	function getParameters()
	{
		return $this->parameters;
	}

	function setParameters($a_par)
	{
		$this->parameters = $a_par;
	}

	function getPrereqType()
	{
		return $this->prereq_type;
	}

	function setPrereqType($a_p_type)
	{
		$this->prereq_type = $a_p_type;
	}

	function getPrerequisites()
	{
		return $this->prerequisites;
	}

	function setPrerequisites($a_pre)
	{
		$this->prerequisites = $a_pre;
	}

	function getMaxTimeAllowed()
	{
		return $this->maxtimeallowed;
	}

	function setMaxTimeAllowed($a_max)
	{
		$this->maxtimeallowed = $a_max;
	}

	function getTimeLimitAction()
	{
		return $this->timelimitaction;
	}

	function setTimeLimitAction($a_lim_act)
	{
		$this->timelimitaction = $a_lim_act;
	}

	function getDataFromLms()
	{
		return $this->datafromlms;
	}

	function setDataFromLms($a_data)
	{
		$this->datafromlms = $a_data;
	}

	function getMasteryScore()
	{
		return $this->masteryscore;
	}

	function setMasteryScore($a_score)
	{
		$this->masteryscore = $a_score;
	}

	function read()
	{
		global $ilDB;
		
		parent::read();

		$obj_set = $ilDB->queryF('SELECT * FROM sc_item WHERE obj_id = %s',
		array('integer'),array($this->getId()));
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		
		$this->setImportId($obj_rec["import_id"]);
		$this->setIdentifierRef($obj_rec["identifierref"]);
		if (strtolower($obj_rec["isvisible"]) == "false")
		{
			$this->setVisible(false);
		}
		else
		{
			$this->setVisible(true);
		}
		$this->setParameters($obj_rec["parameters"]);
		$this->setPrereqType($obj_rec["prereq_type"]);
		$this->setPrerequisites($obj_rec["prerequisites"]);
		$this->setMaxTimeAllowed($obj_rec["maxtimeallowed"]);
		$this->setTimeLimitAction($obj_rec["timelimitaction"]);
		$this->setDataFromLms($obj_rec["datafromlms"]);
		$this->setMasteryScore($obj_rec["masteryscore"]);
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$str_visible = ($this->getVisible())
			? "true"
			: "false";

			$ilDB->manipulateF('
			INSERT INTO sc_item 
			(	obj_id, 
				import_id, 
				identifierref,
				isvisible, 
				parameters, 
				prereq_type, 
				prerequisites, 
				maxtimeallowed,
				timelimitaction, 
				datafromlms, 
				masteryscore
			) 
			VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)',
			array('integer', 'text','text','text','text','text','text','text','text','text','text'),
			array(	$this->getId(),
					$this->getImportId(),
					$this->getIdentifierRef(),
					$str_visible,
					$this->getParameters(),
					$this->getPrereqType(),
					$this->getPrerequisites(),
					$this->getMaxTimeAllowed(),
					$this->getTimeLimitAction(),
					$this->getDataFromLms(),
					$this->getMasteryScore()
					));
			
	}

	function update()
	{
		global $ilDB;
		
		$str_visible = ($this->getVisible())
			? "true"
			: "false";

		parent::update();

			$ilDB->manipulateF('
			UPDATE sc_item 
			SET
				import_id = %s,
				identifierref = %s,
				isvisible = %s,
				parameters = %s,
				prereq_type = %s,
				prerequisites = %s,
				maxtimeallowed = %s,
				timelimitaction = %s,
				datafromlms =  %s,
				masteryscore = %s
			WHERE obj_id = %s',
			array('text','text','text','text','text','text','text','text','text','text','integer'),
			array(	
					$this->getImportId(),
					$this->getIdentifierRef(),
					$str_visible,
					$this->getParameters(),
					$this->getPrereqType(),
					$this->getPrerequisites(),
					$this->getMaxTimeAllowed(),
					$this->getTimeLimitAction(),
					$this->getDataFromLms(),
					$this->getMasteryScore(),
					$this->getId()
					)
						
			);

	}

	/**
	* get tracking data of specified or current user
	*
	*
	*/
	function getTrackingDataOfUser($a_user_id = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}


		$track_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE sco_id = %s 
		AND user_id =  %s
		AND obj_id = %s',
		array('integer','integer','integer'),
		array($this->getId(),$a_user_id,$this->getSLMId()));
		
		$trdata = array();
		while ($track_rec = $ilDB->fetchAssoc($track_set))
		{
			$trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
		}

		return $trdata;
	}

	function _lookupTrackingDataOfUser($a_item_id, $a_user_id = 0, $a_obj_id = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$track_set = $ilDB->queryF('
		SELECT * FROM scorm_tracking 
		WHERE sco_id = %s 
		AND user_id =  %s
		AND obj_id = %s',
		array('integer','integer','integer'),
		array($a_item_id,$a_user_id,$a_obj_id));
		
		$trdata = array();
		while ($track_rec = $ilDB->fetchAssoc($track_set))
		{
			$trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
		}

		return $trdata;
	}

	function delete()
	{
		global $ilDB, $ilLog;

		parent::delete();


		$ilDB->manipulateF('DELETE FROM sc_item WHERE obj_id = %s',
		array('integer'),array($this->getId()));

		$q_log = "DELETE FROM scorm_tracking WHERE ".
			"sco_id = ".$ilDB->quote($this->getId()).
			" AND obj_id = ".$ilDB->quote($this->getSLMId());
		$ilLog->write("SAHS Delete(ScormItem): ".$q_log);
		
		$ilDB->manipulateF('DELETE FROM sc_item WHERE sco_id = %s AND obj_id = %s',
		array('integer','integer'), array($this->getId(),$this->getSLMId()));

	}

	//function insertTrackData($a_lval, $a_rval, $a_ref_id)
	function insertTrackData($a_lval, $a_rval, $a_obj_id)
	{
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
		//ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_ref_id);
		ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_obj_id);
	}

	// Static
	function _getItems($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT * FROM scorm_object 
			WHERE slm_id = %s
			AND type = %s',
			array('integer','text'),
			array($a_obj_id,'sit')
		);
		while($row = $ilDB->fetchObject($res))
		
		{
			$item_ids[] = $row->obj_id;
		}
		return $item_ids ? $item_ids : array();
	}

	function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM scorm_object WHERE obj_id = %s',
		array('integer', array($a_obj_id)));
		
		while($row = $ilDB->fetchObject($res))
		{
			return $row->title;
		}
		return '';
	}
		

}
?>
