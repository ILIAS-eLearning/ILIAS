<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Poll Dataset class
 * 
 * This class implements the following entities:
 * - poll: object data
 * - poll_answer: data from table il_poll_answer
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesBlog
 */
class ilPollDataSet extends ilDataSet
{	
	protected $current_blog;
	
	/**
	 * Get supported versions
	 */
	public function getSupportedVersions()
	{
		return array("4.3.0");
	}
	
	/**
	 * Get xml namespace
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/Poll/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 */
	protected function getTypes($a_entity, $a_version)
	{		
		if ($a_entity == "poll")
		{
			switch ($a_version)
			{			
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",					
						"Question" => "text",					
						"Image" => "text",
						"ViewResults" => "integer",
						"Dir" => "directory"
						);
			}
		}
		
		if ($a_entity == "poll_answer")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					return array(
						"Id" => "integer",
						"PollId" => "integer",
						"Answer" => "text",
						"Pos" => "integer",						
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
		
		if ($a_entity == "poll")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description,".
						"pl.question,pl.image,pl.view_results".
						" FROM il_poll pl".
						" JOIN object_data od ON (od.obj_id = pl.id)".
						" WHERE ".$ilDB->in("pl.id", $a_ids, false, "integer").
						" AND od.type = ".$ilDB->quote("poll", "text"));
					break;				
			}
		}
		
		if ($a_entity == "poll_answer")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id,poll_id,answer,pos".
						" FROM il_poll_answer WHERE ".
						$ilDB->in("poll_id", $a_ids, false, "integer"));
					break;
			}
		}	
	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "poll":
				return array (
					"poll_answer" => array("ids" => $a_rec["Id"])
				);			
		}
		return false;
	}

	/**
	 * Get xml record
	 *
	 * @param
	 * @return
	 */
	function getXmlRecord($a_entity, $a_version, $a_set)
	{
		if ($a_entity == "poll")
		{
			include_once("./Modules/Poll/classes/class.ilObjPoll.php");
			$dir = ilObjPoll::initStorage($a_set["Id"]);
			$a_set["Dir"] = $dir;
		}

		return $a_set;
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
			case "poll":
				include_once("./Modules/Poll/classes/class.ilObjPoll.php");
				$newObj = new ilObjPoll();
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->create();
								
				$newObj->setQuestion($a_rec["Question"]);				
				$newObj->setImage($a_rec["Image"]);
				$newObj->setViewResults($a_rec["ViewResults"]);
				$newObj->update();
				
				// handle image(s)
				if($a_rec["Image"])
				{								
					$dir = str_replace("..", "", $a_rec["Dir"]);										
					if ($dir != "" && $this->getImportDirectory() != "")
					{
						$source_dir = $this->getImportDirectory()."/".$dir;
						$target_dir = ilObjPoll::initStorage($newObj->getId());		
						ilUtil::rCopy($source_dir, $target_dir);
					}
				}

				$a_mapping->addMapping("Modules/Poll", "poll", $a_rec["Id"], $newObj->getId());
				break;

			case "poll_answer":							
				$poll_id = (int) $a_mapping->getMapping("Modules/Poll", "poll", $a_rec["PollId"]);
				if($poll_id)
				{
					$poll = new ilObjPoll($poll_id, false);
					$poll->saveAnswer($a_rec["Answer"], $a_rec["pos"]);											
				}
				break;
		}
	}	
}

?>