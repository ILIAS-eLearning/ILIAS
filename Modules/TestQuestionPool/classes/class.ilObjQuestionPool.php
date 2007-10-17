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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class ilObjQuestionPool
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @defgroup ModulesTestQuestionPool Modules/TestQuestionPool
*/

class ilObjQuestionPool extends ilObject
{
	/**
	* Online status of questionpool
	*
	* @var string
	*/
	var $online;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjQuestionPool($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "qpl";
		$this->ilObject($a_id,$a_call_by_reference);
		$this->setOnline(0);
	}

	/**
	* create questionpool object
	*/
	function create($a_upload = false)
	{
		parent::create();
		
		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}
	}

/**
* Creates a database reference id for the object
* 
* Creates a database reference id for the object (saves the object 
* to the database and creates a reference id in the database)
*
* @access public
*/
	function createReference() 
	{
		$result = parent::createReference();
		$this->saveToDb();
		return $result;
	}
	
	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		$this->updateMetaData();
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff

		return true;
	}

	function updateMetaData()
	{
		global $ilUser;
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_gen =& $md->getGeneral();
		if ($md_gen == false)
		{
			include_once "./Services/MetaData/classes/class.ilMDCreator.php";
			$md_creator = new ilMDCreator($this->getId(),0,$this->getType());
			$md_creator->setTitle($this->getTitle());
			$md_creator->setTitleLanguage($ilUser->getPref('language'));
			$md_creator->create();
		}
		parent::updateMetaData();
	}

	/**
	* read object data from db into object
	* @param	boolean
	* @access	public
	*/
	function read($a_force_db = false)
	{
		parent::read($a_force_db);
		$this->loadFromDb();
	}


	/**
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
/*		$questions =& $this->getAllQuestions();
		$used_questions = 0;
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		foreach ($questions as $question_id)
		{
			if (assQuestion::_isUsedInRandomTest($question_id))
			{
				$used_questions++;
			}
		}
		
		if ($used_questions)
		{
			return false;
		}
*/		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete meta data
		$this->deleteMetaData();

		//put here your module specific stuff
		$this->deleteQuestionpool();

		return true;
	}

	function deleteQuestionpool()
	{
		$questions =& $this->getAllQuestions();

		if (count($questions))
		{
			foreach ($questions as $question_id)
			{
				$this->deleteQuestion($question_id);
			}
		}

		// delete export files
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$qpl_data_dir = ilUtil::getDataDir()."/qpl_data";
		$directory = $qpl_data_dir."/qpl_".$this->getId();
		if (is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}
	}

	/**
	* init default roles settings
	*
	* If your module does not require any default roles, delete this method
	* (For an example how this method is used, look at ilObjForum)
	*
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;

		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		switch ($a_event)
		{
			case "link":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	/**
	* Deletes a question from the question pool
	*
	* Deletes a question from the question pool
	*
	* @param integer $question_id The database id of the question
	* @access private
	*/
	function deleteQuestion($question_id)
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$question =& ilObjTest::_instanciateQuestion($question_id);
		$question->delete($question_id);
	}

	/**
	* Loads a ilObjQuestionpool object from a database
	*
	* Loads a ilObjQuestionpool object from a database
	*
	* @access public
	*/
	function loadFromDb()
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM qpl_questionpool WHERE obj_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setOnline($row["online"]);
		}
	}
	
