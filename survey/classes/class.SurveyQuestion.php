<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Basic class for all survey question types
*
* The SurveyQuestion class defines and encapsulates basic methods and attributes
* for survey question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyQuestion.php
* @modulegroup   Survey
*/
class SurveyQuestion {
/**
* Question id
*
* A unique question id
*
* @var integer
*/
  var $id;

/**
* Question title
*
* A title string to describe the question
*
* @var string
*/
  var $title;
/**
* Question description
*
* A description string to describe the question more detailed as the title
*
* @var string
*/
  var $description;
/**
* Question owner/creator
*
* A unique positive numerical ID which identifies the owner/creator of the question.
* This can be a primary key from a database table for example.
*
* @var integer
*/
  var $owner;

/**
* Contains the name of the author
*
* A text representation of the authors name. The name of the author must
* not necessary be the name of the owner.
*
* @var string
*/
  var $author;

/**
* Contains uris name and uris to additional materials
*
* Contains uris name and uris to additional materials
*
* @var array
*/
  var $materials;

/**
* The database id of a survey in which the question is contained
*
* The database id of a survey in which the question is contained
*
* @var integer
*/
  var $survey_id;

/**
* Object id of the container object
*
* Object id of the container object
*
* @var double
*/
  var $obj_id;

/**
* Contains the questiontext
*
* Questiontext string
*
* @var string
*/
  var $questiontext;

/**
* Contains the obligatory state of the question
*
* Contains the obligatory state of the question
*
* @var boolean
*/
  var $obligatory;
	
/**
* The reference to the ILIAS class
*
* The reference to the ILIAS class
*
* @var object
*/
  var $ilias;

/**
* The reference to the Template class
*
* The reference to the Template class
*
* @var object
*/
  var $tpl;

/**
* The reference to the Language class
*
* The reference to the Language class
*
* @var object
*/
  var $lng;

	/**
	* The domxml representation of the question in qti
	*
	* The domxml representation of the question in qti
	*
	* @var object
	*/
	var $domxml;

/**
* SurveyQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1
  )

  {
		global $ilias;
    global $lng;
    global $tpl;

		$this->ilias =& $ilias;
    $this->lng =& $lng;
    $this->tpl =& $tpl;

    $this->title = $title;
    $this->description = $description;
		$this->questiontext = $questiontext;
    $this->author = $author;
    if (!$this->author) {
      $this->author = $this->ilias->account->fullname;
    }
    $this->owner = $owner;
    if ($this->owner == -1) {
      $this->owner = $this->ilias->account->id;
    }
    $this->id = -1;
    $this->survey_id = -1;
		$this->obligatory = 1;
		register_shutdown_function(array(&$this, '_SurveyQuestion'));
	}

	function _SurveyQuestion()
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
	}

	
/**
* Returns true, if a question is complete for use
*
* Returns true, if a question is complete for use
*
* @return boolean True, if the question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		return false;
	}

/**
* Returns TRUE if the question title exists in the database
*
* Returns TRUE if the question title exists in the database
*
* @param string $title The title of the question
* @param string $questionpool_reference The reference id of a container question pool
* @return boolean The result of the title check
* @access public
*/
  function questionTitleExists($title, $questionpool_object = "") {
		$refwhere = "";
		if (strcmp($questionpool_reference, "") != 0)
		{
			$refwhere = sprintf(" AND obj_fi = %s",
				$this->ilias->db->quote($questionpool_object)
			);
		}
    $query = sprintf("SELECT question_id FROM survey_question WHERE title = %s$refwhere",
      $this->ilias->db->quote($title)
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        return TRUE;
      }
    }
    return FALSE;
  }

/**
* Sets the title string
*
* Sets the title string of the SurveyQuestion object
*
* @param string $title A title string to describe the question
* @access public
* @see $title
*/
  function setTitle($title = "") {
    $this->title = $title;
  }

