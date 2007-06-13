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
* Class ilObjSurveyQuestionPool
* 
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @defgroup ModulesSurveyQuestionPool Modules/SurveyQuestionPool
*/

include_once "./classes/class.ilObject.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

class ilObjSurveyQuestionPool extends ilObject
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
	function ilObjSurveyQuestionPool($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "spl";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* create question pool object
	*/
	function create($a_upload = false)
	{
		parent::create();
		if(!$a_upload)
		{
			$this->createMetaData();
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
	* Loads a ilObjQuestionpool object from a database
	*
	* Loads a ilObjQuestionpool object from a database
	*
	* @access public
	*/
	function loadFromDb()
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM survey_questionpool WHERE obj_fi = %s",
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
* Saves a ilObjSurveyQuestionPool object to a database
* 
* Saves a ilObjSurveyQuestionPool object to a database
*
* @access public
*/
  function saveToDb()
  {
		global $ilDB;
		
		$query = sprintf("SELECT * FROM survey_questionpool WHERE obj_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$query = sprintf("UPDATE survey_questionpool SET online = %s WHERE obj_fi = %s",
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
			$query = sprintf("INSERT INTO survey_questionpool (online, obj_fi) VALUES (%s, %s)",
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
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		$remove = parent::delete();
		// always call parent delete function first!!
		if (!$remove)
		{
			return false;
		}

		// delete all related questions
		$this->deleteAllData();

		// delete meta data
		$this->deleteMetaData();
		
		return true;
	}

	function deleteAllData()
	{
		$query = sprintf("SELECT question_id FROM survey_question WHERE obj_fi = %s AND original_id IS NULL",
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		$found_questions = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->removeQuestion($row["question_id"]);
		}

		// delete export files
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$spl_data_dir = ilUtil::getDataDir()."/spl_data";
		$directory = $spl_data_dir."/spl_".$this->getId();
		if (is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}
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
	* get title of survey question pool object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		//return $this->title;
		return parent::getTitle();
	}

	/**
	* set title of survey question pool object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
	}

/**
* Removes a question from the question pool
*
* Removes a question from the question pool
*
* @param integer $question_id The database id of the question
* @access private
*/
  function removeQuestion($question_id)
  {
    if ($question_id < 1)
      return;
		
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		$question =& SurveyQuestion::_instanciateQuestion($question_id);
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
      return;
      
    $query = sprintf("SELECT survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.question_id = %s",
      $this->ilias->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
			return $data->type_tag;
    } else {
      return;
    }
  }
	
/**
* Checks if a question is in use by a survey
* 
* Checks if a question is in use by a survey
*
* @param integer $question_id The database id of the question
* @result mixed An array of the surveys which use the question, when the question is in use by at least one survey, otherwise false
* @access public
*/
	function isInUse($question_id)
	{
		// check out the already answered questions
		$query = sprintf("SELECT answer_id FROM survey_answer WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
    $result = $this->ilias->db->query($query);
		$answered = $result->numRows();
		
		// check out the questions inserted in surveys
		$query = sprintf("SELECT survey_survey.* FROM survey_survey, survey_survey_question WHERE survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
    $result = $this->ilias->db->query($query);
		$inserted = $result->numRows();
		if (($inserted + $answered) == 0)
		{
			return false;
		}
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row);
		}
		return $result_array;
	}
	
/**
* Pastes a question in the question pool
* 
* Pastes a question in the question pool
*
* @param integer $question_id The database id of the question
* @access public
*/
	function paste($question_id)
	{
		$this->duplicateQuestion($question_id, $this->getId());
	}
	
/**
* Retrieves the datase entries for questions from a given array
* 
* Retrieves the datase entries for questions from a given array
*
* @param array $question_array An array containing the id's of the questions
* @result array An array containing the database rows of the given question id's
* @access public
*/
	function &getQuestionsInfo($question_array)
	{
		$result_array = array();
		$query = sprintf("SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.question_id IN (%s)",
			join($question_array, ",")
		);
    $result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row);
		}
		return $result_array;
	}
	
/**
* Duplicates a question for a questionpool
*
* Duplicates a question for a questionpool
*
* @param integer $question_id The database id of the question
* @access public
*/
  function duplicateQuestion($question_id, $obj_id = "") 
	{
		global $ilUser;
		
		$questiontype = $this->getQuestiontype($question_id);
		include_once "./Modules/SurveyQuestionPool/classes/class.$questiontype.php";
		$question = new $questiontype();
		$question->loadFromDb($question_id);
		$suffix = "";
    $counter = 1;
    while ($question->questionTitleExists($question->getTitle().$suffix, $obj_id)) 
		{
      $counter++;
			if ($counter > 1) $suffix = " ($counter)";
    }
		if ($obj_id)
		{
			$question->setObjId($obj_id);
		}
		$question->duplicate(false, $question->getTitle() . $suffix, $ilUser->fullname, $ilUser->id);
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
		if (strlen($filter_text) > 0) {
			switch($sel_filter_type) {
				case "title":
					$where = " AND survey_question.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "description":
					$where = " AND survey_question.description LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND survey_question.author LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}
  
    // build sort order for sql query
		$order = "";
		$images = array();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
    if (count($sortoptions)) 
		{
      foreach ($sortoptions as $key => $value) 
			{
        switch($key) 
				{
          case "title":
            $order = " ORDER BY title $value";
            $images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "description":
            $order = " ORDER BY description $value";
            $images["description"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "type":
            $order = " ORDER BY questiontype_id $value";
            $images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "author":
            $order = " ORDER BY author $value";
            $images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "created":
            $order = " ORDER BY created $value";
            $images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "updated":
            $order = " ORDER BY TIMESTAMP14 $value";
            $images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.gif") . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
        }
      }
    }
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
    $query = "SELECT survey_question.question_id, survey_question.TIMESTAMP + 0 AS TIMESTAMP14 FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.obj_fi = " . $this->getId() . " AND ISNULL(survey_question.original_id) $where$order$limit";
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
    $query = "SELECT survey_question.*, survey_question.TIMESTAMP + 0 AS TIMESTAMP14, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.obj_fi = " . $this->getId() . " AND ISNULL(survey_question.original_id) $where$order$limit";
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
	* creates data directory for export files
	* (data_dir/spl_data/spl_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$spl_data_dir = ilUtil::getDataDir()."/spl_data";
		ilUtil::makeDir($spl_data_dir);
		if(!is_writable($spl_data_dir))
		{
			$this->ilias->raiseError("Survey Questionpool Data Directory (".$spl_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$spl_dir = $spl_data_dir."/spl_".$this->getId();
		ilUtil::makeDir($spl_dir);
		if(!@is_dir($spl_dir))
		{
			$this->ilias->raiseError("Creation of Survey Questionpool Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $spl_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of survey
	*/
	function getExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export_dir = ilUtil::getDataDir()."/spl_data"."/spl_".$this->getId()."/export";
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
				substr($entry, -4) == ".xml" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(spl__)*[0-9]+\.xml\$", $entry))
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
	* (data_dir/spl_data/spl_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$spl_data_dir = ilUtil::getDataDir()."/spl_data";
		ilUtil::makeDir($spl_data_dir);
		
		if(!is_writable($spl_data_dir))
		{
			$this->ilias->raiseError("Survey Questionpool Data Directory (".$spl_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/spl_data/spl_<id>)
		$spl_dir = $spl_data_dir."/spl_".$this->getId();
		ilUtil::makeDir($spl_dir);
		if(!@is_dir($spl_dir))
		{
			$this->ilias->raiseError("Creation of Survey Questionpool Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/spl_data/spl_<id>/import)
		$import_dir = $spl_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get import directory of survey
	*/
	function getImportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/spl_data".
			"/spl_".$this->getId()."/import";
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
	* export questions to xml
	*/
	function toXML($questions)
	{
		if (!is_array($questions))
		{
			$questions =& $this->getQuestions();
		}
		if (count($questions) == 0)
		{
			$questions =& $this->getQuestions();
		}
		$xml = "";

		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$attrs = array(
			"xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
			"xsi:noNamespaceSchemaLocation" => "http://www.ilias.de/download/xsd/ilias_survey_3_8.xsd"
		);
		$a_xml_writer->xmlStartTag("surveyobject", $attrs);
		$attrs = array(
			"id" => "qpl_" . $this->getId(),
			"label" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("surveyquestions", $attrs);
		$a_xml_writer->xmlElement("dummy", NULL, "dummy");
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("metadata");
		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "SCORM");
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md = new ilMD($this->getId(),0, $this->getType());
		$writer = new ilXmlWriter();
		$md->toXml($writer);
		$metadata = $writer->xmlDumpMem();
		$a_xml_writer->xmlElement("fieldentry", NULL, $metadata);
		$a_xml_writer->xmlEndTag("metadatafield");
		$a_xml_writer->xmlEndTag("metadata");

		$a_xml_writer->xmlEndTag("surveyquestions");
		$a_xml_writer->xmlEndTag("surveyobject");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);

		$questionxml = "";
		foreach ($questions as $key => $value)
		{
			$questiontype = $this->getQuestiontype($value);
			include_once "./Modules/SurveyQuestionPool/classes/class.$questiontype.php";
			$question = new $questiontype();
			$question->loadFromDb($value);
			$questionxml .= $question->toXML(false);
		}
		
		$xml = str_replace("<dummy>dummy</dummy>", $questionxml, $xml);
		return $xml;
	}

	function &getQuestions()
	{
		$questions = array();
		$query = sprintf("SELECT question_id FROM survey_question WHERE obj_fi = %s AND ISNULL(original_id)",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($questions, $row["question_id"]);
			}
		}
		return $questions;
	}

	/**
	* Imports survey questions into ILIAS
	*
	* Imports survey questions into ILIAS
	*
	* @param string $source The filename of an XML import file
	* @access public
	*/
	function importObject($source, $spl_exists = FALSE)
	{
		if (is_file($source))
		{
			$fh = fopen($source, "r") or die("");
			$xml = fread($fh, filesize($source));
			fclose($fh) or die("");
			if (strpos($xml, "questestinterop") > 0)
			{
				// survey questions for ILIAS < 3.8
				include_once "./Services/Survey/classes/class.SurveyImportParserPre38.php";
				$import = new SurveyImportParserPre38($this, "", $spl_exists);
				$import->setXMLContent($xml);
				$import->startParsing();
			}
			else
			{
				// survey questions for ILIAS >= 3.8
				include_once "./Services/Survey/classes/class.SurveyImportParser.php";
				$import = new SurveyImportParser($this, "", $spl_exists);
				$import->setXMLContent($xml);
				$import->startParsing();
			}
		}
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
		
		$query = sprintf("SELECT online FROM survey_questionpool WHERE obj_fi = %s",
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
	* Creates a list of all available question types
	*
	* Creates a list of all available question types
	*
	* @return array An array containing the available questiontypes
	* @access public
	*/
	function &_getQuestiontypes()
	{
		global $ilDB;
		
		$questiontypes = array();
		$query = "SELECT * FROM survey_questiontype ORDER BY type_tag";
		$query_result = $ilDB->query($query);
		while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questiontypes, $row["type_tag"]);
		}
		return $questiontypes;
	}
		
	/**
	* Returns the available question pools for the active user
	*
	* Returns the available question pools for the active user
	*
	* @return array The available question pools
	* @access public
	*/
	function &_getAvailableQuestionpools($use_object_id = FALSE, $could_be_offline = FALSE, $showPath = FALSE)
	{
		global $ilUser;
		global $ilDB;

		$result_array = array();
		$qpls = ilUtil::_getObjectsByOperations("spl","read", $ilUser->getId(), -1);
		$titles = ilObject::_prepareCloneSelection($qpls, "spl");
		if (count($qpls))
		{
			$query = "";
			if ($could_be_offline)
			{
				$query = sprintf("SELECT object_data.*, object_reference.ref_id FROM object_data, object_reference, survey_questionpool WHERE object_data.obj_id = object_reference.obj_id AND object_reference.ref_id IN (%s) AND survey_questionpool.obj_fi = object_data.obj_id ORDER BY object_data.title",
					implode(",", $qpls)
				);
			}
			else
			{
				$query = sprintf("SELECT object_data.*, object_reference.ref_id FROM object_data, object_reference, survey_questionpool WHERE object_data.obj_id = object_reference.obj_id AND object_reference.ref_id IN (%s) AND survey_questionpool.online = '1' AND survey_questionpool.obj_fi = object_data.obj_id ORDER BY object_data.title",
					implode(",", $qpls)
				);
			}
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$title = (($showPath) ? $titles[$row["ref_id"]] : $row["title"]);

				if ($use_object_id)
				{
					$result_array[$row["obj_id"]] = $title;
				}
				else
				{
					$result_array[$row["ref_id"]] = $title;
				}
			}
		}
		return $result_array;
	}

} // END class.ilSurveyObjQuestionPool
?>
