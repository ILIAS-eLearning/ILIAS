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

require_once "class.assQuestion.php";
require_once "class.assAnswerTrueFalse.php";

define("CLOZE_TEXT", "0");
define("CLOZE_SELECT", "1");

/**
* Class for cloze tests
*
* ASS_ClozeText is a class for cloze tests using text or select gaps.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assClozeTest.php
* @modulegroup   Assessment
*/
class ASS_ClozeTest extends ASS_Question {
/**
* The cloze text containing variables defininig the clozes
*
* The cloze text containing variables defininig the clozes. The syntax for the cloze variables is *[varname],
* where varname has to be an unique identifier.
*
* @var string
*/
  var $cloze_text;

/**
* The cloze question type
*
* The cloze question type (text gap or select gap). Use the predefined constants CLOZE_TEXT and CLOZE_SELECT.
* The default value is CLOZE_TEXT.
*
* @var integer
*/
  var $cloze_type;

/**
* The gaps of the cloze question
*
* $gaps is an array of the predefined gaps of the cloze question
*
* @var array
*/
  var $gaps;

/**
* The start tag beginning a cloze gap
*
* The start tag is set to "*[" by default.
*
* @var string
*/
  var $start_tag;
/**
* The end tag beginning a cloze gap
*
* The end tag is set to "]" by default.
*
* @var string
*/
  var $end_tag;
/**
* ASS_ClozeTest constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_ClozeTest object.
*
* @param string $title A title string to describe the question
* @param string $comment A comment string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @param string $cloze_text The question string of the cloze test
* @param integer $cloze_type The cloze question type
* @param string $start_tag The start tag for a cloze gap
* @param string $end_tag The end tag for a cloze gap
* @param string $materials An uri to additional materials
* @access public
*/
  function ASS_ClozeTest(
    $title = "",
    $comment = "",
    $author = "",
    $owner = -1,
    $cloze_text = "",
    $cloze_type = CLOZE_TEXT,
    $start_tag = "#",
    $end_tag = "#"
  )
  {
    $this->start_tag = $start_tag;
    $this->end_tag = $end_tag;
    $this->ASS_Question($title, $comment, $author, $owner);
    $this->gaps = array();
    $this->cloze_type = $cloze_type;
    $this->set_cloze_text($cloze_text);
  }

/**
* Returns true, if a cloze test is complete for use
*
* Returns true, if a cloze test is complete for use
*
* @return boolean True, if the cloze test is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->cloze_text) and (count($this->gaps)))
		{
			return true;
		} 
			else 
		{
			return false;
		}
	}

/**
* Saves a ASS_ClozeTest object to a database
*
* Saves a ASS_ClozeTest object to a database (experimental)
*
* @param object $db A pear DB object
* @access public
*/
  function save_to_db()
  {
    global $ilias;
    $db =& $ilias->db->db;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}

    if ($this->id == -1) {
      // Neuen Datensatz schreiben
      $id = $db->nextId('qpl_questions');
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, ref_fi, title, comment, author, owner, question_text, start_tag, end_tag, cloze_type, complete, created, TIMESTAMP) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote($id),
        $db->quote(3),
        $db->quote($this->ref_id),
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->owner),
        $db->quote($this->cloze_text),
        $db->quote($this->start_tag),
        $db->quote($this->end_tag),
        $db->quote($this->cloze_type),
				$db->quote("$complete"),
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->id = $id;
        // Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
        if ($this->get_test_id() > 0) {
          $this->insert_into_test($this->get_test_id());
        }
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE qpl_questions SET title = %s, comment = %s, author = %s, question_text = %s, cloze_type = %s, complete = %s, start_tag = %s, end_tag = %s WHERE question_id = %s",
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->cloze_text),
        $db->quote($this->cloze_type),
				$db->quote("$complete"),
        $db->quote("$this->start_tag"),
        $db->quote("$this->end_tag"),
        $db->quote($this->id)
      );
      $result = $db->query($query);
    }

    if ($result == DB_OK) {
      // saving material uris in the database
      $this->save_materials_to_db();

      // Antworten schreiben

      // alte Antworten löschen
      $query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
        $db->quote($this->id)
      );
      $result = $db->query($query);
      // Anworten wegschreiben
      foreach ($this->gaps as $key => $value) {
        foreach ($value as $answer_id => $answer_obj) {
          $query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, gap_id, answertext, points, aorder, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
            $db->quote($this->id),
            $db->quote($key),
            $db->quote($answer_obj->get_answertext()),
            $db->quote($answer_obj->get_points()),
            $db->quote($answer_obj->get_order()),
            $db->quote($answer_obj->get_correctness())
          );
          $answer_result = $db->query($query);
        }
      }
    }
  }