/**
* Sets the obligatory state of the question
*
* Sets the obligatory state of the question
*
* @param boolean $obligatory True, if the question is obligatory, otherwise false
* @access public
* @see $obligatory
*/
  function setObligatory($obligatory = 1) {
		if ($obligatory)
		{
	    $this->obligatory = 1;
		}
		else
		{
	    $this->obligatory = 0;
		}
  }

/**
* Sets the id
*
* Sets the id of the SurveyQuestion object
*
* @param integer $id A unique integer value
* @access public
* @see $id
*/
  function setId($id = -1) {
    $this->id = $id;
  }

/**
* Sets the survey id
*
* Sets the survey id of the SurveyQuestion object
*
* @param integer $id A unique integer value
* @access public
* @see $survey_id
*/
  function setSurveyId($id = -1) {
    $this->survey_id = $id;
  }

/**
* Sets the description
*
* Sets the description string of the SurveyQuestion object
*
* @param string $description A description string to describe the question
* @access public
* @see $description
*/
  function setDescription($description = "") {
    $this->description = $description;
  }


/**
* Sets the materials uri
*
* Sets the materials uri
*
* @param string $materials_file An uri to additional materials
* @param string $materials_name An uri name to additional materials
* @access public
* @see $materials
*/
  function addMaterials($materials_file, $materials_name="") {
  	if(empty($materials_name)) {
    	$materials_name = $materials_file;
    }
    if ((!empty($materials_name))&&(!$this->keyInArray($materials_name, $this->materials))) {
      $this->materials[$materials_name] = $materials_file;
    }

  }

/**
* returns TRUE if the key occurs in an array
*
* returns TRUE if the key occurs in an array
*
* @param string $arraykey A key to an element in array
* @param array $array An array to be searched
* @access private
* @see $materials
*/
  function keyInArray($searchkey, $array) {
	  if ($searchKey) {
		   foreach ($array as $key => $value) {
			   if (strcmp($key, $searchkey)==0) {
				   return true;
			   }
		   }
	   }
	   return false;
  }

/**
* Sets and uploads the materials uri
*
* Sets and uploads the materials uri
*
* @param string $materials_filename, string $materials_tempfilename, string $materials
* @access public
* @see $materials
*/  function setMaterialsfile($materials_filename, $materials_tempfilename="", $materials_name="") {
		if (!empty($materials_filename)) {
			$materialspath = $this->getMaterialsPath();
			if (!file_exists($materialspath)) {
				ilUtil::makeDirParents($materialspath);
			}
			if (!move_uploaded_file($materials_tempfilename, $materialspath . $materials_filename)) {
				print "image not uploaded!!!! ";
			} else {
				$this->addMaterials($materials_filename, $materials_name);
			}
		}
	}

/**
* Deletes a materials uri
*
* Deletes a materials uri with a given name.
*
* @param string $index A materials_name of the materials uri
* @access public
* @see $materials
*/
  function deleteMaterial($materials_name = "") {
	foreach ($this->materials as $key => $value) {
		if (strcmp($key, $materials_name)==0) {
			if (file_exists($this->getMaterialsPath().$value)) {
				unlink($this->getMaterialsPath().$value);
			}
			unset($this->materials[$key]);
		}
	}
  }

/**
* Deletes all materials uris
*
* Deletes all materials uris
*
* @access public
* @see $materials
*/
  function flushMaterials() {
    $this->materials = array();
  }

/**
* Sets the authors name
*
* Sets the authors name of the SurveyQuestion object
*
* @param string $author A string containing the name of the questions author
* @access public
* @see $author
*/
  function setAuthor($author = "") {
    if (!$author) {
      $author = $this->ilias->account->fullname;
    }
    $this->author = $author;
  }

/**
* Sets the questiontext
*
* Sets the questiontext of the SurveyQuestion object
*
* @param string $questiontext A string containing the questiontext
* @access public
* @see $questiontext
*/
  function setQuestiontext($questiontext = "") {
    $this->questiontext = $questiontext;
  }

