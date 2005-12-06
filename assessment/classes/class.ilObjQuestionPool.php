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
* Class ilObjQuestionPool
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
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
	* copy all entries of your object.
	*
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);

		// get object instance of ilCloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");

		// always destroy objects in ilClone method because ilClone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
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
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND original_id IS NULL",
			$this->ilias->db->quote($this->getId()));

		$result = $this->ilias->db->query($query);
		$questions = array();

		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questions, $row["question_id"]);
		}

		if (count($questions))
		{
			foreach ($questions as $question_id)
			{
				$this->deleteQuestion($question_id);
			}
		}

		// delete export files
		include_once "./classes/class.ilUtil.php";
		$qpl_data_dir = ilUtil::getDataDir()."/qpl_data";
		$directory = $qpl_data_dir."/qpl_".$this->getId();
		if (is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
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
		include_once "./assessment/classes/class.assQuestion.php";
		$question = new ASS_Question();
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
		if ($question_id < 1)
		{
			return;
		}

		$query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.question_id = %s",
			$this->ilias->db->quote($question_id));

		$result = $this->ilias->db->query($query);
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
		$query = sprintf("SELECT COUNT(solution_id) AS solution_count FROM tst_solutions WHERE question_fi = %s",
			$this->ilias->db->quote("$question_id"));

		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);

		return $row->solution_count;
	}

	function &createQuestion($question_type, $question_id = -1)
	{
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = $this->getQuestiontype($question_id);
		}

		switch ($question_type)
		{
			case "qt_multiple_choice_sr":
				include_once "./assessment/classes/class.assMultipleChoiceGUI.php";
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_SINGLE);
				break;

			case "qt_multiple_choice_mr":
				include_once "./assessment/classes/class.assMultipleChoiceGUI.php";
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_MULTIPLE);
				break;

			case "qt_cloze":
				include_once "./assessment/classes/class.assClozeTestGUI.php";
				$question =& new ASS_ClozeTestGUI();
				break;

			case "qt_matching":
				include_once "./assessment/classes/class.assMatchingQuestionGUI.php";
				$question =& new ASS_MatchingQuestionGUI();
				break;

			case "qt_ordering":
				include_once "./assessment/classes/class.assOrderingQuestionGUI.php";
				$question =& new ASS_OrderingQuestionGUI();
				break;

			case "qt_imagemap":
				include_once "./assessment/classes/class.assImagemapQuestionGUI.php";
				$question =& new ASS_ImagemapQuestionGUI();
				break;

			case "qt_javaapplet":
				include_once "./assessment/classes/class.assJavaAppletGUI.php";
				$question =& new ASS_JavaAppletGUI();
				break;

			case "qt_text":
				include_once "./assessment/classes/class.assTextQuestionGUI.php";
				$question =& new ASS_TextQuestionGUI();
				break;
		}

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
	function getQuestionsTable($sortoptions, $filter_text, $sel_filter_type, $startrow = 0)
	{
		global $ilUser;
		$where = "";
		if (strlen($filter_text) > 0)
		{
			switch($sel_filter_type)
			{
				case "title":
					$where = " AND qpl_questions.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;

				case "comment":
					$where = " AND qpl_questions.comment LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;

				case "author":
					$where = " AND qpl_questions.author LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}

	    // build sort order for sql query
		$order = "";
		$images = array();
		include_once "./classes/class.ilUtil.php";
		if (count($sortoptions))
		{
			foreach ($sortoptions as $key => $value)
			{
				switch($key)
				{
					case "title":
						$order = " ORDER BY title $value";
						$images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
					case "comment":
						$order = " ORDER BY comment $value";
						$images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
					case "type":
						$order = " ORDER BY question_type_id $value";
						$images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
					case "author":
						$order = " ORDER BY author $value";
						$images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
					case "created":
						$order = " ORDER BY created $value";
						$images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
					case "updated":
						$order = " ORDER BY TIMESTAMP14 $value";
						$images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
						break;
				}
			}
		}
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
		$query = "SELECT qpl_questions.question_id, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14 FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $where$order$limit";
		$query_result = $this->ilias->db->query($query);
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
		$query_result = $this->ilias->db->query($query);
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
			include_once "./classes/class.ilUtil.php";
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

		include_once "./content/classes/class.ilLMPageObject.php";

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
		include_once("content/classes/Media/class.ilObjMediaObject.php");

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
		include_once("classes/class.ilObjFile.php");

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
		include_once "./classes/class.ilUtil.php";
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
		include_once "./classes/class.ilUtil.php";
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
				substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(qpl__)*[0-9]+\.zip\$", $entry))
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
		
		include_once "./classes/class.ilUtil.php";
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
		include_once "./classes/class.ilUtil.php";
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
		include_once "./classes/class.ilUtil.php";
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
	
	function &getAllQuestionIds()
	{
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(original_id) AND obj_fi = %s AND complete = %s",
			$this->ilias->db->quote($this->getId()),
			$this->ilias->db->quote("1")
		);
		$query_result = $this->ilias->db->query($query);
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
	function to_xml($questions)
	{
		$xml = "";
		// export button was pressed
		if (count($questions) > 0)
		{
			foreach ($questions as $key => $value)
			{
				$question =& $this->createQuestion("", $value);
				$xml .= $question->object->to_xml();
			}
			if (count($questions) > 1)
			{
				$xml = preg_replace("/<\/questestinterop>\s*<.xml.*?>\s*<questestinterop>/", "", $xml);
			}
		}
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
	
	function _lookupOnline($a_obj_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT online FROM qpl_questionpool WHERE obj_fi = %s",
			$ilDB->quote($a_obj_id . "")
		);
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
	function _hasEqualPoints($a_obj_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT count(DISTINCT points) AS equal_points FROM qpl_questions WHERE obj_fi = %s",
			$ilDB->quote($a_obj_id . "")
		);
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
	* Copies a question to the clipboard
	*
	* Copies a question to the clipboard
	*
	* @param integer $question_id Object id of the question
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
						include_once "./content/classes/Pages/class.ilPageObject.php";
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
								include_once "./classes/class.ilUtil.php";
								ilUtil::makeDirParents($target_path);
							}
							@rename($source_path, $target_path . $question_object["question_id"]);
						}
					}
				}
				else
				{
					$this->copyQuestion($question_object["question_id"], $this->getId());
				}
			}
		}
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
} // END class.ilObjQuestionPool
?>