/**
* Saves a ilObjQuestionpool object to a database
* 
* Saves a ilObjQuestionpool object to a database
*
* @access public
*/
  function saveToDb()
  {
		global $ilDB;
		
		$query = sprintf("SELECT * FROM qpl_questionpool WHERE obj_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$query = sprintf("UPDATE qpl_questionpool SET online = %s WHERE obj_fi = %s",
				$ilDB->quote($this->getOnline() . ""),
				$ilDB->quote($this->getId() . "")
			);
      $result = $ilDB->query($query);
      if ($result != DB_OK) 
			{
      }
		}
		else
		{
			$query = sprintf("INSERT INTO qpl_questionpool (online, obj_fi) VALUES (%s, %s)",
				$ilDB->quote($this->getOnline() . ""),
				$ilDB->quote($this->getId() . "")
			);
      $result = $ilDB->query($query);
      if ($result != DB_OK) 
			{
      }
		}
	}
	
	/**
	* Returns the question type of a question with a given id
	*
	* Returns the question type of a question with a given id
	*
	* @param integer $question_id The database id of the question
	* @result string The question type string
	* @access private
	*/
	function getQuestiontype($question_id)
	{
		global $ilDB;
		
		if ($question_id < 1)
		{
			return;
		}

		$query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.question_id = %s",
			$ilDB->quote($question_id));

		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
			return $data->type_tag;
		}
		else
		{
			return;
		}
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return parent::getDescription();
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
	}

	/**
	* get title of glossary object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return parent::getTitle();
	}

	/**
	* set title of glossary object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
	}

	/**
	* Checks whether the question is in use or not
	*
	* Checks whether the question is in use or not
	*
	* @param integer $question_id The question id of the question to be checked
	* @return boolean The number of datasets which are affected by the use of the query.
	* @access public
	*/
	function isInUse($question_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT COUNT(solution_id) AS solution_count FROM tst_solutions WHERE question_fi = %s",
			$ilDB->quote("$question_id"));

		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);

		return $row->solution_count;
	}

	function &createQuestion($question_type, $question_id = -1)
	{
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = $this->getQuestiontype($question_id);
		}

		include_once "./Modules/TestQuestionPool/classes/class.".$question_type."GUI.php";
		$question_type_gui = $question_type . "GUI";
		$question =& new $question_type_gui();

		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}

		return $question;
	}

	/**
	* Duplicates a question for a questionpool
	*
	* Duplicates a question for a questionpool
	*
	* @param integer $question_id The database id of the question
	* @access public
	*/
	function duplicateQuestion($question_id)
	{
		$question =& $this->createQuestion("", $question_id);
		$newtitle = $question->object->getTitle(); 
		if ($question->object->questionTitleExists($this->getId(), $question->object->getTitle()))
		{
			$counter = 2;
			while ($question->object->questionTitleExists($this->getId(), $question->object->getTitle() . " ($counter)"))
			{
				$counter++;
			}
			$newtitle = $question->object->getTitle() . " ($counter)";
		}
		$question->object->duplicate(false, $newtitle);
		// update question count of question pool
		ilObjQuestionPool::_updateQuestionCount($this->getId());
	}
	
	/**
	* Copies a question into another question pool
	*
	* Copies a question into another question pool
	*
	* @param integer $question_id Database id of the question
	* @param integer $questionpool_to Database id of the target questionpool
	* @access public
	*/
	function copyQuestion($question_id, $questionpool_to)
	{
		$question_gui =& $this->createQuestion("", $question_id);
		if ($question_gui->object->getObjId() == $questionpool_to)
		{
			// the question is copied into the same question pool
			$this->duplicateQuestion($question_id);
		}
		else
		{
			// the question is copied into another question pool
			$newtitle = $question_gui->object->getTitle(); 
			if ($question_gui->object->questionTitleExists($this->getId(), $question_gui->object->getTitle()))
			{
				$counter = 2;
				while ($question_gui->object->questionTitleExists($this->getId(), $question_gui->object->getTitle() . " ($counter)"))
				{
					$counter++;
				}
				$newtitle = $question_gui->object->getTitle() . " ($counter)";
			}
			$question_gui->object->copyObject($this->getId(), $newtitle);
		}
	}

	/**
	* Calculates the data for the output of the questionpool
	*
	* Calculates the data for the output of the questionpool
	*
	* @access public
	*/
	function getQuestionsTable($sort, $sortorder, $filter_text, $sel_filter_type, $startrow = 0)
	{
		global $ilUser;
		global $ilDB;
		
		$where = "";
		if (strlen($filter_text) > 0)
		{
			switch($sel_filter_type)
			{
				case "title":
					$where = " AND qpl_questions.title LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
				case "comment":
					$where = " AND qpl_questions.comment LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND qpl_questions.author LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
			}
		}

		// build sort order for sql query
		$order = "";
		$images = array();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		switch($sort)
		{
			case "title":
				$order = " ORDER BY title $sortorder";
				$images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "comment":
				$order = " ORDER BY comment $sortorder";
				$images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "type":
				$order = " ORDER BY question_type_id $sortorder";
				$images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "author":
				$order = " ORDER BY author $sortorder";
				$images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "created":
				$order = " ORDER BY created $sortorder";
				$images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
			case "updated":
				$order = " ORDER BY TIMESTAMP14 $sortorder";
				$images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($sortorder) . "_order.gif") . "\" alt=\"" . $this->lng->txt(strtolower($sortorder) . "ending_order")."\" />";
				break;
		}
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
		$query = "SELECT qpl_questions.question_id, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14 FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $where$order$limit";
		$query_result = $ilDB->query($query);
		$max = $query_result->numRows();
		if ($startrow > $max -1)
		{
			$startrow = $max - ($max % $maxentries);
		}
		else if ($startrow < 0)
		{
			$startrow = 0;
		}
		$limit = " LIMIT $startrow, $maxentries";
		$query = "SELECT qpl_questions.*, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $where$order$limit";
		$query_result = $ilDB->query($query);
		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($rows, $row);
			}
		}
		$nextrow = $startrow + $maxentries;
		if ($nextrow > $max - 1)
		{
			$nextrow = $startrow;
		}
		$prevrow = $startrow - $maxentries;
		if ($prevrow < 0)
		{
			$prevrow = 0;
		}
		return array(
			"rows" => $rows,
			"images" => $images,
			"startrow" => $startrow,
			"nextrow" => $nextrow,
			"prevrow" => $prevrow,
			"step" => $maxentries,
			"rowcount" => $max
		);
	}

	/**
	* Calculates the data for the print view of the questionpool
	*
	* Calculates the data for the print view of the questionpool
	*
	* @access public
	*/
	function &getPrintviewQuestions($sort)
	{
		global $ilDB;
		
	    // build sort order for sql query
		$order = "";
		switch($sort)
		{
			case "title":
				$order = " ORDER BY title";
				break;
			case "comment":
				$order = " ORDER BY comment,title";
				break;
			case "type":
				$order = " ORDER BY question_type_id,title";
				break;
			case "author":
				$order = " ORDER BY author,title";
				break;
			case "created":
				$order = " ORDER BY created,title";
				break;
			case "updated":
				$order = " ORDER BY TIMESTAMP14,title";
				break;
		}
		$query = "SELECT qpl_questions.*, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $order";
		$query_result = $ilDB->query($query);
		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($rows, $row);
			}
		}
		return $rows;
	}

	/**
	* export pages of test to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportPagesXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog, $questions)
	{
		global $ilBench;
		
		$this->mob_ids = array();
		$this->file_ids = array();

		$attrs = array();
		$attrs["Type"] = "Questionpool_Test";
		$a_xml_writer->xmlStartTag("ContentObject", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		// PageObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Page Objects");
		$ilBench->start("ContentObjectExport", "exportPageObjects");
		$this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog, $questions);
		$ilBench->stop("ContentObjectExport", "exportPageObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Page Objects");

		// MediaObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Media Objects");
		$ilBench->start("ContentObjectExport", "exportMediaObjects");
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportMediaObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Media Objects");

		// FileItems
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export File Items");
		$ilBench->start("ContentObjectExport", "exportFileItems");
		$this->exportFileItems($a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFileItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export File Items");

		$a_xml_writer->xmlEndTag("ContentObject");
	}

	/**
	* export content objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}


	/**
	* export page objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog, $questions)
	{
		global $ilBench;

		include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";

		foreach ($questions as $question_id)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$question_id);

			$attrs = array();
			$a_xml_writer->xmlStartTag("PageObject", $attrs);

			
			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			$page_object = new ilPageObject("qpl", $question_id);
			$page_object->buildDom();
			$page_object->insertInstIntoIDs($a_inst);
			$mob_ids = $page_object->collectMediaObjects(false);
			$file_ids = $page_object->collectFileItems();
			$xml = $page_object->getXMLFromDom(false, false, false, "", true);
			$xml = str_replace("&","&amp;", $xml);
			$a_xml_writer->appendXML($xml);
			$page_object->freeDom();
			unset ($page_object);
			
			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

			// collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			//$mob_ids = $page_obj->getMediaObjectIDs();
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			//$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");
			
			$a_xml_writer->xmlEndTag("PageObject");
			//unset($page_obj);

			$ilBench->stop("ContentObjectExport", "exportPageObject");
			

		}
	}

	/**
	* export media objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		foreach ($this->mob_ids as $mob_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
			$media_obj = new ilObjMediaObject($mob_id);
			$media_obj->exportXML($a_xml_writer, $a_inst);
			$media_obj->exportFiles($a_target_dir);
			unset($media_obj);
		}
	}

	/**
	* export files of file itmes
	*
	*/
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once("./Modules/File/classes/class.ilObjFile.php");

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}	

	/**
	* creates data directory for export files
	* (data_dir/qpl_data/qpl_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$qpl_data_dir = ilUtil::getDataDir()."/qpl_data";
		ilUtil::makeDir($qpl_data_dir);
		if(!is_writable($qpl_data_dir))
		{
			$this->ilias->raiseError("Questionpool Data Directory (".$qpl_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$qpl_dir = $qpl_data_dir."/qpl_".$this->getId();
		ilUtil::makeDir($qpl_dir);
		if(!@is_dir($qpl_dir))
		{
			$this->ilias->raiseError("Creation of Questionpool Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $qpl_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of questionpool
	*/
	function getExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export_dir = ilUtil::getDataDir()."/qpl_data"."/qpl_".$this->getId()."/export";
		return $export_dir;
	}
	
	/**
	* get export files
	*/
	function getExportFiles($dir)
	{
		// quit if import dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}
		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				(substr($entry, -4) == ".zip" or substr($entry, -4) == ".xls") and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(qpl__)*[0-9]+\.(zip|xls)\$", $entry))
			{
				$file[] = $entry;
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);
		return $file;
	}

	/**
	* creates data directory for import files
	* (data_dir/qpl_data/qpl_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function _createImportDirectory()
	{
		global $ilias;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$qpl_data_dir = ilUtil::getDataDir()."/qpl_data";
		ilUtil::makeDir($qpl_data_dir);
		
		if(!is_writable($qpl_data_dir))
		{
			$ilias->raiseError("Questionpool Data Directory (".$qpl_data_dir
				.") not writeable.",$ilias->error_obj->FATAL);
		}

		// create questionpool directory (data_dir/qpl_data/qpl_import)
		$qpl_dir = $qpl_data_dir."/qpl_import";
		ilUtil::makeDir($qpl_dir);
		if(!@is_dir($qpl_dir))
		{
			$ilias->raiseError("Creation of Questionpool Directory failed.",$ilias->error_obj->FATAL);
		}
	}

	/**
	* get import directory of lm
	*/
	function _getImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/qpl_data/qpl_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}

	/**
	* get import directory of lm
	*/
	function getImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/qpl_data/qpl_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Retrieve an array containing all question ids of the questionpool
	*
	* Retrieve an array containing all question ids of the questionpool
	*
	* @return array An array containing all question ids of the questionpool
	*/
	function &getAllQuestions()
	{
		global $ilDB;
		
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND original_id IS NULL",
			$ilDB->quote($this->getId())
		);

		$result = $ilDB->query($query);
		$questions = array();

		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questions, $row["question_id"]);
		}
		return $questions;
	}
	
	function &getAllQuestionIds()
	{
		global $ilDB;
		
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(original_id) AND obj_fi = %s AND complete = %s",
			$ilDB->quote($this->getId()),
			$ilDB->quote("1")
		);
		$query_result = $ilDB->query($query);
		$questions = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($questions, $row["question_id"]);
			}
		}
		return $questions;
	}
	
	/**
	* get array of (two) new created questions for
	* import id
	*/
	function getImportMapping()
	{
		if (!is_array($this->import_mapping))
		{
			return array();
		}
		else
		{
			return $this->import_mapping;
		}
	}
	
	/**
	* Returns a QTI xml representation of a list of questions
	*
	* Returns a QTI xml representation of a list of questions
	*
	* @param array $questions An array containing the question ids of the questions
	* @return string The QTI xml representation of the questions
	* @access public
	*/
	function toXML($questions)
	{
		$xml = "";
		// export button was pressed
		if (count($questions) > 0)
		{
			foreach ($questions as $key => $value)
			{
				$question =& $this->createQuestion("", $value);
				$xml .= $question->object->toXML();
			}
			if (count($questions) > 1)
			{
				$xml = preg_replace("/<\/questestinterop>\s*<.xml.*?>\s*<questestinterop>/", "", $xml);
			}
		}
		$xml = preg_replace("/(<\?xml[^>]*?>)/", "\\1" . "<!DOCTYPE questestinterop SYSTEM \"ims_qtiasiv1p2p1.dtd\">", $xml);
		return $xml;
	}
	
	/**
	* Returns the number of questions in a question pool
	*
	* Returns the number of questions in a question pool
	*
	* @param integer $questonpool_id Object id of the questionpool to examine
	* @param boolean $complete_questions_only If set to TRUE, returns only the number of complete questions in the questionpool. Default is FALSE
	* @return integer The number of questions in the questionpool object
	* @access public
	*/
	function _getQuestionCount($questionpool_id, $complete_questions_only = FALSE)
	{
		global $ilDB;
		$query = sprintf("SELECT COUNT(question_id) AS question_count FROM qpl_questions WHERE obj_fi = %s AND ISNULL(original_id)",
			$ilDB->quote($questionpool_id . "")
		);
		if ($complete_questions_only)
		{
			$query .= " AND complete = '1'";
		}
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["question_count"];
	}
	
	/**
	* Sets the questionpool online status
	*
	* Sets the questionpool online status
	*
	* @param integer $a_online_status Online status of the questionpool
	* @see online
	* @access public
	*/
	function setOnline($a_online_status)
	{
		switch ($a_online_status)
		{
			case 0:
			case 1:
				$this->online = $a_online_status;
				break;
			default:
				$this->online = 0;
				break;
		}
	}
	
	function getOnline()
	{
		if (strcmp($this->online, "") == 0) $this->online = "0";
		return $this->online;
	}
	
	function _lookupOnline($a_obj_id, $is_reference = FALSE)
	{
		global $ilDB;
		
		if ($is_reference)
		{
			$query = sprintf("SELECT qpl_questionpool.online FROM qpl_questionpool,object_reference WHERE object_reference.ref_id = %s AND object_reference.obj_id = qpl_questionpool.obj_fi",
				$ilDB->quote($a_obj_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT online FROM qpl_questionpool WHERE obj_fi = %s",
				$ilDB->quote($a_obj_id . "")
			);
		}
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["online"];
		}
		return 0;
	}
	
	/**
	* Checks a question pool for questions with the same maximum points
	*
	* Checks a question pool for questions with the same maximum points
	*
	* @param integer $a_obj_id Object id of the question pool
	* @access private
	*/
	function _hasEqualPoints($a_obj_id, $is_reference = FALSE)
	{
		global $ilDB;
		
		if ($is_reference)
		{
			$query = sprintf("SELECT count(DISTINCT qpl_questions.points) AS equal_points FROM qpl_questions, object_reference WHERE object_reference.ref_id = %s AND object_reference.obj_id = qpl_questions.obj_fi AND qpl_questions.original_id IS NULL",
				$ilDB->quote($a_obj_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT count(DISTINCT points) AS equal_points FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.original_id IS NULL",
				$ilDB->quote($a_obj_id . "")
			);
		}
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["equal_points"] == 1)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		return 0;
	}
	
	/**
	* Copies/Moves a question from the clipboard
	*
	* Copies/Moves a question from the clipboard
	*
	* @access private
	*/
	function pasteFromClipboard()
	{
		global $ilDB;

		if (array_key_exists("qpl_clipboard", $_SESSION))
		{
			foreach ($_SESSION["qpl_clipboard"] as $question_object)
			{
				if (strcmp($question_object["action"], "move") == 0)
				{
					$query = sprintf("SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
						$ilDB->quote($question_object["question_id"])
					);
					$result = $ilDB->query($query);
					if ($result->numRows() == 1)
					{
						include_once "./Services/COPage/classes/class.ilPageObject.php";
						$page = new ilPageObject("qpl", $question_object["question_id"]);
						$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
						$source_questionpool = $row["obj_fi"];
						// change the questionpool id in the qpl_questions table
						$query = sprintf("UPDATE qpl_questions SET obj_fi = %s WHERE question_id = %s",
							$ilDB->quote($this->getId() . ""),
							$ilDB->quote($question_object["question_id"])
						);
						$ilDB->query($query);
						
						// change page object of question and set the correct parent id
						$query = sprintf("UPDATE page_object SET parent_id = %s WHERE page_id = %s",
							$ilDB->quote($this->getId() . ""),
							$ilDB->quote($question_object["question_id"])
						);
						$ilDB->query($query);
						
						// move question data to the new target directory
						$source_path = CLIENT_WEB_DIR . "/assessment/" . $source_questionpool . "/" . $question_object["question_id"] . "/";
						if (@is_dir($source_path))
						{
							$target_path = CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/";
							if (!@is_dir($target_path))
							{
								include_once "./Services/Utilities/classes/class.ilUtil.php";
								ilUtil::makeDirParents($target_path);
							}
							@rename($source_path, $target_path . $question_object["question_id"]);
						}
						// update question count of source question pool
						ilObjQuestionPool::_updateQuestionCount($source_questionpool);
					}
				}
				else
				{
					$this->copyQuestion($question_object["question_id"], $this->getId());
				}
			}
		}
		// update question count of question pool
		ilObjQuestionPool::_updateQuestionCount($this->getId());
		unset($_SESSION["qpl_clipboard"]);
	}
	
	/**
	* Copies a question to the clipboard
	*
	* Copies a question to the clipboard
	*
	* @param integer $question_id Object id of the question
	* @access private
	*/
	function copyToClipboard($question_id)
	{
		if (!array_key_exists("qpl_clipboard", $_SESSION))
		{
			$_SESSION["qpl_clipboard"] = array();
		}
		$_SESSION["qpl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "copy");
	}
	
	/**
	* Moves a question to the clipboard
	*
	* Moves a question to the clipboard
	*
	* @param integer $question_id Object id of the question
	* @access private
	*/
	function moveToClipboard($question_id)
	{
		if (!array_key_exists("qpl_clipboard", $_SESSION))
		{
			$_SESSION["qpl_clipboard"] = array();
		}
		$_SESSION["qpl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "move");
	}
	
/**
* Returns true, if the question pool is writeable by a given user
* 
* Returns true, if the question pool is writeable by a given user
*
* @param integer $object_id The object id of the question pool
* @param integer $user_id The database id of the user
* @access public
*/
	function _isWriteable($object_id, $user_id)
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = sprintf("SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.obj_id = %s",
			$ilDB->quote($object_id . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{		
			include_once "./classes/class.ilObject.php";
			if ($rbacsystem->checkAccess("write", $row["ref_id"]) && (ilObject::_hasUntrashedReference($row["obj_id"])))
			{
				return true;
			}
		}
		return false;
	}
	
/**
* Returns an array containing the qpl_question and qpl_question_type fields for an array of question ids
* 
* Returns an array containing the qpl_question and qpl_question_type fields for an array of question ids
*
* @param array $question_ids An array containing the question ids
* @return array An array containing the details of the requested questions
* @access public
*/
	function &getQuestionDetails($question_ids)
	{
		global $ilDB;
		
		$result = array();
		$whereclause = join($question_ids, " OR qpl_questions.question_id = ");
		$whereclause = " AND (qpl_questions.question_id = " . $whereclause . ")";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id$whereclause ORDER BY qpl_questions.title";
		$query_result = $ilDB->query($query);
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($result, $row);
			}
		}
		return $result;
	}

/**
* Returns an array containing the qpl_question and qpl_question_type fields
* of deleteable questions for an array of question ids
* 
* Returns an array containing the qpl_question and qpl_question_type fields
* of deleteable questions for an array of question ids
*
* @param array $question_ids An array containing the question ids
* @return array An array containing the details of the requested questions
* @access public
*/
	function &getDeleteableQuestionDetails($question_ids)
	{
		global $ilDB;
		
		$result = array();
		$whereclause = join($question_ids, " OR qpl_questions.question_id = ");
		$whereclause = " AND (qpl_questions.question_id = " . $whereclause . ")";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id$whereclause ORDER BY qpl_questions.title";
		$query_result = $ilDB->query($query);
		if ($query_result->numRows())
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (!assQuestion::_isUsedInRandomTest($row["question_id"]))
				{
					array_push($result, $row);
				}
				else
				{
					// the question was used in a random test prior to ILIAS 3.7 so it was inserted
					// as a reference to the original question pool object and not as a copy. To allow
					// the deletion of the question pool object, a copy must be created and all database references
					// of the original question must changed with the reference of the copy

					// 1. Create a copy of the original question
					$question =& $this->createQuestion("", $row["question_id"]);
					$duplicate_id = $question->object->duplicate(true);
					if ($duplicate_id > 0)
					{
						// 2. replace the question id in the solutions
						$query = sprintf("UPDATE tst_solutions SET question_fi = %s WHERE question_fi = %s",
							$ilDB->quote($duplicate_id),
							$ilDB->quote($row["question_id"])
						);
						$ilDB->query($query);

						// 3. replace the question id in the question list of random tests
						$query = sprintf("UPDATE tst_test_random_question SET question_fi = %s WHERE question_fi = %s",
							$ilDB->quote($duplicate_id),
							$ilDB->quote($row["question_id"])
						);
						$ilDB->query($query);

						// 4. replace the question id in the test results
						$query = sprintf("UPDATE tst_test_result SET question_fi = %s WHERE question_fi = %s",
							$ilDB->quote($duplicate_id),
							$ilDB->quote($row["question_id"])
						);
						$ilDB->query($query);

						// 5. replace the question id in the test&assessment log
						$query = sprintf("UPDATE ass_log SET question_fi = %s WHERE question_fi = %s",
							$ilDB->quote($duplicate_id),
							$ilDB->quote($row["question_id"])
						);
						$ilDB->query($query);

						// 6. The original question can be deleted, so add it to the list of questions
						array_push($result, $row);
					}
				}
			}
		}
		return $result;
	}

