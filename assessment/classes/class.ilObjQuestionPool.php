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
* @author Helmut Schottmüller <hschottm@tzi.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObjectGUI.php";
require_once("classes/class.ilMetaData.php");
require_once "class.assClozeTestGUI.php";
require_once "class.assImagemapQuestionGUI.php";
require_once "class.assJavaAppletGUI.php";
require_once "class.assMatchingQuestionGUI.php";
require_once "class.assMultipleChoiceGUI.php";
require_once "class.assOrderingQuestionGUI.php";

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
	function clone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// get object instance of cloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");		

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
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
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE ref_fi = %s",
			$this->ilias->db->quote($this->getRefId())
		);
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
				$this->delete_question($question_id);
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
  function delete_question($question_id) 
  {
    if ($question_id < 1)
      return;
      
    $query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_question_type, qpl_questions WHERE question_id = %s AND qpl_question_type.question_type_id = qpl_questions.question_type_fi",
      $this->ilias->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      $query = sprintf("DELETE FROM qpl_questions WHERE question_id = %s",
        $this->ilias->db->quote($question_id)
      );
      $result = $this->ilias->db->query($query);
      $query = sprintf("DELETE FROM qpl_question_material WHERE question_id = %s",
        $this->ilias->db->quote($question_id)
      );
      $result = $this->ilias->db->query($query);
      $query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
        $this->ilias->db->quote($question_id)
      );
      $result = $this->ilias->db->query($query);
			$question = new ASS_Question();
			$question->setId($question_id);
			$question->remove_all_question_references();
			// delete the question in the tst_test_question table (list of test questions)
			$querydelete = sprintf("DELETE FROM tst_test_question WHERE question_fi = %s", $this->ilias->db->quote($question_id));
			$deleteresult = $this->ilias->db->query($querydelete);
    } else {
      return;
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
      return;
      
    $query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.question_id = %s",
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
	
	function get_total_answers($question_id)
	{
		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi = %s GROUP BY CONCAT(user_fi,test_fi)",
			$this->ilias->db->quote($question_id)
		);
    $result = $this->ilias->db->query($query);
		return $result->numRows();	
	}

	function get_total_right_answers($question_id)
	{
		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi = %s GROUP BY CONCAT(user_fi,test_fi)",
			$this->ilias->db->quote($question_id)
		);
    $result = $this->ilias->db->query($query);
		$answers = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
    	$question =& $this->create_question("", $row->question_fi);
			$reached = $question->object->get_reached_points($row->user_fi, $row->test_fi);
			$max = $question->get_maximum_points();
			array_push($answers, array("reached" => $reached, "max" => $max));
		}
		$max = 0.0;
		$reached = 0.0;
		foreach ($answers as $key => $value) {
			$max += $value["max"];
			$reached += $value["reached"];
		}
		return $reached / $max;
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
//		return parent::getDescription();
		return $this->meta_data->getDescription();
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
		//return $this->title;
		return $this->meta_data->getTitle();
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
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->meta_data->update();
		$this->setTitle($this->meta_data->getTitle());
		$this->setDescription($this->meta_data->getDescription());
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
	function is_in_use($question_id) {
		$query = sprintf("SELECT COUNT(solution_id) AS solution_count FROM tst_solutions WHERE question_fi = %s",
			$this->ilias->db->quote("$question_id")
		);
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->solution_count;
	}

  function &createQuestion($question_type, $question_id = -1) {
    if ((!$question_type) and ($question_id > 0)) {
			$question_type = $this->getQuestiontype($question_id);
    }
    switch ($question_type) {
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
	
} // END class.ilObjQuestionPool
?>
