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

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Matrix question
*
* The SurveyMatrixQuestion class defines and encapsulates basic methods and attributes
* for matrix question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMatrixQuestion extends SurveyQuestion 
{
/**
* Columns contained in this question
*
* Columns contained in this question
*
* @var array
*/
  var $columns;

/**
* Neutral column
*
* Neutral column
*
* @var string
*/
	var $neutralColumn;
	
/**
* Rows contained in this question
*
* Rows contained in this question
*
* @var array
*/
  var $rows;

/**
* First bipolar adjective for ordinal matrix questions
*
* First bipolar adjective for ordinal matrix questions
*
* @var string
*/
	var $bipolar_adjective1;
	
/**
* Second bipolar adjective for ordinal matrix questions
*
* Second bipolar adjective for ordinal matrix questions
*
* @var string
*/
	var $bipolar_adjective2;
	
/**
* Enable state of separators for matrix columns
*
* 1 if separators are enabled for matrix columns, 0 otherwise
*
* @var integer
*/
	var $columnSeparators;
	
/**
* Enable state of separators for matrix rows
*
* 1 if separators are enabled for matrix rows, 0 otherwise
*
* @var integer
*/
	var $rowSeparators;

/**
* Enable state of a separator for the neutral column
*
* 1 if a separator is enabled for the neutral column, 0 otherwise
*
* @var integer
*/
	var $neutralColumnSeparator;
	
	
/**
* Matrix question subtype
*
* Matrix question subtype:
* 0 = Single choice
* 1 = Multiple choice
* 2 = Text
* 3 = Integer
* 4 = Double
* 5 = Date
* 6 = Time
*
* @var integer
*/
  var $subtype;

/**
* SurveyMatrixQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMatrixQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyMatrixQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1
  )

  {
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->subtype = 0;
		$this->columns = array();
		$this->rows = array();
		$this->neutralColumn = "";
		$this->bipolar_adjective1 = "";
		$this->bipolar_adjective2 = "";
		$this->rowSeparators = 0;
		$this->columnSeparators = 0;
		$this->neutralColumnSeparator = 1;
	}
	
/**
* Returns the text of the neutral column
*
* Returns the text of the neutral column
*
* @return string The text of the neutral column
* @access public
* @see $neutralColumn
*/
	function getNeutralColumn() 
	{
		return $this->neutralColumn;
	}
	
/**
* Sets the text of the neutral column
*
* Sets the text of the neutral column
*
* @param string $a_text The text of the neutral column
* @access public
* @see $neutralColumn
*/
	function setNeutralColumn($a_text) 
	{
		$this->neutralColumn = $a_text;
	}

/**
* Returns the number of columns
*
* Returns the number of columns
*
* @return integer The number of contained columns
* @access public
* @see $columns
*/
	function getColumnCount() 
	{
		return count($this->columns);
	}

/**
* Adds a column at a given position
*
* Adds a column at a given position
*
* @param string $columnname The name of the column
* @param integer $position The position of the column (starting with index 0)
* @access public
* @see $columns
*/
	function addColumnAtPosition($columnname, $position) 
	{
		if (array_key_exists($position, $this->columns))
		{
			$head = array_slice($this->columns, 0, $position);
			$tail = array_slice($this->columns, $position);
			$this->columns = array_merge($head, array($columnname), $tail);
		}
		else
		{
			array_push($this->columns, $columnname);
		}
	}

/**
* Adds a column
*
* Adds a column
*
* @param integer $columnname The name of the column
* @param integer $neutral Indicates if the column is a neutral column
* @access public
* @see $columns
*/
	function addColumn($columnname) 
	{
		array_push($this->columns, $columnname);
	}
	
/**
* Adds a column array
*
* Adds a column array
*
* @param array $columns An array with columns
* @access public
* @see $columns
*/
	function addColumnArray($columns) 
	{
		$this->columns = array_merge($this->columns, $columns);
	}
	
/**
* Removes a column from the list of columns
*
* Removes a column from the list of columns
*
* @param integer $index The index of the column to be removed
* @access public
* @see $columns
*/
	function removeColumn($index)
	{
		unset($this->columns[$index]);
		$this->columns = array_values($this->columns);
	}

/**
* Removes many columns from the list of columns
*
* Removes many columns from the list of columns
*
* @param array $array An array containing the index positions of the columns to be removed
* @access public
* @see $columns
*/
	function removeColumns($array)
	{
		foreach ($array as $index)
		{
			unset($this->columns[$index]);
		}
		$this->columns = array_values($this->columns);
	}

/**
* Removes a column from the list of columns
*
* Removes a column from the list of columns
*
* @param string $name The name of the column to be removed
* @access public
* @see $columns
*/
	function removeColumnWithName($name)
	{
		foreach ($this->columns as $index => $column)
		{
			if (strcmp($column, $name) == 0)
			{
				return $this->removeColumn($index);
			}
		}
	}
	
/**
* Returns the name of a column for a given index
*
* Returns the name of a column for a given index
*
* @param integer $index The index of the column
* @result array column
* @access public
* @see $columns
*/
	function getColumn($index)
	{
		if (array_key_exists($index, $this->columns))
		{
			return $this->columns[$index];
		}
		else
		{
			return "";
		}
	}

/**
* Returns the index of a column with a given name.
*
* Returns the index of a column with a given name.
*
* @param string $name The name of the column
* @access public
* @see $columns
*/
	function getColumnIndex($name)
	{
		foreach ($this->columns as $index => $column)
		{
			if (strcmp($column, $name) == 0)
			{
				return $this->removeColumn($index);
			}
		}
		return -1;
	}
	
	
/**
* Empties the columns list
*
* Empties the columns list
*
* @access public
* @see $columns
*/
	function flushColumns() 
	{
		$this->columns = array();
	}
	
/**
* Returns the number of rows in the question
*
* Returns the number of rows in the question
*
* @result integer The number of rows
* @access public
*/
	function getRowCount()
	{
		return count($this->rows);
	}

/**
* Adds a row to the question
*
* Adds a row to the question
*
* @param string $a_text The text of the row
* @access public
*/
	function addRow($a_text)
	{
		array_push($this->rows, $a_text);
	}
	
/**
* Empties the row list
*
* Empties the row list
*
* @access public
* @see $rows
*/
	function flushRows() 
	{
		$this->rows = array();
	}
	
/**
* Returns a specific row
*
* Returns a specific row
*
* @param integer $a_index The index position of the row
* @access public
*/
	function getRow($a_index)
	{
		if (array_key_exists($a_index, $this->rows))
		{
			return $this->rows[$a_index];
		}
		return "";
	}
	
/**
* Removes rows from the question
*
* Removes rows from the question
*
* @param array $array An array containing the index positions of the rows to be removed
* @access public
* @see $rows
*/
	function removeRows($array)
	{
		foreach ($array as $index)
		{
			unset($this->rows[$index]);
		}
		$this->rows = array_values($this->rows);
	}

/**
* Returns one of the bipolar adjectives
*
* Returns one of the bipolar adjectives
*
* @param integer $a_index The number of the bipoloar adjective (0 for the first and 1 for the second adjective)
* @result string The text of the bipolar adjective
* @access public
*/
	function getBipolarAdjective($a_index)
	{
		switch ($a_index)
		{
			case 1:
				return $this->bipolar_adjective2;
				break;
			case 0:
			default:
				return $this->bipolar_adjective1;
				break;
		}
	}

/**
* Sets one of the bipolar adjectives
*
* Sets one of the bipolar adjectives
*
* @param integer $a_index The number of the bipoloar adjective (0 for the first and 1 for the second adjective)
* @param string $a_value The text of the bipolar adjective
* @access public
*/
	function setBipolarAdjective($a_index, $a_value)
	{
		switch ($a_index)
		{
			case 1:
				$this->bipolar_adjective2 = $a_value;
				break;
			case 0:
			default:
				$this->bipolar_adjective1 = $a_value;
				break;
		}
	}
	
/**
* Gets the available phrases from the database
*
* Gets the available phrases from the database
*
* @param boolean $useronly Returns only the user defined phrases if set to true. The default is false.
* @result array All available phrases as key/value pairs
* @access public
*/
	function &getAvailablePhrases($useronly = 0)
	{
		global $ilUser;
		global $ilDB;
		
		$phrases = array();
    $query = sprintf("SELECT * FROM survey_phrase WHERE defaultvalue = '1' OR owner_fi = %s ORDER BY title",
      $ilDB->quote($ilUser->id)
    );
    $result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				if (!$useronly)
				{
					$phrases[$row->phrase_id] = array(
						"title" => $this->lng->txt($row->title),
						"owner" => $row->owner_fi
					);
				}
			}
			else
			{
				if ($ilUser->getId() == $row->owner_fi)
				{
					$phrases[$row->phrase_id] = array(
						"title" => $row->title,
						"owner" => $row->owner_fi
					);
				}
			}
		}
		return $phrases;
	}
	