/**
* Returns an array containing the qpl_question and qpl_question_type fields
* of questions which are used in a random test for an array of question ids
* 
* Returns an array containing the qpl_question and qpl_question_type fields
* of questions which are used in a random test for an array of question ids
*
* @param array $question_ids An array containing the question ids
* @return array An array containing the details of the requested questions
* @access public
*/
	function &getUsedQuestionDetails($question_ids)
	{
		global $ilDB;
		
		$result = array();
		$whereclause = join($question_ids, " OR qpl_questions.question_id = ");
		$whereclause = " AND (qpl_questions.question_id = " . $whereclause . ")";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id$whereclause ORDER BY qpl_questions.title";
		$query_result = $ilDB->query($query);
		if ($query_result->numRows())
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (assQuestion::_isUsedInRandomTest($row["question_id"]))
				{
					array_push($result, $row);
				}
			}
		}
		return $result;
	}


	/**
	* Retrieves the full path to a question pool with a given reference id
	*
	* Retrieves the full path to a question pool with a given reference id
	*
	* @return string The full path to the question pool including the locator
	* @access public
	*/
	function _getFullPathToQpl($ref_id)
	{
		global $tree;
		$path = $tree->getPathFull($ref_id);
		$items = array();
		$counter = 0;
		foreach ($path as $item)
		{
			if (($counter > 0) && ($counter < count($path)-1))
			{
				array_push($items, $item["title"]);
			}
			$counter++;
		}
		$fullpath = join(" > ", $items);
		include_once "./Services/Utilities/classes/class.ilStr.php";
		if (strlen($fullpath) > 60)
		{
			$fullpath = ilStr::subStr($fullpath, 0, 30) . "..." . ilStr::subStr($fullpath, ilStr::strLen($fullpath)-30, 30);
		}
		return $fullpath;
	}

