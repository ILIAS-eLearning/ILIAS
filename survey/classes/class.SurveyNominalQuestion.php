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

require_once "./survey/classes/class.SurveyQuestion.php";

define("SUBTYPE_MCSR", 1);
define("SUBTYPE_MCMR", 2);

/**
* Nominal survey question
*
* The SurveyNominalQuestion class defines and encapsulates basic methods and attributes
* for nominal survey question types.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyNominalQuestion.php
* @modulegroup   Survey
*/
class SurveyNominalQuestion extends SurveyQuestion {
/**
* Question subtype
*
* A question subtype (Multiple choice single response or multiple choice multiple response)
*
* @var integer
*/
  var $subtype;

/**
* Categories contained in this question
*
* Categories contained in this question
*
* @var array
*/
  var $categories;

/**
* SurveyNominalQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyNominalQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyNominalQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1,
		$subtype = 0
  )

  {
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->subtype = $subtype;
		$this->categories = array();
	}
	
/**
* Sets the question subtype
*
* Sets the question subtype
*
* @param integer $subtype The question subtype
* @access public
* @see $subtype
*/
  function setSubtype($subtype = SUBTYPE_MCSR) 
	{
    $this->subtype = $subtype;
  }

/**
* Gets the question subtype
*
* Gets the question subtype
*
* @return integer The question subtype
* @access public
* @see $subtype
*/
  function getSubtype() 
	{
    return $this->subtype;
  }
	
/**
* Returns the number of categories contained in that question
*
* Returns the number of categories contained in that question
*
* @return integer The number of contained categories
* @access public
* @see $categories
*/
	function getCategoryCount() 
	{
		return count($this->categories);
	}
	
/**
* Adds a category to the question at a given index
*
* Adds a category to the question at a given index
*
* @param string $categoryname The name of the category
* @param integer $index The index of the category
* @access public
* @see $categories
*/
	function addCategoryWithIndex($categoryname, $index) 
	{
		$this->categories[$index] = $categoryname;
	}

/**
* Adds a category to the question
*
* Adds a category to the question
*
* @param integer $categoryname The name of the category
* @access public
* @see $categories
*/
	function addCategory($categoryname) 
	{
		array_push($this->categories, $categoryname);
	}
	
/**
* Adds a category array to the question
*
* Adds a category array to the question
*
* @param array $categories An array with categories
* @access public
* @see $categories
*/
	function addCategoryArray($categories) 
	{
		$this->categories = array_merge($this->categories, $categories);
	}
	
/**
* Removes a category from the list of categories
*
* Removes a category from the list of categories
*
* @param integer $index The index of the category to be removed
* @access public
* @see $categories
*/
	function removeCategory($index)
	{
		array_splice($this->categories, $index, 1);
	}

/**
* Removes many categories from the list of categories
*
* Removes many categories from the list of categories
*
* @param array $array An array containing the index positions of the categories to be removed
* @access public
* @see $categories
*/
	function removeCategories($array)
	{
		foreach ($array as $index)
		{
			unset($this->categories[$index]);
		}
		$this->categories = array_values($this->categories);
	}

/**
* Removes a category from the list of categories
*
* Removes a category from the list of categories
*
* @param string $name The name of the category to be removed
* @access public
* @see $categories
*/
	function removeCategoryWithName($name)
	{
		$index = array_search($name, $this->categories);
		$this->removeCategory($index);
	}
	
/**
* Returns the name of a category for a given index
*
* Returns the name of a category for a given index
*
* @param integer $index The index of the category
* @result string Category name
* @access public
* @see $categories
*/
	function getCategory($index)
	{
		return $this->categories[$index];
	}
	
/**
* Empties the categories list
*
* Empties the categories list
*
* @access public
* @see $categories
*/
	function flushCategories() {
		$this->categories = array();
	}
	
/**
* Loads a SurveyNominalQuestion object from the database
*
* Loads a SurveyNominalQuestion object from the database
*
* @param integer $id The database id of the nominal survey question
* @access public
*/
  function loadFromDb($id) {
    $query = sprintf("SELECT * FROM survey_question WHERE question_id = %s",
      $this->ilias->db->quote($id)
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $data->question_id;
        $this->title = $data->title;
        $this->description = $data->description;
        $this->obj_id = $data->obj_fi;
        $this->author = $data->author;
				$this->subtype = $data->subtype;
				$this->obligatory = $data->obligatory;
        $this->owner = $data->owner_fi;
        $this->questiontext = $data->questiontext;
        $this->complete = $data->complete;
      }
      // loads materials uris from database
      $this->loadMaterialFromDb($id);

			$this->flushCategories();
      $query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable, survey_category WHERE survey_variable.question_fi = %s AND survey_variable.category_fi = survey_category.category_id ORDER BY sequence ASC",
        $this->ilias->db->quote($id)
      );
      $result = $this->ilias->db->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) {
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          array_push($this->categories, $data->title);
        }
      }
    }
  }

/**
* Returns true if the question is complete for use
*
* Returns true if the question is complete for use
*
* @result boolean True if the question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if ($this->title and $this->author and $this->questiontext and count($this->categories))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Saves a SurveyNominalQuestion object to a database
*
* Saves a SurveyNominalQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "")
  {
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
		if ($original_id)
		{
			$original_id = $this->ilias->db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}
    if ($this->id == -1) {
      // Write new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO survey_question (question_id, subtype, questiontype_fi, obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote("$this->subtype"),
        $this->ilias->db->quote("1"),
        $this->ilias->db->quote($this->obj_id),
        $this->ilias->db->quote($this->owner),
        $this->ilias->db->quote($this->title),
        $this->ilias->db->quote($this->description),
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->questiontext),
				$this->ilias->db->quote(sprintf("%d", $this->obligatory)),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($created),
				$original_id
      );
      $result = $this->ilias->db->query($query);
      if ($result == DB_OK) {
        $this->id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // update existing dataset
      $query = sprintf("UPDATE survey_question SET title = %s, subtype = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
        $this->ilias->db->quote($this->title),
				$this->ilias->db->quote("$this->subtype"),
        $this->ilias->db->quote($this->description),
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->questiontext),
				$this->ilias->db->quote(sprintf("%d", $this->obligatory)),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($this->id)
      );
      $result = $this->ilias->db->query($query);
    }
    if ($result == DB_OK) {
      // saving material uris in the database
      $this->saveMaterialsToDb();

      // save categories
			
			// delete existing category relations
      $query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
        $this->ilias->db->quote($this->id)
      );
      $result = $this->ilias->db->query($query);
      // create new category relations
      foreach ($this->categories as $key => $value) {
				$category_id = $this->saveCategoryToDb($value);
        $query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
					$this->ilias->db->quote($category_id),
          $this->ilias->db->quote($this->id),
          $this->ilias->db->quote(($key + 1)),
          $this->ilias->db->quote($key)
        );
        $answer_result = $this->ilias->db->query($query);
      }
    }
  }

}
?>