/**
* Sets the creator/owner
*
* Sets the creator/owner ID of the SurveyQuestion object
*
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
* @see $owner
*/
  function setOwner($owner = "") {
    $this->owner = $owner;
  }

/**
* Gets the title string
*
* Gets the title string of the SurveyQuestion object
*
* @return string The title string to describe the question
* @access public
* @see $title
*/
  function getTitle() {
    return $this->title;
  }

/**
* Gets the id
*
* Gets the id of the SurveyQuestion object
*
* @return integer The id of the SurveyQuestion object
* @access public
* @see $id
*/
  function getId() {
    return $this->id;
  }

/**
* Gets the obligatory state of the question
*
* Gets the obligatory state of the question
*
* @return boolean True, if the question is obligatory, otherwise false
* @access public
* @see $obligatory
*/
  function getObligatory() {
    return $this->obligatory;
  }

/**
* Gets the survey id
*
* Gets the survey id of the SurveyQuestion object
*
* @return integer The survey id of the SurveyQuestion object
* @access public
* @see $survey_id
*/
  function getSurveyId() {
    return $this->survey_id;
  }

/**
* Gets the description
*
* Gets the description string of the SurveyQuestion object
*
* @return string The description string to describe the question
* @access public
* @see $description
*/
  function getDescription() {
    return $this->description;
  }

/**
* Gets the authors name
*
* Gets the authors name of the SurveyQuestion object
*
* @return string The string containing the name of the questions author
* @access public
* @see $author
*/
  function getAuthor() {
    return $this->author;
  }

/**
* Gets the creator/owner
*
* Gets the creator/owner ID of the SurveyQuestion object
*
* @return integer The numerical ID to identify the owner/creator
* @access public
* @see $owner
*/
  function getOwner() {
    return $this->owner;
  }

/**
* Gets the questiontext
*
* Gets the questiontext of the SurveyQuestion object
*
* @return string The questiontext of the question object
* @access public
* @see $questiontext
*/
  function getQuestiontext() {
    return $this->questiontext;
  }

/**
* Get the reference id of the container object
*
* Get the reference id of the container object
*
* @return integer The reference id of the container object
* @access public
* @see $obj_id
*/
  function getObjId() {
    return $this->obj_id;
  }

/**
* Set the reference id of the container object
*
* Set the reference id of the container object
*
* @param integer $obj_id The reference id of the container object
* @access public
* @see $obj_id
*/
  function setObjId($obj_id = 0) {
    $this->obj_id = $obj_id;
  }

/**
* Insert the question into a survey
*
* Insert the question into a survey
*
* @param integer $survey_id The database id of the survey
* @access private
*/
  function insertIntoSurvey($survey_id) {
    // get maximum sequence index in survey
/*    $query = sprintf("SELECT MAX(sequence) AS seq FROM dum_survey_question WHERE survey_fi=%s",
      $this->ilias->db->quote($survey_id)
    );
    $result = $this->ilias->db->db->query($query);
    $sequence = 1;
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      $sequence = $data->seq + 1;
    }
    $query = sprintf("INSERT INTO dum_survey_question (survey_question_id, survey_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
      $this->ilias->db->quote($survey_id),
      $this->ilias->db->quote($this->get_id()),
      $this->ilias->db->quote($sequence)
    );
    $result = $this->ilias->db->db->query($query);
    if ($result != DB_OK) {
      // Fehlermeldung
    }
*/  }

/**
* Saves a SurveyQuestion object to a database
*
* Saves a SurveyQuestion object to a database (only method body)
*
* @access public
*/
  function saveToDb($original_id = "") {
    // Method body
  }