/**
* Loads a ASS_ClozeTest object from a database
*
* Loads a ASS_ClozeTest object from a database (experimental)
*
* @param object $db A pear DB object
* @param integer $question_id A unique key which defines the cloze test in the database
* @access public
*/
  function load_from_db($question_id)
  {
    global $ilias;
    $db =& $ilias->db->db;

    $query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
      $db->quote($question_id)
    );
    $result = $db->query($query);
    if (strcmp(get_class($result), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $question_id;
        $this->ref_id = $data->ref_fi;
        $this->title = $data->title;
        $this->comment = $data->comment;
        $this->author = $data->author;
        $this->owner = $data->owner;
        $this->cloze_text = $data->question_text;
        $this->start_tag = $data->start_tag;
        $this->end_tag = $data->end_tag;
        $this->cloze_type = $data->cloze_type;
      }
      // loads materials uris from database
      $this->load_material_from_db($question_id);

      $query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
        $db->quote($question_id)
      );
      $result = $db->query($query);
      if (strcmp(get_class($result), db_result) == 0) {
        $counter = -1;
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          if ($data->gap_id != $counter) {
            $answer_array = array();
            array_push($this->gaps, $answer_array);
            $counter = $data->gap_id;
          }
          array_push($this->gaps[$counter], new ASS_AnswerTrueFalse($data->answertext, $data->points, $data->aorder, $data->correctness));
        }
      }
    }
  }

/**
* Evaluates the text gap solutions from the cloze text
*
* Evaluates the text gap solutions from the cloze text. A single or multiple text gap solutions
* could be entered using the following syntax in the cloze text:
* solution1 [, solution2, ..., solutionN] enclosed in the text gap selector *[]
*
* @param string $cloze_text The cloze text with all gaps and gap gaps
* @access public
* @see $cloze_text
*/
  function set_cloze_text($cloze_text = "") {
    $this->cloze_text = $cloze_text;
    if ($this->cloze_type == CLOZE_TEXT) {
      $default_correctness = TRUE;
    } else {
      $default_correctness = FALSE;
    }
    preg_match_all("/" . preg_quote($this->start_tag) . "(.*?)" . preg_quote($this->end_tag) . "/", $cloze_text, $matches, PREG_PATTERN_ORDER);
    foreach ($matches[1] as $key => $value) {
      $cloze_words = split(",", $value);
      $answer_array = array();
      foreach ($cloze_words as $index => $text) {
        array_push($answer_array, new ASS_AnswerTrueFalse($text, 0, $index, $default_correctness));
      }
      array_push($this->gaps, $answer_array);
    }
  }

/**
* Returns the cloze text
*
* Returns the cloze text
*
* @return string The cloze text string
* @access public
* @see $cloze_text
*/
  function get_cloze_text() {
    return $this->cloze_text;
  }

/**
* Returns the start tag of a cloze gap
*
* Returns the start tag of a cloze gap
*
* @return string The start tag of a cloze gap
* @access public
* @see $start_tag
*/
  function get_start_tag() {
    return $this->start_tag;
  }

/**
* Returns the end tag of a cloze gap
*
* Returns the end tag of a cloze gap
*
* @return string The end tag of a cloze gap
* @access public
* @see $end_tag
*/
  function get_end_tag() {
    return $this->end_tag;
  }

/**
* Sets the start tag of a cloze gap
*
* Sets the start tag of a cloze gap
*
* @param string $start_tag The start tag for a cloze gap
* @access public
* @see $start_tag
*/
  function set_start_tag($start_tag = "*[") {
    $this->start_tag = $start_tag;
  }

/**
* Sets the end tag of a cloze gap
*
* Sets the end tag of a cloze gap
*
* @param string $end_tag The end tag for a cloze gap
* @access public
* @see $end_tag
*/
  function set_end_tag($end_tag = "*[") {
    $this->end_tag = $end_tag;
  }

