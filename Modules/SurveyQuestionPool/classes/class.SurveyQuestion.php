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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Basic class for all survey question types
*
* The SurveyQuestion class defines and encapsulates basic methods and attributes
* for survey question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyQuestion
{
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
	* The orientation of the question output
	*
	* The orientation of the question output (0 = vertical, 1 = horizontal)
	*
	* @var integer
	*/
	var $orientation;
	
	var $material;

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
		$this->orientation = 0;
		$this->materials = array();
		$this->material = array();
		register_shutdown_function(array(&$this, '_SurveyQuestion'));
	}

	function _SurveyQuestion()
	{
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
  function questionTitleExists($title, $questionpool_object = "") 
	{
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
    if (strcmp(strtolower(get_class($result)), db_result) == 0) 
		{
      if ($result->numRows() == 1) 
			{
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
  function setTitle($title = "") 
	{
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
  function setObligatory($obligatory = 1) 
	{
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
* Sets the orientation of the question output
*
* Sets the orientation of the question output
*
* @param integer $orientation 0 = vertical, 1 = horizontal
* @access public
* @see $orientation
*/
  function setOrientation($orientation = 0) 
	{
		if (strlen($orientation) == 0)
		{
			$this->orientation = 0;
		}
		else
    {
			$this->orientation = $orientation;
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
  function setId($id = -1) 
	{
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
  function setSurveyId($id = -1) 
	{
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
  function setDescription($description = "") 
	{
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
  function addMaterials($materials_file, $materials_name="") 
	{
  	if(empty($materials_name)) 
		{
    	$materials_name = $materials_file;
    }
    if ((!empty($materials_name))&&(!$this->keyInArray($materials_name, $this->materials))) 
		{
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
  function keyInArray($searchkey, $array) 
	{
	  if ($searchKey) 
		{
		   foreach ($array as $key => $value) 
			 {
			   if (strcmp($key, $searchkey)==0) 
				 {
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
	*/
	function setMaterialsfile($materials_filename, $materials_tempfilename="", $materials_name="")
	{
		if (!empty($materials_filename))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$materialspath = $this->getMaterialsPath();
			if (!file_exists($materialspath))
			{
				ilUtil::makeDirParents($materialspath);
			}
			//if (!move_uploaded_file($materials_tempfilename, $materialspath . $materials_filename))
			if (ilUtil::moveUploadedFile($materials_tempfilename, $materials_filename,
				$materialspath.$materials_filename))
			{
				print "image not uploaded!!!! ";
			}
			else
			{
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
  function deleteMaterial($materials_name = "") 
	{
		foreach ($this->materials as $key => $value) 
		{
			if (strcmp($key, $materials_name)==0) 
			{
				if (file_exists($this->getMaterialsPath().$value)) 
				{
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
  function flushMaterials() 
	{
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
  function setAuthor($author = "") 
	{
    if (!$author) 
		{
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
  function setQuestiontext($questiontext = "") 
	{
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
  function setOwner($owner = "") 
	{
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
  function getTitle() 
	{
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
  function getId() 
	{
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
  
	function getObligatory($survey_id = "") 
	{
		if ($survey_id > 0)
		{
			global $ilDB;
			
			$query = sprintf("SELECT * FROM survey_question_obligatory WHERE survey_fi = %s AND question_fi = %s",
				$ilDB->quote($survey_id . ""),
				$ilDB->quote($this->getId() . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				return $row["obligatory"];
			}
			else
			{
				return $this->obligatory;
			}
		}
		else
		{
			return $this->obligatory;
		}
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
  function getSurveyId() 
	{
    return $this->survey_id;
  }

/**
* Gets the orientation of the question output
*
* Gets the orientation of the question output
*
* @return integer 0 = vertical, 1 = horizontal
* @access public
* @see $orientation
*/
  function getOrientation() 
	{
		switch ($this->orientation)
		{
			case 0:
			case 1:
			case 2:
				break;
			default:
				$this->orientation = 0;
				break;
		}
    return $this->orientation;
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
  function getDescription() 
	{
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
  function getAuthor() 
	{
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
  function getOwner() 
	{
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
  function getQuestiontext() 
	{
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
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		return $clone->getId();
	}

/**
* Increases the media object usage counter when a question is duplicated
*
* Increases the media object usage counter when a question is duplicated
*
* @param integer $a_q_id The question id of the original question
* @access public
*/
	function copyXHTMLMediaObjectsOfQuestion($a_q_id)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $a_q_id);
		foreach ($mobs as $mob)
		{
			ilObjMediaObject::_saveUsage($mob, "spl:html", $this->getId());
		}
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
			if (!file_exists($materialspath)) 
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::makeDirParents($materialspath);
			}
			if (!copy($materialspath_original . $filename, $materialspath . $filename)) 
			{
				print "material could not be duplicated!!!! ";
			}
		}
	}


/**
* Loads a SurveyQuestion object from the database
*
* Loads a SurveyQuestion object from the database
*
* @param integer $question_id A unique key which defines the question in the database
* @access public
*/
	function loadFromDb($question_id)
	{
		$query = sprintf("SELECT * FROM survey_material WHERE question_fi = %s",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		$this->material = array();
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->material = array(
					"internal_link" => $row["internal_link"],
					"import_id" => $row["import_id"],
					"title" => $row["material_title"]
				);
			}
		}
	}

/**
* Checks wheather the question is complete or not
*
* Checks wheather the question is complete or not
*
* @return boolean TRUE if the question is complete, FALSE otherwise
* @access public
*/
	function _isComplete($question_id)
	{
		global $ilDB;

		$query = sprintf("SELECT complete FROM survey_question WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["complete"] == 1)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
/**
* Saves the complete flag to the database
*
* Saves the complete flag to the database
*
* @access public
*/
	function saveCompletionStatus($original_id = "")
	{
		$question_id = $this->getId();
		if (strlen($original_id))
		{
			$question_id = $original_id;
		}

		$complete = 0;
		if ($this->isComplete()) 
		{
			$complete = 1;
		}
    if ($this->id > 0) 
		{
      // update existing dataset
      $query = sprintf("UPDATE survey_question SET complete = %s WHERE question_id = %s",
				$this->ilias->db->quote("$complete"),
				$this->ilias->db->quote($question_id . "")
      );
      $result = $this->ilias->db->query($query);
    }
	}

	/**
	* Saves a SurveyQuestion object to a database
	*
	* Saves a SurveyQuestion object to a database
	*
	* @param integer $original_id
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM survey_material WHERE question_fi = %s",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		ilInternalLink::_deleteAllLinksOfSource("sqst", $this->getId());
		if (count($this->material))
		{
			$query = sprintf("INSERT INTO survey_material (material_id, question_fi, internal_link, import_id, material_title, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getId() . ""),
				$this->ilias->db->quote($this->material["internal_link"] . ""),
				$this->ilias->db->quote($this->material["import_id"] . ""),
				$this->ilias->db->quote($this->material["title"] . "")
			);
			$this->ilias->db->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("sqst", $this->getId(), $matches[2], $matches[3], $matches[1]);
			}
		}
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
	function getImagePath() 
	{
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
	function getMaterialsPath() 
	{
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
	function getImagePathWeb() 
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/survey/$this->obj_id/$this->id/images/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

/**
* Returns the web image path for web accessable images of a question
*
* Returns the web image path for web accessable images of a question.
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
*
* @access public
*/
	function getMaterialsPathWeb() 
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/survey/$this->obj_id/$this->id/materials/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
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
				foreach ($this->materials as $key => $value) 
				{
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
    if (strcmp(strtolower(get_class($result)), db_result) == 0) 
		{
    	$this->materials = array();
    	while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
			{
				$this->addMaterials($data->materials_file, $data->materials);
			}
		}
	}

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
	function saveCategoryToDb($categorytext, $neutral = 0)
	{
		global $ilUser;
		
		$query = sprintf("SELECT title, category_id FROM survey_category WHERE title = %s AND neutral = %s AND owner_fi = %s",
			$this->ilias->db->quote($categorytext . ""),
			$this->ilias->db->quote($neutral . ""),
			$this->ilias->db->quote($ilUser->getId() . "")
		);
    $result = $this->ilias->db->query($query);
		$insert = FALSE;
		$returnvalue = "";
		if ($result->numRows()) 
		{
			$insert = TRUE;
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if (strcmp($row->title, $categorytext) == 0)
				{
					$returnvalue = $row->category_id;
					$insert = FALSE;
				}
			}
		}
		else
		{
			$insert = TRUE;
		}
		if ($insert)
		{
			$query = sprintf("INSERT INTO survey_category (category_id, title, neutral, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($categorytext . ""),
				$this->ilias->db->quote($neutral . ""),
				$this->ilias->db->quote($ilUser->getId() . "")
			);
			$result = $this->ilias->db->query($query);
			$returnvalue = $this->ilias->db->getLastInsertId();
		}
		return $returnvalue;
	}

	/**
	* Deletes datasets from the additional question table in the database
	*
	* Deletes datasets from the additional question table in the database
	*
	* @param integer $question_id The question id which should be deleted in the additional question table
	* @access public
	*/
	function deleteAdditionalTableData($question_id)
	{
		global $ilDB;
		$additional_table_name = $this->getAdditionalTableName();
		$query = sprintf("DELETE FROM $additional_table_name WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
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

		$query = sprintf("DELETE survey_question_obligatory FROM WHERE question_fi = %s",
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

		$this->deleteAdditionalTableData($question_id);
		
		$query = sprintf("DELETE FROM survey_material WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		ilInternalLink::_deleteAllLinksOfSource("sqst", $question_id);

		$directory = CLIENT_WEB_DIR . "/survey/" . $obj_id . "/$question_id";
		if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $question_id);
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, "spl:html", $question_id);
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
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
  function _getQuestionType($question_id) 
	{
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT type_tag FROM survey_question, survey_questiontype WHERE survey_question.question_id = %s AND survey_question.questiontype_fi = survey_questiontype.questiontype_id",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) 
		{
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      return $data->type_tag;
    } 
		else 
		{
      return "";
    }
  }

/**
* Returns the question title of a question with a given id
* 
* Returns the question title of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question title
* @access private
*/
  function _getTitle($question_id) 
	{
		global $ilDB;

    if ($question_id < 1) return "";

    $query = sprintf("SELECT title FROM survey_question WHERE survey_question.question_id = %s",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) 
		{
      $data = $result->fetchRow(DB_FETCHMODE_ASSOC);
      return $data["title"];
    } 
		else 
		{
      return "";
    }
  }

/**
* Returns the original id of a question
*
* Returns the original id of a question
*
* @param integer $question_id The database id of the question
* @return integer The database id of the original question
* @access public
*/
	function _getOriginalId($question_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM survey_question WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["original_id"] > 0)
			{
				return $row["original_id"];
			}
			else
			{
				return $row["question_id"];
			}
		}
		else
		{
			return "";
		}
	}
	
	function _getRefIdFromObjId($obj_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT ref_id FROM object_reference WHERE obj_id=%s",
			$ilDB->quote($obj_id)
			
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["ref_id"];
		}
		return 0;
	}
	
	function syncWithOriginal()
	{
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM survey_material WHERE question_fi = %s",
			$this->ilias->db->quote($this->original_id . "")
		);
		$result = $this->ilias->db->query($query);
		ilInternalLink::_deleteAllLinksOfSource("sqst", $this->original_id);
		if (strlen($this->material["internal_link"]))
		{
			$query = sprintf("INSERT INTO survey_material (material_id, question_fi, internal_link, import_id, material_title, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->original_id . ""),
				$this->ilias->db->quote($this->material["internal_link"] . ""),
				$this->ilias->db->quote($this->material["import_id"] . ""),
				$this->ilias->db->quote($this->material["title"] . "")
			);
			$this->ilias->db->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("sqst", $this->original_id, $matches[2], $matches[3], $matches[1]);
			}
		}
	}

/**
* Returns a phrase for a given database id
*
* Returns a phrase for a given database id
*
* @result String The title of the phrase
* @access public
*/
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
* Returns true if the phrase title already exists for the current user
*
* Returns true if the phrase title already exists for the current user
*
* @param string $title The title of the phrase
* @result boolean True, if the title exists, otherwise False
* @access public
*/
	function phraseExists($title)
	{
		global $ilUser;
		
		$query = sprintf("SELECT phrase_id FROM survey_phrase WHERE title = %s AND owner_fi = %s",
			$this->ilias->db->quote($title),
			$this->ilias->db->quote($ilUser->id)
		);
    $result = $this->ilias->db->query($query);
		if ($result->numRows() == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

/**
* Returns true if the question already exists in the database
*
* Returns true if the question already exists in the database
*
* @param integer $question_id The database id of the question
* @result boolean True, if the question exists, otherwise False
* @access public
*/
	function _questionExists($question_id)
	{
		global $ilDB;

		if ($question_id < 1)
		{
			return false;
		}
		
		$query = sprintf("SELECT question_id FROM survey_question WHERE question_id = %s",
			$ilDB->quote($question_id)
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

/**
* Sets a material link for the question
*
* Sets a material link for the question
*
* @param string $material_id An internal link pointing to the material
* @param boolean $is_import A boolean indication that the internal link was imported from another ILIAS installation
* @access public
*/
	function setMaterial($material_id = "", $is_import = false, $material_title = "")
	{
		if (strcmp($material_id, "") != 0)
		{
			$import_id = "";
			if ($is_import)
			{
				$import_id = $material_id;
				$material_id = $this->_resolveInternalLink($import_id);
			}
			if (strcmp($material_title, "") == 0)
			{
				if (preg_match("/il__(\w+)_(\d+)/", $material_id, $matches))
				{
					$type = $matches[1];
					$target_id = $matches[2];
					$material_title = $this->lng->txt("obj_$type") . ": ";
					switch ($type)
					{
						case "lm":
							include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
							$cont_obj =& new ilObjContentObject($target_id, true);
							$material_title .= $cont_obj->getTitle();
							break;
						case "pg":
							include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
							include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
							include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
							$lm_id = ilLMObject::_lookupContObjID($target_id);
							$cont_obj =& new ilObjContentObject($lm_id, false);
							$pg_obj =& new ilLMPageObject($cont_obj, $target_id);
							$material_title .= $pg_obj->getTitle();
							break;
						case "st":
							include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
							include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
							include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
							$lm_id = ilLMObject::_lookupContObjID($target_id);
							$cont_obj =& new ilObjContentObject($lm_id, false);
							$st_obj =& new ilStructureObject($cont_obj, $target_id);
							$material_title .= $st_obj->getTitle();
							break;
						case "git":
							include_once "./Modules/Glossary/classes/class.ilGlossaryTerm.php";
							$material_title = $this->lng->txt("glossary_term") . ": " . ilGlossaryTerm::_lookGlossaryTerm($target_id);
							break;
						case "mob":
							break;
					}
				}
			}
			$this->material = array(
				"internal_link" => $material_id,
				"import_id" => $import_id,
				"title" => $material_title
			);
		}
	}
	
	function _resolveInternalLink($internal_link)
	{
		if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches))
		{
			include_once "./Services/COPage/classes/class.ilInternalLink.php";
			include_once "./Modules/LearningModule/classes/class.ilLMObject.php";
			include_once "./Modules/Glossary/classes/class.ilGlossaryTerm.php";
			switch ($matches[2])
			{
				case "lm":
					$resolved_link = ilLMObject::_getIdForImportId($internal_link);
					break;
				case "pg":
					$resolved_link = ilInternalLink::_getIdForImportId("PageObject", $internal_link);
					break;
				case "st":
					$resolved_link = ilInternalLink::_getIdForImportId("StructureObject", $internal_link);
					break;
				case "git":
					$resolved_link = ilInternalLink::_getIdForImportId("GlossaryItem", $internal_link);
					break;
				case "mob":
					$resolved_link = ilInternalLink::_getIdForImportId("MediaObject", $internal_link);
					break;
			}
			if (strcmp($resolved_link, "") == 0)
			{
				$resolved_link = $internal_link;
			}
		}
		else
		{
			$resolved_link = $internal_link;
		}
		return $resolved_link;
	}
	
	function _resolveIntLinks($question_id)
	{
		global $ilDB;
		$resolvedlinks = 0;
		$query = sprintf("SELECT * FROM survey_material WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$internal_link = $row["internal_link"];
				include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
				$resolved_link = SurveyQuestion::_resolveInternalLink($internal_link);
				if (strcmp($internal_link, $resolved_link) != 0)
				{
					// internal link was resolved successfully
					$queryupdate = sprintf("UPDATE survey_material SET internal_link = %s WHERE material_id = %s",
						$ilDB->quote($resolved_link),
						$ilDB->quote($row["material_id"] . "")
					);
					$updateresult = $ilDB->query($queryupdate);
					$resolvedlinks++;
				}
			}
		}
		if ($resolvedlinks)
		{
			// there are resolved links -> reenter theses links to the database

			// delete all internal links from the database
			include_once "./Services/COPage/classes/class.ilInternalLink.php";
			ilInternalLink::_deleteAllLinksOfSource("sqst", $question_id);

			$query = sprintf("SELECT * FROM survey_material WHERE question_fi = %s",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches))
					{
						ilInternalLink::_saveLink("sqst", $question_id, $matches[2], $matches[3], $matches[1]);
					}
				}
			}
		}
	}
	
	function _getInternalLinkHref($target = "")
	{
		global $ilDB;
		$linktypes = array(
			"lm" => "LearningModule",
			"pg" => "PageObject",
			"st" => "StructureObject",
			"git" => "GlossaryItem",
			"mob" => "MediaObject"
		);
		$href = "";
		if (preg_match("/il__(\w+)_(\d+)/", $target, $matches))
		{
			$type = $matches[1];
			$target_id = $matches[2];
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			switch($linktypes[$matches[1]])
			{
				case "LearningModule":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "PageObject":
				case "StructureObject":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "GlossaryItem":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "MediaObject":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type] . "&cmd=media&ref_id=".$_GET["ref_id"]."&mob_id=".$target_id;
					break;
			}
		}
		return $href;
	}
	
/**
* Returns true if the question is writeable by a certain user
*
* Returns true if the question is writeable by a certain user
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user
* @result boolean True, if the question exists, otherwise False
* @access public
*/
	function _isWriteable($question_id, $user_id)
	{
		global $ilDB;

		if (($question_id < 1) || ($user_id < 1))
		{
			return false;
		}
		
		$query = sprintf("SELECT obj_fi FROM survey_question WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$qpl_object_id = $row["obj_fi"];
			include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
			return ilObjSurveyQuestionPool::_isWriteable($qpl_object_id, $user_id);
		}
		else
		{
			return false;
		}
	}

	/**
	* Returns the question type ID of the question
	*
	* Returns the question type ID of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionTypeID()
	{
		global $ilDB;
		$query = sprintf("SELECT questiontype_id FROM survey_questiontype WHERE type_tag = %s",
			$ilDB->quote($this->getQuestionType())
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["questiontype_id"];
		}
		else
		{
			return 0;
		}
	}

	/**
	* Returns the question type of the question
	*
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "";
	}
	
/**
* Creates an instance of a question with a given question id
*
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id) 
	{
		$question_type = SurveyQuestion::_getQuestionType($question_id);
		include_once "./Modules/SurveyQuestionPool/classes/class.$question_type.php";
		$question = new $question_type();
		$question->loadFromDb($question_id);
		return $question;
  }
	
	/**
	* Checks if a given string contains HTML or not
	*
	* @param string $a_text Text which should be checked
	
	* @return boolean 
	* @access public
	*/
	function isHTML($a_text)
	{
		if (preg_match("/<[^>]*?>/", $a_text))
		{
			return TRUE;
		}
		else
		{
			return FALSE; 
		}
	}
	
	/**
	* Reads an QTI material tag an creates a text string
	*
	* @param string $a_material QTI material tag
	* @return string text or xhtml string
	* @access public
	*/
	function QTIMaterialToString($a_material)
	{
		$result = "";
		for ($i = 0; $i < $a_material->getMaterialCount(); $i++)
		{
			$material = $a_material->getMaterial($i);
			if (strcmp($material["type"], "mattext") == 0)
			{
				$result .= $material["material"]->getContent();
			}
			if (strcmp($material["type"], "matimage") == 0)
			{
				$matimage = $material["material"];
				if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches))
				{
					// import an mediaobject which was inserted using tiny mce
					if (!is_array($_SESSION["import_mob_xhtml"])) $_SESSION["import_mob_xhtml"] = array();
					array_push($_SESSION["import_mob_xhtml"], array("mob" => $matimage->getLabel(), "uri" => $matimage->getUri()));
				}
			}
		}
		return $result;
	}
	
	/**
	* Creates an XML material tag from a plain text or xhtml text
	*
	* @param object $a_xml_writer Reference to the ILIAS XML writer
	* @param string $a_material plain text or html text containing the material
	* @return string XML material tag
	* @access public
	*/
	function addMaterialTag(&$a_xml_writer, $a_material, $close_material_tag = TRUE, $add_mobs = TRUE)
	{
		include_once "./Services/RTE/classes/class.ilRTE.php";
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"type" => "text/plain"
		);
		if ($this->isHTML($a_material))
		{
			$attrs["type"] = "text/xhtml";
		}
		$a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));

		if ($add_mobs)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $this->getId());
			foreach ($mobs as $mob)
			{
				$mob_obj =& new ilObjMediaObject($mob);
				$imgattrs = array(
					"label" => "il_" . IL_INST_ID . "_mob_" . $mob,
					"uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
				);
				$a_xml_writer->xmlElement("matimage", $imgattrs, NULL);
			}
		}		
		if ($close_material_tag) $a_xml_writer->xmlEndTag("material");
	}

	/**
	* Prepares a string for a text area output in surveys
	*
	* @param string $txt_output String which should be prepared for output
	* @access public
	*/
	function prepareTextareaOutput($txt_output, $prepare_for_latex_output = FALSE)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		return ilUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output);
	}

	/**
	* Returns the question data fields from the database
	*
	* Returns the question data fields from the database
	*
	* @param integer $id The question ID from the database
	* @return array Array containing the question fields and data from the database
	* @access public
	*/
	function _getQuestionDataArray($id)
	{
		return array();
	}

	
	/**
	* Adds the entries for the title row of the user specific results
	*
	* Adds the entries for the title row of the user specific results
	*
	* @param array $a_array An array which is used to append the title row entries
	* @access public
	*/
	function addUserSpecificResultsExportTitles(&$a_array)
	{
		array_push($a_array, $this->getTitle());
	}

	/**
	* Adds the values for the user specific results export for a given user
	*
	* Adds the values for the user specific results export for a given user
	*
	* @param array $a_array An array which is used to append the values
	* @param array $resultset The evaluation data for a given user
	* @access public
	*/
	function addUserSpecificResultsData(&$a_array, &$resultset)
	{
		// overwrite in inherited classes
	}

	/**
	* Returns an array containing all answers to this question in a given survey
	*
	* Returns an array containing all answers to this question in a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return array An array containing the answers to the question. The keys are either the user id or the anonymous id
	* @access public
	*/
	function &getUserAnswers($survey_id)
	{
		// overwrite in inherited classes
		return array();
	}

	/**
	* Creates the user data of the survey_answer table from the POST data
	*
	* Creates the user data of the survey_answer table from the POST data
	*
	* @return array User data according to the survey_answer table
	* @access public
	*/
	function &getWorkingDataFromUserInput($post_data)
	{
		// overwrite in inherited classes
		$data = array();
		return $data;
	}
	
	/**
	* Import additional meta data from the question import file
	*
	* Import additional meta data from the question import file. Usually
	* the meta data section is used to store question elements which are not
	* part of the standard XML schema.
	*
	* @return array $a_meta Array containing the additional meta data
	* @access public
	*/
	function importAdditionalMetadata($a_meta)
	{
		// overwrite in inherited classes
	}
	
	/**
	* Import response data from the question import file
	*
	* Import response data from the question import file
	*
	* @return array $a_data Array containing the response data
	* @access public
	*/
	function importResponses($a_data)
	{
		// overwrite in inherited classes
	}

	/**
	* Import bipolar adjectives from the question import file
	*
	* Import bipolar adjectives from the question import file
	*
	* @return array $a_data Array containing the adjectives
	* @access public
	*/
	function importAdjectives($a_data)
	{
		// overwrite in inherited classes
	}

	/**
	* Import matrix rows from the question import file
	*
	* Import matrix rows from the question import file
	*
	* @return array $a_data Array containing the matrix rows
	* @access public
	*/
	function importMatrix($a_data)
	{
		// overwrite in inherited classes
	}

	/**
	* Creates the Excel output for the cumulated results of this question
	*
	* Creates the Excel output for the cumulated results of this question
	*
	* @param object $worksheet Reference to the excel worksheet
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @param integer $row Actual row in the worksheet
	* @return integer The next row which should be used for the export
	* @access public
	*/
	function setExportCumulatedXLS(&$worksheet, &$format_title, &$format_bold, &$eval_data, $row)
	{
		include_once ("./classes/class.ilExcelUtils.php");
		$worksheet->writeString($row, 0, ilExcelUtils::_convert_text($this->getTitle()));
		$worksheet->writeString($row, 1, ilExcelUtils::_convert_text($this->getQuestiontext()));
		$worksheet->writeString($row, 2, ilExcelUtils::_convert_text($this->lng->txt($eval_data["QUESTION_TYPE"])));
		$worksheet->write($row, 3, $eval_data["USERS_ANSWERED"]);
		$worksheet->write($row, 4, $eval_data["USERS_SKIPPED"]);
		$worksheet->write($row, 5, ilExcelUtils::_convert_text($eval_data["MODE_VALUE"]));
		$worksheet->write($row, 6, ilExcelUtils::_convert_text($eval_data["MODE"]));
		$worksheet->write($row, 7, $eval_data["MODE_NR_OF_SELECTIONS"]);
		$worksheet->write($row, 8, ilExcelUtils::_convert_text(str_replace("<br />", " ", $eval_data["MEDIAN"])));
		$worksheet->write($row, 9, $eval_data["ARITHMETIC_MEAN"]);
		return $row + 1;
	}
	
	/**
	* Creates the CSV output for the cumulated results of this question
	*
	* Creates the CSV output for the cumulated results of this question
	*
	* @param object $worksheet Reference to the excel worksheet
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @param integer $row Actual row in the worksheet
	* @return integer The next row which should be used for the export
	* @access public
	*/
	function &setExportCumulatedCVS(&$eval_data)
	{
		$csvrow = array();
		array_push($csvrow, $this->getTitle());
		array_push($csvrow, $this->getQuestiontext());
		array_push($csvrow, $this->lng->txt($eval_data["QUESTION_TYPE"]));
		array_push($csvrow, $eval_data["USERS_ANSWERED"]);
		array_push($csvrow, $eval_data["USERS_SKIPPED"]);
		array_push($csvrow, $eval_data["MODE"]);
		array_push($csvrow, $eval_data["MODE_NR_OF_SELECTIONS"]);
		array_push($csvrow, $eval_data["MEDIAN"]);
		array_push($csvrow, $eval_data["ARITHMETIC_MEAN"]);
		$result = array();
		array_push($result, $csvrow);
		return $result;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param object $workbook Reference to the parent excel workbook
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @access public
	*/
	function setExportDetailsXLS(&$workbook, &$format_title, &$format_bold, &$eval_data)
	{
		// overwrite in inherited classes
	}
	
	/**
	* Returns if the question is usable for preconditions
	*
	* Returns if the question is usable for preconditions
	*
	* @return boolean TRUE if the question is usable for a precondition, FALSE otherwise
	* @access public
	*/
	function usableForPrecondition()
	{
		// overwrite in inherited classes
		return FALSE;
	}

	/**
	* Returns the available relations for the question
	*
	* Returns the available relations for the question
	*
	* @return array An array containing the available relations
	* @access public
	*/
	function getAvailableRelations()
	{
		// overwrite in inherited classes
		return array();
	}

	/**
	* Creates a value selection for preconditions
	*
	* Creates a value selection for preconditions
	*
	* @return The HTML code for the precondition value selection
	* @access public
	*/
	function getPreconditionSelectValue()
	{
		// overwrite in inherited classes
	}
	
	/**
	* Returns the output for a precondition value
	*
	* Returns the output for a precondition value
	*
	* @param string $value The precondition value
	* @return string The output of the precondition value
	* @access public
	*/
	function getPreconditionValueOutput($value)
	{
		// overwrite in inherited classes
		return "";
	}

/**
* Creates an image visualising the results of the question
*
* Creates an image visualising the results of the question
*
* @param integer $survey_id The database ID of the survey
* @param string $type An additional parameter to allow to draw more than one chart per question. Must be interpreted by the question. Default is an empty string
* @return binary Image with the visualisation
* @access private
*/
	function outChart($survey_id, $type = "")
	{
		// overwrite in inherited classes
	}
	
}
?>
