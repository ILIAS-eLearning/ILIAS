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
* Class for search actions in ILIAS survey tool
*
* The SurveySearch class defines and encapsulates basic methods and attributes
* to search the ILIAS survey tool for questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
*/
class SurveySearch 
{
/**
* Search terms
*
* An array containing all search terms
*
* @var array
*/
  var $search_terms;

/**
* Concatenation
*
* The concatenation type of the search terms
*
* @var integer
*/
  var $concatenation;

/**
* Search field
*
* A database field to restrict the search results
*
* @var string
*/
  var $search_field;

/**
* Search type
*
* A question type to restrict the search results
*
* @var string
*/
  var $search_type;

/**
* Search results
*
* An array containing the results of a search
*
* @var array
*/
  var $search_results;

/**
* The reference to the ILIAS database class
*
* The reference to the ILIAS database class
*
* @var object
*/
  var $ilDB;


/**
* SurveySearch constructor
*
* The constructor takes possible arguments an creates an instance of the SurveySearch object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveySearch(
    $search_text = "",
    $concatenation = CONCAT_AND,
    $search_field = "all",
		$search_type = "all"
  )

  {
		global $ilDB;

		$this->ilDB =& $ilDB;

    $this->search_terms = split(" +", $search_text);
    $this->concatenation = $concatenation;
		$this->search_field = $search_field;
    $this->search_type = $search_type;
		$this->search_results = array();
	}
	
/**
* Executes a search
*
* Executes a search
*
* @access public
*/
	function search()
	{
		$where = "";
		$fields = array();
		if (strcmp($this->search_type, "all") != 0)
		{
			$where = sprintf("svy_qtype.type_tag = %s",
				$this->ilDB->quote($this->search_type)
			);
		}
		foreach ($this->search_terms as $term)
		{
			switch ($this->search_field)
			{
				case "all":
					$fields["$term"] = array();
					array_push($fields["$term"], sprintf("svy_question.title LIKE %s",
						$this->ilDB->quote("%$term%")
					));
					array_push($fields["$term"], sprintf("svy_question.description LIKE %s",
						$this->ilDB->quote("%$term%")
					));
					array_push($fields["$term"], sprintf("svy_question.author LIKE %s",
						$this->ilDB->quote("%$term%")
					));
					array_push($fields["$term"], sprintf("svy_question.questiontext LIKE %s",
						$this->ilDB->quote("%$term%")
					));
					break;
				default:
					$fields["$term"] = array();
					array_push($fields["$term"], sprintf("svy_question.$this->search_field LIKE %s",
						$this->ilDB->quote("%$term%")
					));
					break;				
			}
		}
		$cumulated_fields = array();
		foreach ($fields as $params)
		{
			array_push($cumulated_fields, "(" . join($params, " OR ") . ")");
		}
		$str_where = "";
		if ($this->concatenation == CONCAT_AND)
		{
			$str_where = "(" . join($cumulated_fields, " AND ") . ")";
		}
		else
		{
			$str_where = "(" . join($cumulated_fields, " OR ") . ")";
		}
		if ($str_where)
		{
			$str_where = " AND $str_where";
		}
		if ($where)
		{
			$str_where .= " AND (" . $where . ")";
		}
		$query = "SELECT svy_question.*, svy_qtype.type_tag, object_reference.ref_id FROM svy_question, svy_qtype, object_reference WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id AND ISNULL(svy_question.original_id) AND svy_question.obj_fi = object_reference.obj_id AND svy_question.obj_fi > 0$str_where";
		$result = $this->ilDB->query($query);
		$result_array = array();
		global $rbacsystem;
		if ($result->numRows() > 0) 
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				if (($row["complete"] == 1) and ($rbacsystem->checkAccess('write', $row["ref_id"])))
				{
					array_push($result_array, $row);
				}
			}
		}
		$this->search_results =& $result_array;
	}
}
?>
