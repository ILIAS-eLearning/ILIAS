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

require_once "./classes/class.ilObjectGUI.php";
require_once "./classes/class.ilMetaData.php";
require_once "./assessment/classes/class.assQuestion.php";
require_once "./assessment/classes/class.assClozeTestGUI.php";
require_once "./assessment/classes/class.assImagemapQuestionGUI.php";
require_once "./assessment/classes/class.assJavaAppletGUI.php";
require_once "./assessment/classes/class.assMatchingQuestionGUI.php";
require_once "./assessment/classes/class.assMultipleChoiceGUI.php";
require_once "./assessment/classes/class.assOrderingQuestionGUI.php";

class ilObjQuestionPool extends ilObject
{
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
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}
	}

	/**
	* create question pool object
	*/
	function create($a_upload = false)
	{
		parent::create();
		if (!$a_upload)
		{
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();
		}
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
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
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());
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

		//put here your module specific stuff
		$this->deleteQuestionpool();

		return true;
	}

	function deleteQuestionpool()
	{
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE obj_fi = %s",
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
		$question = new ASS_Question();
		$question->delete($question_id);
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

	/* moved to ASS_Question
	function get_total_answers($question_id)
	{
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s",
			$this->ilias->db->quote($question_id));

		$result = $this->ilias->db->query($query);

		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}

		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi IN (%s) GROUP BY CONCAT(user_fi,test_fi)",
			join($found_id, ","));

		$result = $this->ilias->db->query($query);
		return $result->numRows();
	}*/

	/* moved to ASS_Question
	function get_total_right_answers($question_id)
	{
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s",
			$this->ilias->db->quote($question_id));

		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi IN (%s) GROUP BY CONCAT(user_fi,test_fi)",
			join($found_id, ","));

		$result = $this->ilias->db->query($query);
		$answers = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
    		$question =& $this->createQuestion("", $row->question_fi);
			$reached = $question->object->getReachedPoints($row->user_fi, $row->test_fi);
			$max = $question->object->getMaximumPoints();
			array_push($answers, array("reached" => $reached, "max" => $max));
		}
		$max = 0.0;
		$reached = 0.0;
		foreach ($answers as $key => $value)
		{
			$max += $value["max"];
			$reached += $value["reached"];
		}
		return $reached / $max;
	}*/

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return parent::getDescription();
		//return $this->meta_data->getDescription();
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
		$this->meta_data->setDescription($a_description);
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
		$this->meta_data->setTitle($a_title);
	}

	/**
	* assign a meta data object to glossary object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of glossary object
	*
	* @return	object		meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}

	/**
	* init meta data object if needed
	*/
	function initMeta()
	{
		if (!is_object($this->meta_data))
		{
			if ($this->getId())
			{
				$new_meta =& new ilMetaData($this->getType(), $this->getId());
			}	
			else
			{
				$new_meta =& new ilMetaData();
			}
			$this->assignMetaData($new_meta);
		}
	}

	/**
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->initMeta();
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();
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
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_SINGLE);
				break;

			case "qt_multiple_choice_mr":
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_MULTIPLE);
				break;

			case "qt_cloze":
				$question =& new ASS_ClozeTestGUI();
				break;

			case "qt_matching":
				$question =& new ASS_MatchingQuestionGUI();
				break;

			case "qt_ordering":
				$question =& new ASS_OrderingQuestionGUI();
				break;

			case "qt_imagemap":
				$question =& new ASS_ImagemapQuestionGUI();
				break;

			case "qt_javaapplet":
				$question =& new ASS_JavaAppletGUI();
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
		global $ilUser;

		$question =& $this->createQuestion("", $question_id);
		$counter = 2;
		while ($question->object->questionTitleExists($question->object->getTitle() . " ($counter)"))
		{
			$counter++;
		}

		$question->object->duplicate(false, $question->object->getTitle() . " ($counter)", $ilUser->fullname, $ilUser->id);
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
		if (count($sortoptions))
		{
			foreach ($sortoptions as $key => $value)
			{
				switch($key)
				{
					case "title":
						$order = " ORDER BY title $value";
						$images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
					case "comment":
						$order = " ORDER BY comment $value";
						$images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
					case "type":
						$order = " ORDER BY question_type_id $value";
						$images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
					case "author":
						$order = " ORDER BY author $value";
						$images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
					case "created":
						$order = " ORDER BY created $value";
						$images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
					case "updated":
						$order = " ORDER BY TIMESTAMP $value";
						$images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
				}
			}
		}
		$maxentries = $ilUser->prefs["hits_per_page"];
		$query = "SELECT qpl_questions.question_id FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $where$order$limit";
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
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.obj_fi = " . $this->getId() . " $where$order$limit";
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
		$nested = new ilNestedSetXML();
		$nested->setParameterModifier($this, "modifyExportIdentifier");
		$a_xml_writer->appendXML($nested->export($this->getId(),
			$this->getType()));
	}

	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
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
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(qpl_)*[0-9]+\.zip\$", $entry))
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
	function createImportDirectory()
	{
		$qpl_data_dir = ilUtil::getDataDir()."/qpl_data";
		ilUtil::makeDir($qpl_data_dir);
		
		if(!is_writable($qpl_data_dir))
		{
			$this->ilias->raiseError("Questionpool Data Directory (".$qpl_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create questionpool directory (data_dir/qpl_data/qpl_<id>)
		$qpl_dir = $qpl_data_dir."/qpl_".$this->getId();
		ilUtil::makeDir($qpl_dir);
		if(!@is_dir($qpl_dir))
		{
			$this->ilias->raiseError("Creation of Questionpool Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/qpl_data/qpl_<id>/import)
		$import_dir = $qpl_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get import directory of lm
	*/
	function getImportDirectory()
	{
		$import_dir = ilUtil::getDataDir()."/qpl_data".
			"/qpl_".$this->getId()."/import";
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
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(original_id) AND obj_fi = %s",
			$this->ilias->db->quote($this->getId())
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
} // END class.ilObjQuestionPool
?>
