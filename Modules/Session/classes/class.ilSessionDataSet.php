<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Session data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesSession
 */
class ilSessionDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.1.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/Session/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "sess")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"Location" => "text",
						"TutorName" => "text",
						"TutorEmail" => "text",
						"TutorPhone" => "text",
						"Details" => "text",
						"Registration" => "integer",
						"EventStart" => "text",
						"EventEnd" => "text",
						"StartingTime" => "integer",
						"EndingTime" => "integer",
						"Fulltime" => "integer"
						);
			}
		}

		if ($a_entity == "sess_item")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"SessionId" => "integer",
						"ItemId" => "text",
						);
			}
		}

	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		global $ilDB;

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
				
		if ($a_entity == "sess")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery($q = "SELECT ev.obj_id id, od.title title, od.description description, ".
						" location, tutor_name, tutor_email, tutor_phone, details, registration, ".
						" e_start event_start, e_end event_end, starting_time, ending_time, fulltime ".
						" FROM event ev JOIN object_data od ON (ev.obj_id = od.obj_id) ".
						" JOIN event_appointment ea ON (ev.obj_id = ea.event_id)  ".
						"WHERE ".
						$ilDB->in("ev.obj_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "sess_item")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery($q = "SELECT event_id session_id, item_id ".
						" FROM event_items ".
						"WHERE ".
						$ilDB->in("event_id", $a_ids, false, "integer"));
					break;
			}
		}

	}

	/**
	 * Get xml record (export)
	 *
	 * @param	array	abstract data record
	 * @return	array	xml record
	 */
	function getXmlRecord($a_entity, $a_version, $a_set)
	{
		if ($a_entity == "sess")
		{
			// convert server dates to utc
			if(!$a_set["Fulltime"])
			{
				// nothing has to be done here, since the dates are already stored in UTC
				#$start = new ilDateTime($a_set["EventStart"], IL_CAL_DATETIME);
				#$a_set["EventStart"] = $start->get(IL_CAL_DATETIME,'','UTC');
				#$end = new ilDateTime($a_set["EventEnd"], IL_CAL_DATETIME);
				#$a_set["EventEnd"] = $end->get(IL_CAL_DATETIME,'','UTC');
			}
		}
		if ($a_entity == "sess_item")
		{
			// make ref id an object id
			$a_set["ItemId"] = ilObject::_lookupObjId($a_set["ItemId"]);
		}
		return $a_set;
	}



	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "sess":
				return array (
					"sess_item" => array("ids" => $a_rec["Id"])
				);
		}

		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "sess":
				include_once("./Modules/Session/classes/class.ilObjSession.php");
				include_once("./Modules/Session/classes/class.ilSessionAppointment.php");

				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjSession();
					$newObj->setType("sess");
					$newObj->create(true);
				}
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setLocation($a_rec["Location"]);
				$newObj->setName($a_rec["TutorName"]);
				$newObj->setPhone($a_rec["TutorPhone"]);
				$newObj->setEmail($a_rec["TutorEmail"]);
				$newObj->setDetails($a_rec["Details"]);
				$newObj->update();

				$start = new ilDateTime($a_rec["EventStart"], IL_CAL_DATETIME, "UTC");
				$end = new ilDateTime($a_rec["EventEnd"], IL_CAL_DATETIME, "UTC");
//echo "<br>".$start->get(IL_CAL_UNIX);
//echo "<br>".$start->get(IL_CAL_DATETIME);
				$app = new ilSessionAppointment();
				$app->setStart($a_rec["EventStart"]);
				$app->setEnd($a_rec["EventEnd"]);
				$app->setStartingTime($start->get(IL_CAL_UNIX));
				$app->setEndingTime($end->get(IL_CAL_UNIX));
				$app->toggleFullTime($a_rec["Fulltime"]);
				$app->setSessionId($newObj->getId());
				$app->create();
				
				//$newObj->setAppointments(array($app));
				//$newObj->update();

				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/Session", "sess", $a_rec["Id"], $newObj->getId());
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
				break;

			case "sess_item":

				if($obj_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['ItemId']))
				{
					$ref_id = current(ilObject::_getAllReferences($obj_id));
					include_once './Modules/Session/classes/class.ilEventItems.php';
					$evi = new ilEventItems($this->current_obj->getId());
					$evi->addItem($ref_id);
					$evi->update();
				}
				break;
		}
	}
}
?>