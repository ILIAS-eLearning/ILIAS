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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
*/

require_once "./classes/class.ilObjectGUI.php";
require_once "./classes/class.ilMetaData.php";
require_once "./survey/classes/class.SurveyNominalQuestion.php";
require_once "./survey/classes/class.SurveyTextQuestion.php";
require_once "./survey/classes/class.SurveyMetricQuestion.php";
require_once "./survey/classes/class.SurveyOrdinalQuestion.php";
require_once "./survey/classes/class.SurveyQuestion.php";

class ilObjSurveyQuestionPool extends ilObject
{
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
		$remove = parent::delete();
		// always call parent delete function first!!
		if (!$remove)
		{
			return false;
		}
		
		// delete all related questions
		$this->deleteAllData();
		
		return true;
	}

	function deleteAllData()
	{
		$query = sprintf("SELECT question_id FROM survey_question WHERE obj_fi = %s",
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		$found_questions = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->removeQuestion($row["question_id"]);
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
	* get title of survey question pool object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		//return $this->title;
		return $this->meta_data->getTitle();
	}

	/**
	* set title of survey question pool object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
		$this->meta_data->setTitle($a_title);
	}

	/**
	* assign a meta data object to survey question pool object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of survey question pool object
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
		
		$question = new SurveyQuestion();
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
	
	function getPhrase($phrase_id)
	{
		$query = sprintf("SELECT title FROM survey_phrase WHERE phrase_id = %s",
			$this->ilias->db->quote($phrase_id)
		);
    $result = $this->ilias->db->query($query);
		if ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $row["title"];
		}
		return "";
	}

/**
* Duplicates a question for a questionpool
*
* Duplicates a question for a questionpool
*
* @param integer $question_id The database id of the question
* @access public
*/
  function duplicateQuestion($question_id, $obj_id = "") {
		global $ilUser;
		
		$questiontype = $this->getQuestiontype($question_id);
		switch ($questiontype)
		{
			case "qt_nominal":
				$question = new SurveyNominalQuestion();
				break;
			case "qt_ordinal":
				$question = new SurveyOrdinalQuestion();
				break;
			case "qt_metric":
				$question = new SurveyMetricQuestion();
				break;
			case "qt_text":
				$question = new SurveyTextQuestion();
				break;
		}
		$question->loadFromDb($question_id);
    $counter = 2;
    while ($question->questionTitleExists($question->getTitle() . " ($counter)")) {
      $counter++;
    }
		if ($obj_id)
		{
			$question->setObjId($obj_id);
		}
		$question->duplicate(false, $question->getTitle() . " ($counter)", $ilUser->fullname, $ilUser->id);
  }
	
/**
* Delete phrases from the database
*
* Delete phrases from the database
*
* @param array $phrase_array An array containing phrase id's to delete
* @access public
*/
	function deletePhrases($phrase_array)
	{
		$query = "DELETE FROM survey_phrase WHERE phrase_id IN (" . join($phrase_array, ",") . ")";
		$result = $this->ilias->db->query($query);
		$query = "DELETE FROM survey_phrase_category WHERE phrase_fi IN (" . join($phrase_array, ",") . ")";
		$result = $this->ilias->db->query($query);
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
					$where = " AND qpl_questions.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "description":
					$where = " AND qpl_questions.description LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND qpl_questions.author LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}
  
    // build sort order for sql query
		$order = "";
		$images = array();
    if (count($sortoptions)) {
      foreach ($sortoptions as $key => $value) {
        switch($key) {
          case "title":
            $order = " ORDER BY title $value";
            $images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "description":
            $order = " ORDER BY description $value";
            $images["description"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "type":
            $order = " ORDER BY questiontype_id $value";
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
    $query = "SELECT survey_question.question_id FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.obj_fi = " . $this->getId() . " AND ISNULL(survey_question.original_id) $where$order$limit";
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
    $query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.obj_fi = " . $this->getId() . " AND ISNULL(survey_question.original_id) $where$order$limit";
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
	
} // END class.ilSurveyObjQuestionPool
?>
