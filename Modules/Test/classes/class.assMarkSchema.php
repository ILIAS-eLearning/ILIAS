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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* A class defining mark schemas for assessment test objects
* 
* A class defining mark schemas for assessment test objects
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTest
*/
class ASS_MarkSchema 
{
/**
* An array containing all mark steps defined for the test
* 
* An array containing all mark steps defined for the test
*
* @var array
*/
  var $mark_steps;

/**
* ASS_MarkSchema constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_MarkSchema object.
*
* @access public
*/
  function ASS_MarkSchema() 
  {
    $this->mark_steps = array();
  }

/**
* Creates a simple mark schema for two mark steps
* 
* Creates a simple mark schema for two mark steps:
* failed an passed.
*
* @param string $txt_failed_short The short text of the failed mark
* @param string $txt_failed_official The official text of the failed mark
* @param double $percentage_failed The minimum percentage level reaching the failed mark
* @param integer $failed_passed Indicates the passed status of the failed mark (0 = failed, 1 = passed)
* @param string $txt_passed_short The short text of the passed mark
* @param string $txt_passed_official The official text of the passed mark
* @param double $percentage_passed The minimum percentage level reaching the passed mark
* @param integer $passed_passed Indicates the passed status of the passed mark (0 = failed, 1 = passed)
* @access public
* @see $mark_steps
*/
  function createSimpleSchema(
    $txt_failed_short = "failed", 
    $txt_failed_official = "failed", 
    $percentage_failed = 0,
		$failed_passed = 0,
    $txt_passed_short = "passed",
    $txt_passed_official = "passed",
    $percentage_passed = 50,
		$passed_passed = 1
  )
  {
    $this->flush();
    $this->addMarkStep($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed);
    $this->addMarkStep($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed);
  }

/**
* Adds a mark step to the mark schema
* 
* Adds a mark step to the mark schema. A new ASS_Mark object will be created and stored
* in the $mark_steps array.
*
* @param string $txt_short The short text of the mark
* @param string $txt_official The official text of the mark
* @param double $percentage The minimum percentage level reaching the mark
* @param integer $passed The passed status of the mark (0 = failed, 1 = passed)
* @access public
* @see $mark_steps
*/
  function addMarkStep(
    $txt_short = "", 
    $txt_official = "", 
    $percentage = 0,
		$passed = 0
  )
  {
		include_once "./Modules/Test/classes/class.assMark.php";
    $mark = new ASS_Mark($txt_short, $txt_official, $percentage, $passed);
    array_push($this->mark_steps, $mark);
  }

/**
* Saves a ASS_MarkSchema object to a database
* 
* Saves a ASS_MarkSchema object to a database (experimental)
*
* @param integer $test_id The database id of the related test
* @access public
*/
  function saveToDb($test_id)
  {
		global $lng;
		global $ilDB;

		$oldmarks = array();
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
				$ilDB->quote($test_id)
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$oldmarks[$row["minimum_level"]] = $row;
				}
			}
		}
		
    if (!$test_id) return;
    // Delete all entries
    $query = sprintf("DELETE FROM tst_mark WHERE test_fi = %s",
      $ilDB->quote($test_id)
    );
    $result = $ilDB->query($query);
    if (count($this->mark_steps) == 0) return;
    
    // Write new datasets
    foreach ($this->mark_steps as $key => $value) 
		{
      $query = sprintf("INSERT INTO tst_mark (mark_id, test_fi, short_name, official_name, minimum_level, passed, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
        $ilDB->quote($test_id),
        $ilDB->quote($value->getShortName()), 
        $ilDB->quote($value->getOfficialName()), 
        $ilDB->quote($value->getMinimumLevel()),
        $ilDB->quote(sprintf("%d", $value->getPassed()))
      );
      $result = $ilDB->query($query);
      if ($result == DB_OK) {
      }
    }
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
				$ilDB->quote($test_id)
			);
			$result = $ilDB->query($query);
			$newmarks = array();
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$newmarks[$row["minimum_level"]] = $row;
				}
			}
			foreach ($oldmarks as $level => $row)
			{
				if (array_key_exists($level, $newmarks))
				{
					$difffields = array();
					foreach ($row as $key => $value)
					{
						if (strcmp($value, $newmarks[$level][$key]) != 0)
						{
							switch ($key)
							{
								case "mark_id":
								case "TIMESTAMP":
									break;
								default:
									array_push($difffields, "$key: $value => " .$newmarks[$level][$key]); 
									break;
							}
						}
					}
					if (count($difffields))
					{
						$this->logAction($test_id, $lng->txtlng("assessment", "log_mark_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . join($difffields, ", "));
					}
				}
				else
				{
					$this->logAction($test_id, $lng->txtlng("assessment", "log_mark_removed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . 
						$lng->txtlng("assessment", "tst_mark_minimum_level", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["minimum_level"] . ", " .
						$lng->txtlng("assessment", "tst_mark_short_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["short_name"] . ", " .
						$lng->txtlng("assessment", "tst_mark_official_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["official_name"] . ", " .
						$lng->txtlng("assessment", "tst_mark_passed", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["passed"]);						
				}
			}
			foreach ($newmarks as $level => $row)
			{
				if (!array_key_exists($level, $oldmarks))
				{
					$this->logAction($test_id, $lng->txtlng("assessment", "log_mark_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . 
						$lng->txtlng("assessment", "tst_mark_minimum_level", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["minimum_level"] . ", " .
						$lng->txtlng("assessment", "tst_mark_short_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["short_name"] . ", " .
						$lng->txtlng("assessment", "tst_mark_official_form", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["official_name"] . ", " .
						$lng->txtlng("assessment", "tst_mark_passed", ilObjAssessmentFolder::_getLogLanguage()) . " = " . $row["passed"]);
				}
			}
		}
  }

/**
* Loads a ASS_MarkSchema object from a database
* 
* Loads a ASS_MarkSchema object from a database (experimental)
*
* @param integer $test_id A unique key which defines the test in the database
* @access public
*/
  function loadFromDb($test_id)
  {
		global $ilDB;
		
    if (!$test_id) return;
    $query = sprintf("SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
      $ilDB->quote($test_id)
    );

    $result = $ilDB->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
        $this->addMarkStep($data->short_name, $data->official_name, $data->minimum_level, $data->passed);
      }
    }
  }
  
/**
* Empties the mark schema and removes all mark steps
* 
* Empties the mark schema and removes all mark steps
*
* @access public
* @see $mark_steps
*/
  function flush() {
    $this->mark_steps = array();
  }
  
/**
* Sorts the mark schema using the minimum level values
* 
* Sorts the mark schema using the minimum level values
*
* @access public
* @see $mark_steps
*/
  function sort() {
    function level_sort($a, $b) {
      if ($a->getMinimumLevel() == $b->getMinimumLevel()) {
        $res = strcmp($a->getShortName(), $b->getShortName());
        if ($res == 0) {
          return strcmp($a->getOfficialName(), $b->getOfficialName());
        } else {
          return $res;
        }
      }
      return ($a->getMinimumLevel() < $b->getMinimumLevel()) ? -1 : 1;
    }
    
    usort($this->mark_steps, 'level_sort');
  }
  
/**
* Deletes a mark step
* 
* Deletes the mark step with a given index.
*
* @param integer $index The index of the mark step to delete
* @access public
* @see $mark_steps
*/
  function deleteMarkStep($index = 0) {
    if ($index < 0) return;
    if (count($this->mark_steps) < 1) return;
    if ($index >= count($this->mark_steps)) return;
    unset($this->mark_steps[$index]);
    $this->mark_steps = array_values($this->mark_steps);
  }

/**
* Deletes multiple mark steps
* 
* Deletes multiple mark steps using their index positions.
*
* @param array $indexes An array with all the index positions to delete
* @access public
* @see $mark_steps
*/
  function deleteMarkSteps($indexes) {
    foreach ($indexes as $key => $index) {
      if (!(($index < 0) or (count($this->mark_steps) < 1))) {
        unset($this->mark_steps[$index]);
      }
    }
    $this->mark_steps = array_values($this->mark_steps);
  }

/**
* Returns the matching mark for a given percentage
* 
* Returns the matching mark for a given percentage
*
* @param double $percentage A percentage value between 0 and 100
* @return mixed The mark object, if a matching mark was found, false otherwise
* @access public
* @see $mark_steps
*/
  function getMatchingMark($percentage) {
    for ($i = count($this->mark_steps) - 1; $i >= 0; $i--) {
      if ($percentage >= $this->mark_steps[$i]->getMinimumLevel()) {
        return $this->mark_steps[$i];
      }
    }
		return false;
  }
  
	/**
	* Returns the matching mark for a given percentage
	* 
	* Returns the matching mark for a given percentage
	*
	* @param int $test_id The database id of the test
	* @param double $percentage A percentage value between 0 and 100
	* @return mixed The mark object, if a matching mark was found, false otherwise
	* @access public
	* @see $mark_steps
	*/
	function _getMatchingMark($test_id, $percentage)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level DESC",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($percentage >= $row["minimum_level"])
			{
				return $row;
			}
		}
		return FALSE;
	}

	/**
	* Returns the matching mark for a given percentage
	* 
	* Returns the matching mark for a given percentage
	*
	* @param int $test_id The database id of the test
	* @param double $percentage A percentage value between 0 and 100
	* @return mixed The mark object, if a matching mark was found, false otherwise
	* @access public
	* @see $mark_steps
	*/
	function _getMatchingMarkFromObjId($a_obj_id, $percentage)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_mark.* FROM tst_mark, tst_tests WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.obj_fi = %s ORDER BY minimum_level DESC",
			$ilDB->quote($a_obj_id . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($percentage >= $row["minimum_level"])
			{
				return $row;
			}
		}
		return FALSE;
	}
	
/**
* Check the marks for consistency
* 
* Check the marks for consistency
*
* @return mixed true if the check succeeds, als a text string containing a language string for an error message
* @access public
* @see $mark_steps
*/
	function checkMarks()
	{
		$minimum_percentage = 100;
		$passed = 0;
    for ($i = 0; $i < count($this->mark_steps); $i++) {
			if ($this->mark_steps[$i]->getMinimumLevel() < $minimum_percentage)
			{
				$minimum_percentage = $this->mark_steps[$i]->getMinimumLevel();
			}
			if ($this->mark_steps[$i]->getPassed())
			{
				$passed++;
			}
    }
		if ($minimum_percentage != 0)
		{
			return "min_percentage_ne_0";
		}
		if ($passed == 0)
		{
			return "no_passed_mark";
		}
		return true;
	}

/**
* Logs an action into the Test&Assessment log
* 
* Logs an action into the Test&Assessment log
*
* @param integer $test_id The database id of the test
* @param string $logtext The log text
* @access public
*/
	function logAction($test_id, $logtext = "")
	{
		global $ilUser;
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		ilObjAssessmentFolder::_addLog($ilUser->id, ilObjTest::_getObjectIDFromTestID($test_id), $logtext, "", "", TRUE, $_GET["ref_id"]);
	}
}

?>