/**
* Rebuilds the cloze text from the gaps array
*
* Rebuilds the cloze text from the gaps array
*
* @access public
* @see $cloze_text
*/
  function rebuild_cloze_text() {
    preg_match_all("/" . preg_quote($this->start_tag) . "(.*?)" . preg_quote($this->end_tag) . "/", $this->cloze_text, $matches, PREG_PATTERN_ORDER);
    foreach ($matches[1] as $key => $value) {
      $this->cloze_text = preg_replace("/$value/", $this->get_gap_text_list($key), $this->cloze_text);
    }
  }

/**
* Sets the cloze question type
*
* Sets the cloze question type (CLOZE_TEXT or CLOZE_SELECT)
*
* @param integer $cloze_type The cloze question type
* @access public
* @see $cloze_type
*/
  function set_cloze_type($cloze_type = CLOZE_TEXT) {
    $this->cloze_type = $cloze_type;
  }

/**
* Returns the cloze question type
*
* Returns the cloze question type (CLOZE_TEXT or CLOZE_SELECT)
*
* @return integer The cloze question type
* @access public
* @see $cloze_type
*/
  function get_cloze_type() {
    return $this->cloze_type;
  }

/**
* Returns an array of gap answers
*
* Returns the array of gap answers with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @return array Array of ASS_AnswerTrueFalse-Objects containing the gap gaps
* @access public
* @see $gaps
*/
  function get_gap($index = 0) {
    if ($index < 0) return array();
    if (count($this->gaps) < 1) return array();
    if ($index >= count($this->gaps)) return array();
    return $this->gaps[$index];
  }

/**
* Returns the number of gaps
*
* Returns the number of gaps
*
* @return integer The number of gaps in the question text
* @access public
* @see $gaps
*/
  function get_gap_count() {
    return count($this->gaps);
  }

/**
* Returns a separated string of all answers for a given text gap
*
* Returns a separated string of all answers for a given gap. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param string $separator A string that separates the answer strings
* @return string Separated string containing the answer strings
* @access public
* @see $gaps
*/
  function get_gap_text_list($index = 0, $separator = ",") {
    if ($index < 0) return "";
    if (count($this->gaps) < 1) return "";
    if ($index >= count($this->gaps)) return "";
    $result = array();
    foreach ($this->gaps[$index] as $key => $value) {
      array_push($result, $value->get_answertext());
    }
    return join($separator, $result);
  }

/**
* Deletes a gap
*
* Deletes a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @access public
* @see $gaps
*/
  function delete_gap($index = 0) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    $old_text = $this->get_gap_text_list($index);
    unset($this->gaps[$index]);
    $this->gaps = array_values($this->gaps);

    $this->cloze_text = preg_replace("/" . preg_quote($this->start_tag) . preg_quote($old_text) . preg_quote($this->end_tag) . "/", "", $this->cloze_text);
  }

/**
* Deletes all gaps without changing the cloze text
*
* Deletes all gaps without changing the cloze text
*
* @access public
* @see $gaps
*/
  function flush_gaps() {
    $this->gaps = array();
  }

/**
* Deletes an answer text of a gap
*
* Deletes an answer text of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param string $answertext The answer text that should be deleted
* @access public
* @see $gaps
*/
  function delete_answertext($index = 0, $answertext = "") {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    $old_text = $this->get_gap_text_list($index);
    $deleted = FALSE;
    foreach ($this->gaps[$index] as $key => $value) {
      if (strcmp($value->get_answertext(), $answertext) == 0) {
        if (count($this->gaps[$index]) == 1) {
          $this->delete_gap($index);
          $deleted = TRUE;
        } else {
          unset($this->gaps[$index][$key]);
        }
      }
    }
    if (!$deleted) {
      $this->gaps[$index] = array_values($this->gaps[$index]);
      $this->cloze_text = preg_replace("/" . preg_quote($this->start_tag) . preg_quote($old_text) . preg_quote($this->end_tag) . "/", "$this->start_tag" . $this->get_gap_text_list($index) . "$this->end_tag", $this->cloze_text);
    }
  }