/**
* Duplicates a survey question
*
* Duplicates a survey question
*
* @access public
*/
	function duplicate($for_survey = true, $title = "", $author = "", $owner = "")
	{
		if ($this->getId() <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		$original_id = $this->getId();
		$clone->setId(-1);
		if ($title)
		{
			$clone->setTitle($title);
		}
		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}
		if ($for_survey)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}
		// duplicate the materials
		$clone->duplicateMaterials($original_id);
		return $clone->getId();
	}

/**
* Duplicates the materials of a question
*
* Duplicates the materials of a question
*
* @param integer $question_id The database id of the original survey question
* @access public
*/
	function duplicateMaterials($question_id)
	{
		foreach ($this->materials as $filename)
		{
			$materialspath = $this->getMaterialsPath();
			$materialspath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $materialspath);
			if (!file_exists($materialspath)) {
				ilUtil::makeDirParents($materialspath);
			}
			if (!copy($materialspath_original . $filename, $materialspath . $filename)) {
				print "material could not be duplicated!!!! ";
			}
		}
	}


/**
* Loads a SurveyQuestion object from the database
*
* Loads a SurveyQuestion object from the database (only method body)
*
* @param integer $id The database id of the survey question
* @access public
*/
  function loadFromDb($id) {
    // Method body
  }

/**
* Saves the learners input of the question to the database
*
* Saves the learners input of the question to the database
*
* @access public
* @see $answers
*/
  function saveWorkingData($limit_to = LIMIT_NO_LIMIT) 
	{
  }

/**
* Returns the image path for web accessable images of a question
*
* Returns the image path for web accessable images of a question.
* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
*
* @access public
*/
	function getImagePath() {
		return CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/images/";
	}

/**
* Returns the materials path for web accessable material of a question
*
* Returns the materials path for web accessable materials of a question.
* The materials path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/materials
*
* @access public
*/
	function getMaterialsPath() {
		return CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/materials/";
	}

/**
* Returns the web image path for web accessable images of a question
*
* Returns the web image path for web accessable images of a question.
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
*
* @access public
*/
	function getImagePathWeb() {
		$webdir = CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/images/";
		return str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $webdir);
	}

/**
* Returns the web image path for web accessable images of a question
*
* Returns the web image path for web accessable images of a question.
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
*
* @access public
*/
	function getMaterialsPathWeb() {
		$webdir = CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/materials/";
		return str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $webdir);
	}

/**
* Saves a materials to a database
*
* Saves a materials to a database
*
* @param object $db A pear DB object
* @access public
*/
  function saveMaterialsToDb()
  {
		if ($this->id > 0) 
		{
			$query = sprintf("DELETE FROM survey_question_material WHERE question_fi = %s",
				$this->ilias->db->quote($this->id)
			);
			$result = $this->ilias->db->query($query);
			if (!empty($this->materials)) {
				foreach ($this->materials as $key => $value) {
					$query = sprintf("INSERT INTO survey_question_material (question_fi, materials, materials_file) VALUES (%s, %s, %s)",
						$this->ilias->db->quote($this->id),
						$this->ilias->db->quote($key),
						$this->ilias->db->quote($value)
					);
					$result = $this->ilias->db->query($query);
				}
			}
		}
	}

/**
* Loads materials uris from a database
*
* Loads materials uris from a database
*
* @param integer $question_id A unique key which defines the survey question in the database
* @access public
*/
  function loadMaterialFromDb($question_id)
  {
    $query = sprintf("SELECT * FROM survey_question_material WHERE question_fi = %s",
      $this->ilias->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
    	$this->materials = array();
    	while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
				$this->addMaterials($data->materials_file, $data->materials);
			}
		}
	}


/**
* Checks whether the question is in use or not
*
* Checks whether the question is in use or not
*
* @return boolean The number of datasets which are affected by the use of the query.
* @access public
*/
	function isInUse() {
/*		$query = sprintf("SELECT COUNT(solution_id) AS solution_count FROM tst_solutions WHERE question_fi = %s",
			$this->ilias->db->quote("$this->id")
		);
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->solution_count;
*/	}
	
