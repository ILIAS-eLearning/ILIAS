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

require_once "./assessment/classes/class.assMark.php";

/**
* A class defining mark schemas for assessment test objects
* 
* A class defining mark schemas for assessment test objects
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMarkSchema.php
* @modulegroup   Assessment
*/
class ASS_MarkSchema {
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
  function create_simple_schema(
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
    $this->add_mark_step($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed);
    $this->add_mark_step($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed);
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
  function add_mark_step(
    $txt_short = "", 
    $txt_official = "", 
    $percentage = 0,
		$passed = 0
  )
  {
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
		global $ilias;
		$db =& $ilias->db->db;
		
    if (!$test_id) return;
    // Delete all entries
    $query = sprintf("DELETE FROM tst_mark WHERE test_fi = %s",
      $db->quote($test_id)
    );
    $result = $db->query($query);
    if (count($this->mark_steps) == 0) return;
    
    // Write new datasets
    foreach ($this->mark_steps as $key => $value) {
      $query = sprintf("INSERT INTO tst_mark (mark_id, test_fi, short_name, official_name, minimum_level, passed, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
        $db->quote($test_id),
        $db->quote($value->get_short_name()), 
        $db->quote($value->get_official_name()), 
        $db->quote($value->get_minimum_level()),
        $db->quote(sprintf("%d", $value->get_passed()))
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
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
		global $ilias;
		$db =& $ilias->db->db;
		
    if (!$test_id) return;
    $query = sprintf("SELECT * FROM tst_mark WHERE test_fi = %s ORDER BY minimum_level",
      $db->quote($test_id)
    );

    $result = $db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
        $this->add_mark_step($data->short_name, $data->official_name, $data->minimum_level, $data->passed);
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
      if ($a->get_minimum_level() == $b->get_minimum_level()) {
        $res = strcmp($a->get_short_name(), $b->get_short_name());
        if ($res == 0) {
          return strcmp($a->get_official_name(), $b->get_official_name());
        } else {
          return $res;
        }
      }
      return ($a->get_minimum_level() < $b->get_minimum_level()) ? -1 : 1;
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
  function delete_mark_step($index = 0) {
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
  function delete_mark_steps($indexes) {
    foreach ($indexes as $key => $index) {
      if (!(($index < 0) or (count($this->mark_steps) < 1))) {
        unset($this->mark_steps[$index]);
      }
    }
    $this->mark_steps = array_values($this->mark_steps);
  }

  function get_matching_mark($percentage) {
    for ($i = count($this->mark_steps) - 1; $i >= 0; $i--) {
      if ($percentage >= $this->mark_steps[$i]->get_minimum_level()) {
        return $this->mark_steps[$i];
      }
    }
  }
  
}

?>