/**
* Returns the available question pools for the active user
*
* Returns the available question pools for the active user
*
* @return array The available question pools
* @access public
*/
	function &_getAvailableQuestionpools($use_object_id = FALSE, $equal_points = FALSE, $could_be_offline = FALSE, $showPath = FALSE, $with_questioncount = FALSE)
	{
		global $ilUser;
		global $ilDB;

		$result_array = array();
		$qpls = ilUtil::_getObjectsByOperations("qpl","read", $ilUser->getId(), -1);
		$titles = ilObject::_prepareCloneSelection($qpls, "qpl");
		if (count($qpls))
		{
			$query = "";
			if ($could_be_offline)
			{
				$query = sprintf("SELECT object_data.*, object_reference.ref_id, qpl_questionpool.questioncount FROM object_data, object_reference, qpl_questionpool WHERE object_data.obj_id = object_reference.obj_id AND object_reference.ref_id IN ('%s') AND qpl_questionpool.obj_fi = object_data.obj_id ORDER BY object_data.title",
					implode("','", $qpls)
				);
			}
			else
			{
				$query = sprintf("SELECT object_data.*, object_reference.ref_id, qpl_questionpool.questioncount FROM object_data, object_reference, qpl_questionpool WHERE object_data.obj_id = object_reference.obj_id AND object_reference.ref_id IN ('%s') AND qpl_questionpool.online = '1' AND qpl_questionpool.obj_fi = object_data.obj_id ORDER BY object_data.title",
					implode("','", $qpls)
				);
			}
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$add = TRUE;
				if ($equal_points)
				{
					if (!ilObjQuestionPool::_hasEqualPoints($row["obj_id"]))
					{
						$add = FALSE;
					}
				}
				if ($add)
				{
					$title = (($showPath) ? $titles[$row["ref_id"]] : $row["title"]);
					if ($with_questioncount)
					{
						$title .= " [" . $row["questioncount"] . " " . ($row["questioncount"] == 1 ? $this->lng->txt("ass_question") : $this->lng->txt("assQuestions")) . "]";
					}

					if ($use_object_id)
					{
						$result_array[$row["obj_id"]] = array("title" => $title, "count" => $row["questioncount"]);
					}
					else
					{
						$result_array[$row["ref_id"]] = array("title" => $title, "count" => $row["questioncount"]);
					}
				}
			}
		}
		return $result_array;
	}

	function &getQplQuestions()
	{
		global $ilDB;
		
		$questions = array();
		$query = sprintf("SELECT qpl_questions.question_id FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.obj_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questions, $row["question_id"]);
		}
		return $questions;
	}