/**
* Removes all references to the question in executed surveys in case the question has been changed
*
* Removes all references to the question in executed surveys in case the question has been changed.
* If a question was changed it cannot be guaranteed that the content and the meaning of the question
* is the same as before. So we have to delete all already started or completed surveys using that question.
* Therefore we have to delete all references to that question in tst_solutions and the tst_active
* entries which were created for the user and survey in the tst_solutions entry.
*
* @access public
*/
	function removeAllQuestionReferences() {
/*		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi = %s", $this->ilias->db->quote("$this->id"));
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			// Mark all surveys containing this question as "not started"
			$querychange = sprintf("DELETE FROM tst_active WHERE user_fi = %s AND survey_fi = %s",
				$this->ilias->db->quote("$result->user_fi"),
				$this->ilias->db->quote("$result->survey_fi")
			);
			$changeresult = $this->ilias->db->query($querychange);
		}
		// delete all resultsets for this question
		$querydelete = sprintf("DELETE FROM tst_solutions WHERE question_fi = %s", $this->ilias->db->quote("$this->id"));
		$deleteresult = $this->ilias->db->query($querydelete);
	}
*/}

/**
* Saves a category to the database
*
* Saves a category to the database
*
* @param string $categorytext The description of the category
* @result integer The database id of the category
* @access public
* @see $categories
*/
	function saveCategoryToDb($categorytext)
	{
		global $ilUser;
		
		$query = sprintf("SELECT category_id FROM survey_category WHERE title = %s AND owner_fi = %s",
			$this->ilias->db->quote($categorytext),
			$this->ilias->db->quote($ilUser->id)
		);
    $result = $this->ilias->db->query($query);
		if ($result->numRows()) {
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
			return $row->category_id;
		} else {
			$query = sprintf("INSERT INTO survey_category (category_id, title, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
				$this->ilias->db->quote($categorytext),
				$this->ilias->db->quote($ilUser->id)
			);
			$result = $this->ilias->db->query($query);
			return $this->ilias->db->getLastInsertId();
		}
	}

/**
* Deletes a question from the database
* 
* Deletes a question and all materials from the database
*
* @param integer $question_id The database id of the question
* @access private
*/
  function delete($question_id) 
  {
    if ($question_id < 1)
      return;
      
		$query = sprintf("SELECT obj_fi FROM survey_question WHERE question_id = %s",
			$this->ilias->db->quote($question_id)
		);
    $result = $this->ilias->db->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$obj_id = $row["obj_fi"];
		}
		else
		{
			return;
		}
		
		$query = sprintf("DELETE FROM survey_answer WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("SELECT constraint_id FROM survey_constraint WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = sprintf("DELETE FROM survey_question_constraint WHERE constraint_fi = %s",
				$this->ilias->db->quote($row->constraint_id)
			);
			$delresult = $this->ilias->db->query($query);
		}
		
		$query = sprintf("DELETE FROM survey_constraint WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("SELECT constraint_fi FROM survey_question_constraint WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = sprintf("DELETE FROM survey_constraint WHERE constraint_id = %s",
				$this->ilias->db->quote($row->constraint_fi)
			);
			$delresult = $this->ilias->db->query($query);
		}
		$query = sprintf("DELETE FROM survey_question_constraint WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_question_material WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_questionblock_question WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_survey_question WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_question WHERE question_id = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);

		$directory = CLIENT_WEB_DIR . "/survey/" . $obj_id . "/$question_id";
		if (is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
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
  function _getQuestionType($question_id) {
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT type_tag FROM survey_question, survey_questiontype WHERE survey_question.question_id = %s AND survey_question.questiontype_fi = survey_questiontype.questiontype_id",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      return $data->type_tag;
    } else {
      return "";
    }
  }

}
?>
