<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Exercise data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesExercise
 */
class ilExerciseDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Modules/Exercise/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "exc")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"PassMode" => "text",
						"PassNr" => "integer",
						"ShowSubmissions" => "integer");
			}
		}

		if ($a_entity == "exc_assignment")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"ExerciseId" => "integer",
						"Deadline" => "text",
						"Instruction" => "text",
						"Title" => "text",
						"Mandatory" => "integer",
						"OrderNr" => "integer",
						"Dir" => "directory");
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
				
		if ($a_entity == "exc")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT exc_data.obj_id id, title, description, ".
						" pass_mode, pass_nr, show_submissions".
						" FROM exc_data JOIN object_data ON (exc_data.obj_id = object_data.obj_id) ".
						"WHERE ".
						$ilDB->in("exc_data.obj_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "exc_assignment")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, exc_id exercise_id, time_stamp deadline, ".
						" instruction, title, start_time, mandatory, order_nr".
						" FROM exc_assignment ".
						"WHERE ".
						$ilDB->in("exc_id", $a_ids, false, "integer"));
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
		if ($a_entity == "exc_assignment")
		{
			// convert server dates to utc
			if($a_set["StartTime"] != "")
			{
				$start = new ilDateTime($a_set["StartTime"], IL_CAL_UNIX);
				$a_set["StartTime"] = $start->get(IL_CAL_DATETIME,'','UTC');
			}
			if($a_set["Deadline"] != "")
			{
				$deadline = new ilDateTime($a_set["Deadline"], IL_CAL_UNIX);
				$a_set["Deadline"] = $deadline->get(IL_CAL_DATETIME,'','UTC');
			}

			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$fstorage = new ilFSStorageExercise($a_set["ExerciseId"], $a_set["Id"]);
			$a_set["Dir"] = $fstorage->getPath();

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
			case "exc":
				return array (
					"exc_assignment" => array("ids" => $a_rec["Id"])
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
//echo $a_entity;
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "exc":
				include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
				
				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjExercise();
					$newObj->setType("exc");
					$newObj->create(true);
				}
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setPassMode($a_rec["PassMode"]);
				$newObj->setPassNr($a_rec["PassNr"]);
				$newObj->setShowSubmissions($a_rec["ShowSubmissions"]);
				$newObj->update();
				$newObj->saveData();
//var_dump($a_rec);
				$this->current_exc = $newObj;

				$a_mapping->addMapping("Modules/Exercise", "exc", $a_rec["Id"], $newObj->getId());
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
				break;

			case "exc_assignment":
				$exc_id = $a_mapping->getMapping("Modules/Exercise", "exc", $a_rec["ExerciseId"]);
				if ($exc_id > 0)
				{
					if (is_object($this->current_exc) && $this->current_exc->getId() == $exc_id)
					{
						$exc = $this->current_exc;
					}
					else
					{
						include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
						$exc = new ilObjExercise($exc_id, false);
					}

					include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

					$ass = new ilExAssignment();
					$ass->setExerciseId($exc_id);
					
					if ($a_rec["StartTime"] != "")
					{
						$start = new ilDateTime($a_rec["StartTime"], IL_CAL_DATETIME, "UTC");
						$ass->setStartTime($start->get(IL_CAL_UNIX));
					}

					if ($a_rec["Deadline"] != "")
					{
						$deadline = new ilDateTime($a_rec["Deadline"], IL_CAL_DATETIME, "UTC");
						$ass->setDeadline($deadline->get(IL_CAL_UNIX));
					}
//var_dump($a_rec);
					$ass->setInstruction($a_rec["Instruction"]);
					$ass->setTitle($a_rec["Title"]);
					$ass->setMandatory($a_rec["Mandatory"]);
					$ass->setOrderNr($a_rec["OrderNr"]);
					$ass->save();

					include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
					$fstorage = new ilFSStorageExercise($exc_id, $ass->getId());
					$fstorage->create();
					$dir = str_replace("..", "", $a_rec["Dir"]);
					if ($dir != "" && $this->getImportDirectory() != "")
					{
						$source_dir = $this->getImportDirectory()."/".$dir;
						$target_dir = $fstorage->getPath();
						ilUtil::rCopy($source_dir, $target_dir);
					}

					$a_mapping->addMapping("Modules/Exercise", "exc_assignment", $a_rec["Id"], $ass->getId());

				}

				break;
		}
	}
}
?>