/**
* Gets the available columns for a given phrase
*
* Gets the available columns for a given phrase
*
* @param integer $phrase_id The database id of the given phrase
* @result array All available columns
* @access public
*/
	function &getColumnsForPhrase($phrase_id)
	{
		global $ilDB;
		$columns = array();
    $query = sprintf("SELECT survey_category.* FROM survey_category, survey_phrase_category WHERE survey_phrase_category.category_fi = survey_category.category_id AND survey_phrase_category.phrase_fi = %s ORDER BY survey_phrase_category.sequence",
      $ilDB->quote($phrase_id)
    );
    $result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				$columns[$row->category_id] = $this->lng->txt($row->title);
			}
			else
			{
				$columns[$row->category_id] = $row->title;
			}
		}
		return $columns;
	}
	
/**
* Adds a phrase to the question
*
* Adds a phrase to the question
*
* @param integer $phrase_id The database id of the given phrase
* @access public
*/
	function addPhrase($phrase_id)
	{
		global $ilUser;
		global $ilDB;
		
    $query = sprintf("SELECT survey_category.* FROM survey_category, survey_phrase_category WHERE survey_phrase_category.category_fi = survey_category.category_id AND survey_phrase_category.phrase_fi = %s AND (survey_category.owner_fi = 0 OR survey_category.owner_fi = %s) ORDER BY survey_phrase_category.sequence",
      $ilDB->quote($phrase_id),
			$ilDB->quote($ilUser->id)
    );
    $result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				$this->addColumn($this->lng->txt($row->title));
			}
			else
			{
				$this->addColumn($row->title);
			}
		}
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
		global $ilDB;
		
    $query = sprintf("SELECT survey_question.*, survey_question_matrix.* FROM survey_question, survey_question_matrix WHERE survey_question.question_id = %s AND survey_question.question_id = survey_question_matrix.question_fi",
      $ilDB->quote($id)
    );
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return $result->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		{
			return array();
		}
	}
	