/**
* Sets an answer text of a gap
*
* Sets an answer text of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param integer $answertext_index A nonnegative index of the n-th answertext
* @param string $answertext The answer text that should be deleted
* @access public
* @see $gaps
*/
  function set_answertext($index = 0, $answertext_index = 0, $answertext = "") {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    if ($answertext_index < 0) return;
    if (count($this->gaps[$index]) < 1) return;
    if ($answertext_index >= count($this->gaps[$index])) return;
    if (strlen($answertext) == 0) {
      // delete the answertext
      $this->delete_answertext($index, $this->gaps[$index][$answertext_index]->get_answertext());
    } else {
      $this->gaps[$index][$answertext_index]->set_answertext($answertext);
      $this->rebuild_cloze_text();
    }
  }

/**
* Sets the points of a gap
*
* Sets the points of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param double $points The points for the correct solution of the gap
* @access public
* @see $gaps
*/
  function set_gap_points($index = 0, $points = 0.0) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    foreach ($this->gaps[$index] as $key => $value) {
      $this->gaps[$index][$key]->set_points($points);
    }
  }

/**
* Sets the points of a gap answer
*
* Sets the points of a gap answer with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index_gaps A nonnegative index of the n-th gap
* @param integer $index_answerobject A nonnegative index of the n-th answer in the specified gap
* @param double $points The points for the correct solution of the answer
* @access public
* @see $gaps
*/
  function set_single_answer_points($index_gaps = 0, $index_answerobject = 0, $points = 0.0) {
    if ($index_gaps < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index_gaps >= count($this->gaps)) return;
    if ($index_answerobject < 0) return;
    if (count($this->gaps[$index_gaps]) < 1) return;
    if ($index_answerobject >= count($this->gaps[$index_gaps])) return;
    $this->gaps[$index_gaps][$index_answerobject]->set_points($points);
  }

/**
* Sets the correctness of a gap answer
*
* Sets the correctness of a gap answer with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index_gaps A nonnegative index of the n-th gap
* @param integer $index_answerobject A nonnegative index of the n-th answer in the specified gap
* @param boolean $correctness The correctness of the answer
* @access public
* @see $gaps
*/
  function set_single_answer_correctness($index_gaps = 0, $index_answerobject = 0, $correctness = FALSE) {
    if ($index_gaps < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index_gaps >= count($this->gaps)) return;
    if ($index_answerobject < 0) return;
    if (count($this->gaps[$index_gaps]) < 1) return;
    if ($index_answerobject >= count($this->gaps[$index_gaps])) return;
    $this->gaps[$index_gaps][$index_answerobject]->set_correctness($correctness);
  }

/**
* Moves the order of an answer object up one position
*
* Moves the order of an answer object up one position
*
* @param object $answer_object The instance of the object that should be moved up
* @access public
* @see $gaps
*/
  function answer_move_up ($answer_object) {
    // Alle Lücken untersuchen
    foreach ($this->gaps as $key => $value) {
      foreach ($this->gaps[$key] as $index => $object) {
        if ($object == $answer_object) {
          $position = $index;
          // Das Objekt wurde gefunden
          if ($position > 0) {
            $change_position_with = $this->gaps[$key][$position - 1];
            $change_position_with->set_order($change_position_with->get_order() + 1);
            $this->gaps[$key][$position - 1] = $this->gaps[$key][$position];
            $this->gaps[$key][$position - 1]->set_order($this->gaps[$key][$position - 1]->get_order() - 1);
            $this->gaps[$key][$position] = $change_position_with;
            $this->rebuild_cloze_text();
          }
        }
      }
    }
  }

/**
* Moves the order of an answer object down one position
*
* Moves the order of an answer object down one position
*
* @param object $answer_object The instance of the object that should be moved down
* @access public
* @see $gaps
*/
  function answer_move_down ($answer_object) {
    // Alle Lücken untersuchen
    foreach ($this->gaps as $key => $value) {
      foreach ($this->gaps[$key] as $index => $object) {
        if ($object == $answer_object) {
          $position = $index;
          // Das Objekt wurde gefunden
          if ($position < count($this->gaps[$key]) - 1) {
            $change_position_with = $this->gaps[$key][$position + 1];
            $change_position_with->set_order($change_position_with->get_order() - 1);
            $this->gaps[$key][$position + 1] = $this->gaps[$key][$position];
            $this->gaps[$key][$position + 1]->set_order($this->gaps[$key][$position + 1]->get_order() + 1);
            $this->gaps[$key][$position] = $change_position_with;
            $this->rebuild_cloze_text();
          }
        }
      }
    }
  }