/**
* Creates a 1:1 copy of the object and places the copy in a given repository
* 
* Creates a 1:1 copy of the object and places the copy in a given repository
*
* @access public
*/
	function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilLog;
		$newObj = parent::cloneObject($a_target_id,$a_copy_id);
		$newObj->setOnline($this->getOnline());
		$newObj->saveToDb();
		// clone the questions in the question pool
		$questions =& $this->getQplQuestions();
		foreach ($questions as $question_id)
		{
			$newObj->copyQuestion($question_id, $newObj->getId());
		}
		
		// clone meta data
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md = new ilMD($this->getId(),0,$this->getType());
		$new_md =& $md->cloneMD($newObj->getId(),0,$newObj->getType());

		// update the metadata with the new title of the question pool
		$newObj->updateMetaData();
		return $newObj;
	}
	
	function &getQuestionTypes($all_tags = FALSE)
	{
		return $this->_getQuestionTypes($all_tags);
	}

	function &_getQuestionTypes($all_tags = FALSE)
	{
		global $ilDB;
		global $lng;
		
		include_once "./classes/class.ilObjAssessmentFolder.php";
		$forbidden_types = ilObjAssessmentFolder::_getForbiddenQuestionTypes();
		$lng->loadLanguageModule("assessment");
		$query = "SELECT * FROM qpl_question_type";
		$result = $ilDB->query($query);
		$types = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($all_tags || (!in_array($row["question_type_id"], $forbidden_types)))
			{
				if (!DEVMODE)
				{
					if (strcmp($row["type_tag"], "assFlashApp") != 0)
					{
						$types[$lng->txt($row["type_tag"])] = $row;
					}
				}
				else
				{
					$types[$lng->txt($row["type_tag"])] = $row;
				}
			}
		}
		ksort($types);
		return $types;
	}

	function &getQuestionList()
	{
		global $ilDB;
		
		$questions = array();
		$query = sprintf("SELECT qpl_questions.*, qpl_questions.TIMESTAMP+0 AS TIMESTAMP14, qpl_question_type.* FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.obj_fi = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questions, $row);
		}
		return $questions;
	}
	
	/**
	* Updates the number of available questions for a question pool in the database
	*
	* Updates the number of available questions for a question pool in the database
	*
	* @param integer $object_id Object id of the questionpool to examine
	* @access public
	*/
	public static function _updateQuestionCount($object_id)
	{
		global $ilDB;
		$query = sprintf("UPDATE qpl_questionpool SET questioncount = %s WHERE obj_fi = %s",
			$ilDB->quote(ilObjQuestionPool::_getQuestionCount($object_id, TRUE)),
			$ilDB->quote($object_id)
		);
		$result = $ilDB->query($query);
	}
	
} // END class.ilObjQuestionPool
?>