/**
* Loads a SurveyMatrixQuestion object from the database
*
* Loads a SurveyMatrixQuestion object from the database
*
* @param integer $id The database id of the matrix question
* @access public
*/
  function loadFromDb($id) 
	{
		global $ilDB;
    $query = sprintf("SELECT survey_question.*, survey_question_matrix.* FROM survey_question, survey_question_matrix WHERE survey_question.question_id = %s AND survey_question.question_id = survey_question_matrix.question_fi",
      $ilDB->quote($id)
    );
    $result = $ilDB->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) 
		{
      if ($result->numRows() == 1) 
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $data->question_id;
				$this->title = $data->title;
				$this->description = $data->description;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->owner = $data->owner_fi;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->questiontext = ilRTE::_replaceMediaObjectImageSrc($data->questiontext, 1);
				$this->obligatory = $data->obligatory;
				$this->complete = $data->complete;
				$this->original_id = $data->original_id;
				$this->setSubtype($data->subtype);
				$this->setBipolarAdjective(0, $data->bipolar_adjective1);
				$this->setBipolarAdjective(1, $data->bipolar_adjective2);
				$this->setRowSeparators($data->row_separators);
				$this->setNeutralColumnSeparator($data->neutral_column_separator);
				$this->setColumnSeparators($data->column_separators);
      }
      // loads materials uris from database
      $this->loadMaterialFromDb($id);

			$this->flushColumns();

      $query = sprintf("SELECT survey_variable.*, survey_category.title, survey_category.neutral FROM survey_variable, survey_category WHERE survey_variable.question_fi = %s AND survey_variable.category_fi = survey_category.category_id ORDER BY sequence ASC",
        $ilDB->quote($id)
      );
      $result = $ilDB->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) 
			{
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
				{
					if ($data->neutral == 0)
					{
						$this->addColumn($data->title);
					}
					else
					{
						$this->setNeutralColumn($data->title);
					}
        }
      }
			
			$query = sprintf("SELECT * FROM survey_question_matrix_rows WHERE question_fi = %s ORDER BY sequence",
				$ilDB->quote($id . "")
			);
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->addRow($row["title"]);
			}
    }
		parent::loadFromDb($id);
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
		if (
			strlen($this->getTitle()) && 
			strlen($this->getAuthor()) && 
			strlen($this->getQuestiontext()) && 
			$this->getColumnCount() &&
			$this->getRowCount()
		)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Saves a SurveyMatrixQuestion object to a database