/**
* Returns the points, a learner has reached answering the question
*
* Returns the points, a learner has reached answering the question
*
* @param integer $user_id The database ID of the learner
* @param integer $test_id The database Id of the test containing the question
* @access public
*/
  function get_reached_points($user_id, $test_id) {
    $found_value1 = array();
    $found_value2 = array();
    $query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->db->quote($user_id),
      $this->ilias->db->db->quote($test_id),
      $this->ilias->db->db->quote($this->get_id())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_value1, $data->value1);
      array_push($found_value2, $data->value2);
    }
    $points = 0;
    $counter = 0;
    foreach ($found_value1 as $key => $value) {
      if ($this->get_cloze_type() == CLOZE_TEXT) {
        foreach ($this->gaps[$value] as $k => $v) {
          if (strcmp($v->get_answertext(), $found_value2[$key]) == 0) {
            $points += $v->get_points();
          }
        }
      } else {
        if ($this->gaps[$value][$found_value2[$key]]->is_true()) {
          $points += $this->gaps[$value][$found_value2[$key]]->get_points();
        }
      }
    }
    return $points;
  }

/**
* Returns the evaluation data, a learner has entered to answer the question
*
* Returns the evaluation data, a learner has entered to answer the question
*
* @param integer $user_id The database ID of the learner
* @param integer $test_id The database Id of the test containing the question
* @access public
*/
  function get_reached_information($user_id, $test_id) {
    $found_value1 = array();
    $found_value2 = array();
    $query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->db->quote($user_id),
      $this->ilias->db->db->quote($test_id),
      $this->ilias->db->db->quote($this->get_id())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_value1, $data->value1);
      array_push($found_value2, $data->value2);
    }
    $counter = 1;
		$user_result = array();
    foreach ($found_value1 as $key => $value) {
      if ($this->get_cloze_type() == CLOZE_TEXT) {
        foreach ($this->gaps[$value] as $k => $v) {
					$solution = array(
						"gap" => "$counter",
						"points" => 0,
						"true" => 0,
						"value" => $v->get_answertext()
					);
          if (strcmp($v->get_answertext(), $found_value2[$key]) == 0) {
						$solution["points"] = $v->get_points();
						$solution["true"] = 1;
          }
        }
      } else {
				$solution = array(
					"gap" => "$counter",
					"points" => 0,
					"true" => 0,
					"value" => $this->gaps[$value][$found_value2[$key]]->get_answertext()
				);
        if ($this->gaps[$value][$found_value2[$key]]->is_true()) {
					$solution["points"] = $this->gaps[$value][$found_value2[$key]]->get_points();;
					$solution["true"] = 1;
        }
      }
			$counter++;
			array_push($user_result, $solution);
    }
    return $user_result;
  }

/**
* Returns the maximum points, a learner can reach answering the question
*
* Returns the maximum points, a learner can reach answering the question
*
* @access public
* @see $points
*/
  function get_maximum_points() {
    $points = 0;
    foreach ($this->gaps as $key => $value) {
      if ($this->get_cloze_type() == CLOZE_TEXT) {
        $points += $value[0]->get_points();
      } else {
        foreach ($value as $key2 => $value2) {
          if ($value2->is_true()) {
            $points += $value2->get_points();
          }
        }
      }
    }
    return $points;
  }

/**
* Saves the learners input of the question to the database
*
* Saves the learners input of the question to the database
*
* @param integer $test_id The database id of the test containing this question
* @access public
* @see $answers
*/
  function save_working_data($test_id, $limit_to = LIMIT_NO_LIMIT) {
    global $ilDB;
		global $ilUser;
    $db =& $ilDB->db;

    $query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $db->quote($ilUser->id),
      $db->quote($test_id),
      $db->quote($this->get_id())
    );
    $result = $db->query($query);

    foreach ($_POST as $key => $value) {
      if (preg_match("/gap_(\d+)/", $key, $matches)) {
        $query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
          $db->quote($this->get_id()),
          $db->quote($matches[1]),
          $db->quote($value)
        );
        $result = $db->query($query);
      }
    }
    //parent::save_working_data($limit_to);
  }
}

?>