*
* Saves a SurveyMatrixQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "", $withanswers = true)
  {
		global $ilDB;
		$complete = 0;
		if ($this->isComplete()) 
		{
			$complete = 1;
		}
		if ($original_id)
		{
			$original_id = $ilDB->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($this->questiontext, "spl:html",
			$this->getId());

    if ($this->id == -1) 
		{
      // Write new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO survey_question (question_id, questiontype_fi, obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getQuestionTypeID()),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->owner),
				$ilDB->quote($this->title),
				$ilDB->quote($this->description),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->questiontext, 0)),
				$ilDB->quote(sprintf("%d", $this->obligatory)),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
      );
      $result = $ilDB->query($query);
      if ($result == DB_OK) 
			{
        $this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO survey_question_matrix (question_fi, subtype, row_separators, column_separators, neutral_column_separator, bipolar_adjective1, bipolar_adjective2) VALUES (%s, %s, %s, %s, %s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote(sprintf("%d", $this->getSubtype())),
					$ilDB->quote($this->getRowSeparators() . ""),
					$ilDB->quote($this->getColumnSeparators() . ""),
					$ilDB->quote($this->getNeutralColumnSeparator() . ""),
					$ilDB->quote($this->getBipolarAdjective(0) . ""),
					$ilDB->quote($this->getBipolarAdjective(1) . "")
				);
				$ilDB->query($query);
      }
    } 
		else 
		{
      // update existing dataset
      $query = sprintf("UPDATE survey_question SET title = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->title),
				$ilDB->quote($this->description),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->questiontext, 0)),
				$ilDB->quote(sprintf("%d", $this->obligatory)),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
      );
      $result = $ilDB->query($query);
			$query = sprintf("UPDATE survey_question_matrix SET subtype = %s, row_separators = %s, column_separators = %s, neutral_column_separator = %s, bipolar_adjective1 = %s, bipolar_adjective2 = %s WHERE question_fi = %s",
				$ilDB->quote(sprintf("%d", $this->getSubtype())),
				$ilDB->quote($this->getRowSeparators() . ""),
				$ilDB->quote($this->getColumnSeparators() . ""),
				$ilDB->quote($this->getNeutralColumnSeparator() . ""),
				$ilDB->quote($this->getBipolarAdjective(0) . ""),
				$ilDB->quote($this->getBipolarAdjective(1) . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
    }
    if ($result == DB_OK) 
		{
      // saving material uris in the database
      $this->saveMaterialsToDb();
			if ($withanswers)
			{
				$this->saveColumnsToDb();
				$this->saveRowsToDb();
			}
    }
		parent::saveToDb($original_id);
  }
	
	function saveBipolarAdjectives($adjective1, $adjective2)
	{
		global $ilDB;
		
		$query = sprintf("UPDATE survey_question_matrix SET bipolar_adjective1 = %s, bipolar_adjective2 = %s WHERE question_fi = %s",
			$ilDB->quote($adjective1 . ""),
			$ilDB->quote($adjective2 . ""),
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
	}

	function saveColumnsToDb($original_id = "")
	{
		global $ilDB;
		
		// save columns
		$question_id = $this->getId();
		if (strlen($original_id))
		{
			$question_id = $original_id;
		}
		
		// delete existing column relations
		$query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		// create new column relations
		for ($i = 0; $i < $this->getColumnCount(); $i++)
		{
			$cat = $this->getColumn($i);
			$column_id = $this->saveColumnsToDb($cat);
			$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$ilDB->quote($column_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote(($i + 1) . ""),
				$ilDB->quote($i . "")
			);
			$answer_result = $ilDB->query($query);
		}
		if (strlen($this->getNeutralColumn()))
		{
			$column_id = $this->saveColumnsToDb($this->getNeutralColumn(), 1);
			$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$ilDB->quote($column_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote(($i + 1) . ""),
				$ilDB->quote($i . "")
			);
			$answer_result = $ilDB->query($query);
		}
		$this->saveCompletionStatus($original_id);
	}

	function saveRowsToDb($original_id = "")
	{
		global $ilDB;
		
		// save rows
		$question_id = $this->getId();
		if (strlen($original_id))
		{
			$question_id = $original_id;
		}
		
		// delete existing rows
		$query = sprintf("DELETE FROM survey_question_matrix_rows WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		// create new rows
		for ($i = 0; $i < $this->getRowCount(); $i++)
		{
			$row = $this->getRow($i);
			$query = sprintf("INSERT INTO survey_question_matrix_rows (id_survey_question_matrix_rows, title, sequence, question_fi) VALUES (NULL, %s, %s, %s)",
				$ilDB->quote($row . ""),
				$ilDB->quote($i . ""),
				$ilDB->quote($question_id . "")
			);
			$answer_result = $ilDB->query($query);
		}
		$this->saveCompletionStatus($original_id);
	}

	/**
	* Imports a question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function from_xml($xml_text)
	{
		$result = false;
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_text = preg_replace("/>\s*?</", "><", $xml_text);
		$this->domxml = domxml_open_mem($xml_text);
		if (!empty($this->domxml))
		{
			$root = $this->domxml->document_element();
			$item = $root->first_child();
			$this->setTitle($item->get_attribute("title"));
			$this->gaps = array();
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$this->setAuthor($comment);
						}
						else
						{
							$this->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$this->setObligatory($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$this->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_lid") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$shuffle = "";

								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "render_choice":
											$render_choice = $resp_lid_node;
											$labels = $render_choice->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												$material = $response_label->first_child();
												$mattext = $material->first_child();
												$shuf = 0;
												$this->addColumnAtPosition($mattext->get_content(), $response_label->get_attribute("ident"));
											}
											break;
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($this->getId() < 1)
													{
														$this->saveToDb();
													}
													$this->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
		}
		return $result;
	}

	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function to_xml($a_include_header = true, $obligatory_state = "")
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => $this->getId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getDescription());
		$a_xml_writer->xmlElement("qticomment", NULL, "ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlElement("qticomment", NULL, "Questiontype=".$this->getQuestionType());
		$a_xml_writer->xmlElement("qticomment", NULL, "Author=".$this->getAuthor());
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "obligatory");
		if (strcmp($obligatory_state, "") != 0)
		{
			$this->setObligatory($obligatory_state);
		}
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getObligatory()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		$attrs = array(
			"label" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$this->addQTIMaterial($a_xml_writer, $this->getQuestiontext());
		$ident = "";
		$cardinality = "";
		switch ($this->getSubtype())
		{
			case 0:
				$ident = "MCSR";
				$cardinality = "Single";
				break;
			case 1:
				$ident = "MCMR";
				$cardinality = "Multiple";
				break;
		}
		$attrs = array(
			"ident" => "$ident",
			"rcardinality" => "$cardinality"
		);
		$a_xml_writer->xmlStartTag("response_lid", $attrs);
		
		if (count($this->material))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches))
			{
				$attrs = array(
					"label" => $this->material["title"]
				);
				$a_xml_writer->xmlStartTag("material", $attrs);
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $this->material["internal_link"];
				}
				$a_xml_writer->xmlElement("mattext", NULL, $intlink);
				$a_xml_writer->xmlEndTag("material");
			}
		}

		$attrs = array(
			"shuffle" => "no"
		);
		$a_xml_writer->xmlStartTag("render_choice", $attrs);

		// add columns
		for ($index = 0; $index < $this->getColumnCount(); $index++)
		{
			$column = $this->getColumn($index);
			$attrs = array(
				"ident" => "$index"
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext", NULL, $column);
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		$a_xml_writer->xmlEndTag("render_choice");
		$a_xml_writer->xmlEndTag("response_lid");
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}

	function syncWithOriginal()
	{
		global $ilDB;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete()) 
			{
				$complete = 1;
			}
			$query = sprintf("UPDATE survey_question SET title = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->description . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->questiontext . ""),
				$ilDB->quote(sprintf("%d", $this->obligatory) . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE survey_question_matrix SET row_separators = %s, column_separators = %s, neutral_column_separator = %s, bipolar_adjective1 = %s, bipolar_adjective2 = %s WHERE question_fi = %s",
				$ilDB->quote($this->getRowSeparators() . ""),
				$ilDB->quote($this->getColumnSeparators() . ""),
				$ilDB->quote($this->getNeutralColumnSeparator() . ""),
				$ilDB->quote($this->getBipolarAdjective(0) . ""),
				$ilDB->quote($this->getBipolarAdjective(1) . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			if ($result == DB_OK) 
			{
				// sync columns
				$this->saveColumnsToDb($this->original_id);
				// sync rows
				$this->saveRowsToDb($this->original_id);
			}
		}
		parent::syncWithOriginal();
	}

/**
* Adds standard numbers as columns
*
* Adds standard numbers as columns
*
* @param integer $lower_limit The lower limit
* @param integer $upper_limit The upper limit
* @access public
*/
	function addStandardNumbers($lower_limit, $upper_limit)
	{
		for ($i = $lower_limit; $i <= $upper_limit; $i++)
		{
			$this->addColumn($i);
		}
	}

/**
* Saves a set of columns to a default phrase
*
* Saves a set of columns to a default phrase
*
* @param array $phrases The database ids of the seleted phrases
* @param string $title The title of the default phrase
* @access public
*/
	function savePhrase($phrases, $title)
	{
		global $ilUser;
		global $ilDB;
		
		$query = sprintf("INSERT INTO survey_phrase (phrase_id, title, defaultvalue, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$ilDB->quote($title . ""),
			$ilDB->quote("1"),
			$ilDB->quote($ilUser->id . "")
		);
    $result = $ilDB->query($query);
		$phrase_id = $ilDB->getLastInsertId();
				
		$counter = 1;
	  foreach ($phrases as $column_index) 
		{
			$column = $this->getColumn($column_index);
			$query = sprintf("INSERT INTO survey_category (category_id, title, neutral, defaultvalue, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$ilDB->quote($column . ""),
				$ilDB->quote("0"),
				$ilDB->quote("1"),
				$ilDB->quote($ilUser->getId() . "")
			);
			$result = $ilDB->query($query);
			$column_id = $ilDB->getLastInsertId();
			$query = sprintf("INSERT INTO survey_phrase_category (phrase_category_id, phrase_fi, category_fi, sequence) VALUES (NULL, %s, %s, %s)",
				$ilDB->quote($phrase_id . ""),
				$ilDB->quote($column_id . ""),
				$ilDB->quote($counter . "")
			);
			$result = $ilDB->query($query);
			$counter++;
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
		return "SurveyMatrixQuestion";
	}

	/**
	* Returns the name of the additional question data table in the database
	*
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "survey_question_matrix";
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
		$data = array();
		foreach ($post_data as $key => $value)
		{
			switch ($this->getSubtype())
			{
				case 0:
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						array_push($data, array("value" => $value, "row" => $matches[1]));
					}
					break;
				case 1:
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						array_push($data, array("value" => $value, "row" => $matches[1]));
					}
					break;
			}
		}
		return $data;
	}
	
	function checkUserInput($post_data)
	{
		if (!$this->getObligatory()) return "";
		switch ($this->getSubtype())
		{
			case 0:
				$counter = 0;
				foreach ($post_data as $key => $value)
				{
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						$counter++;
					}
				}
				if ($counter != $this->getRowCount()) return $this->lng->txt("matrix_question_radio_button_not_checked");
				break;
			case 1:
				foreach ($post_data as $key => $value)
				{
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						if ((!is_array($value)) || (count($value) < 1))
						{
							return $this->lng->txt("matrix_question_checkbox_not_checked");
						}
					}
				}
				break;
		}
		return "";
	}

	function saveUserInput($post_data, $survey_id, $user_id, $anonymous_id)
	{
		global $ilDB;

		switch ($this->getSubtype())
		{
			case 0:
				foreach ($post_data as $key => $value)
				{
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						$query = sprintf("INSERT INTO survey_answer (answer_id, survey_fi, question_fi, user_fi, anonymous_id, value, textanswer, row, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
							$ilDB->quote($survey_id . ""),
							$ilDB->quote($this->getId() . ""),
							$ilDB->quote($user_id . ""),
							$ilDB->quote($anonymous_id . ""),
							$ilDB->quote($value . ""),
							"NULL",
							$ilDB->quote($matches[1] . "")
						);
						$result = $ilDB->query($query);
					}
				}
				break;
			case 1:
				foreach ($post_data as $key => $value)
				{
					if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches))
					{
						foreach ($value as $checked)
						{
							if (strlen($checked))
							{
								$query = sprintf("INSERT INTO survey_answer (answer_id, survey_fi, question_fi, user_fi, anonymous_id, value, textanswer, row, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
									$ilDB->quote($survey_id . ""),
									$ilDB->quote($this->getId() . ""),
									$ilDB->quote($user_id . ""),
									$ilDB->quote($anonymous_id . ""),
									$ilDB->quote($checked . ""),
									"NULL",
									$ilDB->quote($matches[1] . "")
								);
								$result = $ilDB->query($query);
							}
						}
					}
				}
				break;
		}
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
		parent::deleteAdditionalTableData($question_id);
		
		global $ilDB;
		$query = sprintf("DELETE FROM survey_question_matrix_rows WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
	}

	/**
	* Returns the number of users that answered the question for a given survey
	*
	* Returns the number of users that answered the question for a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users
	* @access public
	*/
	function getNrOfUsersAnswered($survey_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT DISTINCT(CONCAT(user_fi,anonymous_id,question_fi,survey_fi)) AS participants FROM survey_answer WHERE question_fi = %s AND survey_fi = %s",
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($survey_id . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
	}

	/**
	* Returns the cumulated results for a given row
	*
	* Returns the cumulated results for a given row
	*
	* @param integer $row The index of the row
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users who took part in the survey
	* @access public
	*/
	function &getCumulatedResultsForRow($rowindex, $survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$query = sprintf("SELECT * FROM survey_answer WHERE question_fi = %s AND survey_fi = %s AND row = %s",
			$ilDB->quote($question_id . ""),
			$ilDB->quote($survey_id . ""),
			$ilDB->quote($rowindex . "")
		);
		$result = $ilDB->query($query);
		
		switch ($this->getSubtype())
		{
			case 0:
			case 1:
				while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$cumulated[$row->value]++;
				}
				asort($cumulated, SORT_NUMERIC);
				end($cumulated);
				break;
		}
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $this->getNrOfUsersAnswered($survey_id);
		$result_array["USERS_SKIPPED"] = $nr_of_users - $this->getNrOfUsersAnswered($survey_id);

		$prefix = "";
		if (strcmp(key($cumulated), "") != 0)
		{
			$prefix = (key($cumulated)+1) . " - ";
		}
		$cat = $this->getColumn(key($cumulated));
		$result_array["MODE"] =  $prefix . $cat;
		$result_array["MODE_VALUE"] =  key($cumulated)+1;
		$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		for ($key = 0; $key < $this->getColumnCount(); $key++)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$key]/$numrows);
			}
			$cat = $this->getColumn($key);
			$result_array["variables"][$key] = array("title" => $cat, "selected" => (int)$cumulated[$key], "percentage" => $percentage);
		}
		ksort($cumulated, SORT_NUMERIC);
		$median = array();
		$total = 0;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value+1);
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
				if (round($median_value) != $median_value)
				{
					$cat = $this->getColumn((int)floor($median_value)-1);
					$cat2 = $this->getColumn((int)ceil($median_value)-1);
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $cat . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $cat2 . ")";
				}
			}
			else
			{
				$median_value = $median[(($total+1)/2)-1];
			}
		}
		else
		{
			$median_value = "";
		}
		$result_array["ARITHMETIC_MEAN"] = "";
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMatrixQuestion";
		$result_array["ROW"] = $this->getRow($rowindex);
		return $result_array;
	}

	/**
	* Returns the cumulated results for the question
	*
	* Returns the cumulated results for the question
	*
	* @param integer $survey_id The database ID of the survey
	* @return integer The number of users who took part in the survey
	* @access public
	*/
	function &getCumulatedResults($survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$query = sprintf("SELECT * FROM survey_answer WHERE question_fi = %s AND survey_fi = %s",
			$ilDB->quote($question_id),
			$ilDB->quote($survey_id)
		);
		$result = $ilDB->query($query);
		
		switch ($this->getSubtype())
		{
			case 0:
			case 1:
				while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$cumulated[$row->value]++;
				}
				asort($cumulated, SORT_NUMERIC);
				end($cumulated);
				break;
		}
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $this->getNrOfUsersAnswered($survey_id);
		$result_array["USERS_SKIPPED"] = $nr_of_users - $this->getNrOfUsersAnswered($survey_id);

		$prefix = "";
		if (strcmp(key($cumulated), "") != 0)
		{
			$prefix = (key($cumulated)+1) . " - ";
		}
		$cat = $this->getColumn(key($cumulated));
		$result_array["MODE"] =  $prefix . $cat;
		$result_array["MODE_VALUE"] =  key($cumulated)+1;
		$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		for ($key = 0; $key < $this->getColumnCount(); $key++)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$key]/$numrows);
			}
			$cat = $this->getColumn($key);
			$result_array["variables"][$key] = array("title" => $cat, "selected" => (int)$cumulated[$key], "percentage" => $percentage);
		}
		ksort($cumulated, SORT_NUMERIC);
		$median = array();
		$total = 0;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value+1);
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
				if (round($median_value) != $median_value)
				{
					$cat = $this->getColumn((int)floor($median_value)-1);
					$cat2 = $this->getColumn((int)ceil($median_value)-1);
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $cat . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $cat2 . ")";
				}
			}
			else
			{
				$median_value = $median[(($total+1)/2)-1];
			}
		}
		else
		{
			$median_value = "";
		}
		$result_array["ARITHMETIC_MEAN"] = "";
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMatrixQuestion";
		
		$cumulated_results = array();
		$cumulated_results["TOTAL"] = $result_array;
		for ($i = 0; $i < $this->getRowCount(); $i++)
		{
			$rowresult =& $this->getCumulatedResultsForRow($i, $survey_id, $nr_of_users);
			$cumulated_results[$i] = $rowresult;
		}
		return $cumulated_results;
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
		include_once ("./classes/class.ilExcelUtils.php");
		$worksheet =& $workbook->addWorksheet();
		$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
		$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
		$worksheet->writeString(1, 0, ilExcelUtils::_convert_text($this->lng->txt("question")), $format_bold);
		$worksheet->writeString(1, 1, ilExcelUtils::_convert_text($this->getQuestiontext()));
		$worksheet->writeString(2, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type")), $format_bold);
		$worksheet->writeString(2, 1, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())));
		$worksheet->writeString(3, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered")), $format_bold);
		$worksheet->write(3, 1, $eval_data["USERS_ANSWERED"]);
		$worksheet->writeString(4, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped")), $format_bold);
		$worksheet->write(4, 1, $eval_data["USERS_SKIPPED"]);
		$rowcounter = 5;

		preg_match("/(.*?)\s+-\s+(.*)/", $eval_data["MODE"], $matches);
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[1]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[2]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MODE_NR_OF_SELECTIONS"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text(str_replace("<br />", " ", $eval_data["MEDIAN"])));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories")), $format_bold);
		$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_title);
		$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value")), $format_title);
		$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected")), $format_title);
		$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections")), $format_title);

		foreach ($eval_data["variables"] as $key => $value)
		{
			$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"]));
			$worksheet->write($rowcounter, 2, $key+1);
			$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"]));
			$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"]), $format_percent);
		}
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
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["value"]+1);
			}
		}
		else
		{
			array_push($a_array, $this->lng->txt("skipped"));
		}
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
		global $ilDB;
		
		$answers = array();

		$query = sprintf("SELECT * FROM survey_answer WHERE survey_fi = %s AND question_fi = %s ORDER BY row, value",
			$ilDB->quote($survey_id),
			$ilDB->quote($this->getId())
		);
		$result = $ilDB->query($query);
		$results = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$column = $this->getColumn($row["value"]);
			if (strlen($row["anonymous_id"]) > 0)
			{
				if (!is_array($answers[$row["anonymous_id"]])) $answers[$row["anonymous_id"]] = array();
				array_push($answers[$row["anonymous_id"]], $this->getRow($row["row"]) . ": " . ($row["value"] + 1) . " - " . $column);
			}
			else
			{
				if (!is_array($answers[$row["user_fi"]])) $answers[$row["user_fi"]] = array();
				array_push($answers[$row["user_fi"]], $this->getRow($row["row"]) . ": " . ($row["value"] + 1) . " - " . $column);
			}
		}
		foreach ($answers as $key => $value)
		{
			$answers[$key] = implode("<br />", $value);
		}
		return $answers;
	}

	/**
	* Returns the subtype of the matrix question
	*
	* Returns the subtype of the matrix question
	*
	* @return integer The subtype of the matrix question
	* @access public
	*/
	function getSubtype()
	{
		return $this->subtype;
	}

	/**
	* Sets the subtype of the matrix question
	*
	* Sets the subtype of the matrix question
	*
	* @return integer $a_subtype The subtype of the matrix question
	* @access public
	*/
	function setSubtype($a_subtype = 0)
	{
		switch ($a_subtype)
		{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
				$this->subtype = $a_subtype;
				break;
			case 0:
			default:
				$this->subtype = 0;
				break;
		}
	}
	
	/**
	* Enables/Disables separators for the matrix columns
	*
	* Enables/Disables separators for the matrix columns
	*
	* @param integer $enable 1 if the separators should be enabled, 0 otherwise
	* @access public
	*/
	function setColumnSeparators($enable = 0)
	{
		switch ($enable)
		{
			case 1:
				$this->columnSeparators = 1;
				break;
			case 0:
			default:
				$this->columnSeparators = 0;
				break;
		}
	}
	
	/**
	* Gets the separators enable state for the matrix columns
	*
	* Gets the separators enable state for the matrix columns
	*
	* @return integer 1 if the separators are enabled, 0 otherwise
	* @access public
	*/
	function getColumnSeparators()
	{
		return $this->columnSeparators;
	}
	
	/**
	* Enables/Disables separators for the matrix rows
	*
	* Enables/Disables separators for the matrix rows
	*
	* @param integer $enable 1 if the separators should be enabled, 0 otherwise
	* @access public
	*/
	function setRowSeparators($enable = 0)
	{
		switch ($enable)
		{
			case 1:
				$this->rowSeparators = 1;
				break;
			case 0:
			default:
				$this->rowSeparators = 0;
				break;
		}
	}
	
	/**
	* Gets the separators enable state for the matrix rows
	*
	* Gets the separators enable state for the matrix rows
	*
	* @return integer 1 if the separators are enabled, 0 otherwise
	* @access public
	*/
	function getRowSeparators()
	{
		return $this->rowSeparators;
	}

	/**
	* Enables/Disables a separator for the neutral column
	*
	* Enables/Disables a separator for the neutral column
	*
	* @param integer $enable 1 if the separator should be enabled, 0 otherwise
	* @access public
	*/
	function setNeutralColumnSeparator($enable = 0)
	{
		switch ($enable)
		{
			case 1:
				$this->neutralColumnSeparator = 1;
				break;
			case 0:
			default:
				$this->neutralColumnSeparator = 0;
				break;
		}
	}
	
	/**
	* Gets the separator enable state for the neutral column
	*
	* Gets the separator enable state for the neutral column
	*
	* @return integer 1 if the separator is enabled, 0 otherwise
	* @access public
	*/
	function getNeutralColumnSeparator()
	{
		return $this->neutralColumnSeparator;
	}
}
?>
