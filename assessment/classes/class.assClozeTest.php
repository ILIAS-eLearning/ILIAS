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
require_once "class.assAnswerCloze.php";

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
    $cloze_text = ""
  )
  {
    $this->start_tag = "<gap>";
    $this->end_tag = "</gap>";
    $this->ASS_Question($title, $comment, $author, $owner);
    $this->gaps = array();
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
  function saveToDb()
  {
    global $ilias;
    $db =& $ilias->db;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
    $estw_time = $this->getEstimatedWorkingTime();
    $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
		$shuffle = 1;
		if (!$this->shuffle)
			$shuffle = 0;

    if ($this->id == -1) {
      // Neuen Datensatz schreiben
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
        $query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, ref_fi, title, comment, author, owner, question_text, working_time, shuffle, complete, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote(3),
        $db->quote($this->ref_id),
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->owner),
        $db->quote($this->cloze_text),
        $db->quote($estw_time),
        $db->quote("$this->shuffle"),
				$db->quote("$complete"),
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->id = $this->ilias->db->getLastInsertId();
        // Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
        if ($this->getTestId() > 0) {
          $this->insertIntoTest($this->getTestId());
        }
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE qpl_questions SET title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, shuffle = %s, complete = %s WHERE question_id = %s",
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->cloze_text),
        $db->quote($estw_time),
				$db->quote("$this->shuffle"),
				$db->quote("$complete"),
        $db->quote($this->id)
      );
      $result = $db->query($query);
    }

    if ($result == DB_OK) {
      // saving material uris in the database
      $this->saveMaterialsToDb();

      // Antworten schreiben

      // alte Antworten löschen
      $query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
        $db->quote($this->id)
      );
      $result = $db->query($query);
      // Anworten wegschreiben
      foreach ($this->gaps as $key => $value) {
        foreach ($value as $answer_id => $answer_obj) {
          $query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, name, shuffle, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
            $db->quote($this->id),
            $db->quote($key),
            $db->quote($answer_obj->get_answertext()),
            $db->quote($answer_obj->get_points()),
            $db->quote($answer_obj->get_order()),
						$db->quote($answer_obj->get_cloze_type()),
						$db->quote($answer_obj->get_name()),
						$db->quote($answer_obj->get_shuffle()),
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
  function loadFromDb($question_id)
  {
    global $ilias;
    $db =& $ilias->db;

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
				$this->shuffle = $data->shuffle;
        $this->setEstimatedWorkingTiem(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
      }
      // loads materials uris from database
      $this->loadMaterialFromDb($question_id);

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
          array_push($this->gaps[$counter], new ASS_AnswerCloze($data->answertext, $data->points, $data->aorder, $data->correctness, $data->cloze_type, $data->name, $data->shuffle));
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
    preg_match_all("/" . "<gap(.*?)>" . "(.*?)" . preg_quote($this->end_tag, "/") . "/", $cloze_text, $matches, PREG_PATTERN_ORDER);
    foreach ($matches[2] as $key => $value) {
      $cloze_words = split(",", $value);
      $answer_array = array();
			$name = "";
			if (preg_match("/name\=\"(.*?)\"/", $matches[1][$key], $param))
			{
				// name param
				$name = $param[1];
			}
			$type = "text";
			if (preg_match("/type\=\"(.*?)\"/", $matches[1][$key], $param))
			{
				// name param
				$type = $param[1];
			}
			$shuffle = "yes";
			if (preg_match("/shuffle\=\"(.*?)\"/", $matches[1][$key], $param))
			{
				// name param
				$shuffle = $param[1];
			}
			if (strcmp(strtolower($type), "select") == 0)
			{
				$type = CLOZE_SELECT;
			}
				else
			{
				$type = CLOZE_TEXT;
			}
			if (strcmp(strtolower($shuffle), "no") == 0)
			{
				$shuffle = 0;
			}
				else
			{
				$shuffle = 1;
			}
			if ($type == CLOZE_TEXT) {
				$default_correctness = TRUE;
			} else {
				$default_correctness = FALSE;
			}
      foreach ($cloze_words as $index => $text) {
        array_push($answer_array, new ASS_AnswerCloze($text, 0, $index, $default_correctness, $type, $name, $shuffle));
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
  function set_start_tag($start_tag = "<gap>") {
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
  function set_end_tag($end_tag = "</gap>") {
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
    preg_match_all("/" . "<gap.*?>(.*?)" . preg_quote($this->end_tag, "/") . "/", $this->cloze_text, $matches, PREG_PATTERN_ORDER);
    foreach ($matches[1] as $key => $value) {
      $this->cloze_text = preg_replace("/$value/", $this->get_gap_text_list($key), $this->cloze_text);
    }
  }

/**
* Returns an array of gap answers
*
* Returns the array of gap answers with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @return array Array of ASS_AnswerCloze-Objects containing the gap gaps
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
* Returns a count of all answers of a gap
*
* Returns a count of all answers of a gap
*
* @param integer $index A nonnegative index of the n-th gap
* @access public
* @see $gaps
*/
  function get_gap_text_count($index = 0) {
    if ($index < 0) return 0;
    if (count($this->gaps) < 1) return 0;
    if ($index >= count($this->gaps)) return 0;
    return count($this->gaps[$index]);
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

    $this->cloze_text = preg_replace("/" . "<gap.*?>" . preg_quote($old_text, "/") . preg_quote($this->end_tag, "/") . "/", "", $this->cloze_text);
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
  function delete_answertext_by_index($gap_index = 0, $answertext_index = 0) {
    if ($gap_index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($gap_index >= count($this->gaps)) return;
    $old_text = $this->get_gap_text_list($gap_index);
		if (count($this->gaps[$gap_index]) == 1) {
			$this->delete_gap($gap_index);
		} else {
			unset($this->gaps[$gap_index][$answertext_index]);
      $this->gaps[$gap_index] = array_values($this->gaps[$gap_index]);
			$gap_params = "";
			if (preg_match("/" . "<gap(.*?)>" . preg_quote($old_text, "/") . preg_quote($this->end_tag, "/") . "/", $this->cloze_text, $matches))
			{
				$gap_params = $matches[1];
			}
      $this->cloze_text = preg_replace("/" . "<gap.*?>" . preg_quote($old_text, "/") . preg_quote($this->end_tag, "/") . "/", "<gap" . $gap_params . ">" . $this->get_gap_text_list($gap_index) . "$this->end_tag", $this->cloze_text);
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
  function set_answertext($index = 0, $answertext_index = 0, $answertext = "", $add_gaptext=0) {
    if ($add_gaptext == 1)    {
    	$arr = $this->gaps[$index][0];
    	if (strlen($this->gaps[$index][count($this->gaps[$index])-1]->get_answertext()) != 0) {
    		array_push($this->gaps[$index], new ASS_AnswerCloze($answertext, 0, count($this->gaps[$index]),
    			$arr->get_correctness(), $arr->get_cloze_type(),
    			$arr->get_name(), $arr->get_shuffle()));
    		$this->rebuild_cloze_text();
    	}
    	return;
    }
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
* Updates the cloze text setting the cloze type for every gap
*
* Updates the cloze text setting the cloze type for every gap
*
* @access public
* @see $cloze_text
*/
	function update_all_gap_params() {
		global $lng;

		for ($i = 0; $i < $this->get_gap_count(); $i++)
		{
	    $gaptext = $this->get_gap_text_list($i);
			if ($this->gaps[$i][0]->get_cloze_type() == CLOZE_TEXT)
			{
				$strType = "text";
			}
				else
			{
				$strType = "select";
			}
			if ($this->gaps[$i][0]->get_shuffle() == 0)
			{
				$shuffle = "no";
			}
				else
			{
				$shuffle = "yes";
			}
			if (preg_match("/" . "<gap[^<]*?" . "type\=\"[^\"]+\"" . "[^>]*?>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", $this->cloze_text)) {
				// change the type attribute
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)" . "type\=\"[^\"]+\"" . "([^<]*?)>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", "<gap\$1type=\"$strType\"\$2>$gaptext</gap>", $this->cloze_text);
			} else {
				// create a type attribute
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)>" . preg_quote($gaptext, "/") . "/", "<gap type=\"$strType\"\$1>$gaptext", $this->cloze_text);
			}
			if ($this->gaps[$i][0]->get_cloze_type() == CLOZE_SELECT) {
				if (preg_match("/" . "<gap[^<]*?" . "shuffle\=\"[^\"]+\"" . "[^>]*?>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", $this->cloze_text)) {
					// change the shuffle attribute
					$this->cloze_text = preg_replace("/" . "<gap([^<]*?)" . "shuffle\=\"[^\"]+\"" . "([^>]*?)>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", "<gap\$1shuffle=\"$shuffle\"\$2>$gaptext</gap>", $this->cloze_text);
				} else {
					// create a shuffle attribute
					$this->cloze_text = preg_replace("/" . "<gap([^<]*?)>" . preg_quote($gaptext, "/") . "/", "<gap shuffle=\"$shuffle\"\$1>$gaptext", $this->cloze_text);
				}
			}
				else
			{
				// remove the shuffle attribute
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)" . "shuffle\=\"[^\"]+\"" . "([^>]*?)>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", "<gap\$1\$2>$gaptext</gap>", $this->cloze_text);
			}
			if (!preg_match("/" . "<gap[^<]*?" . "name\=\"[^\"]+\"" . "[^>]*?>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", $this->cloze_text)) {
				// create a name attribute
				$name = $this->gaps[$i][0]->get_name();
				if (!$name)
				{
					$name = $lng->txt("gap") . " " . ($i+1);
				}
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)>" . preg_quote($gaptext, "/") . "/", "<gap name=\"" . $name . "\"\$1>$gaptext", $this->cloze_text);
			}
		}
	}

/**
* Sets the cloze type of the gap
*
* Sets the cloze type of the gap
*
* @param integer $index The index of the chosen gap
* @param integer $cloze_type The cloze type of the gap
* @access public
* @see $gaps
*/
	function set_cloze_type($index, $cloze_type = CLOZE_TEXT) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
		if ($this->gaps[$index][0]->get_cloze_type() != $cloze_type) {
			// change all answer objects
			foreach ($this->gaps[$index] as $key => $value) {
				$this->gaps[$index][$key]->set_cloze_type($cloze_type);
				$this->gaps[$index][$key]->set_points(0);
				if ($cloze_type == CLOZE_TEXT) {
					$this->gaps[$index][$key]->set_correctness(TRUE);
				} else {
					$this->gaps[$index][$key]->set_correctness(FALSE);
				}
			}
			// change/add the <gap> attribute
	    $gaptext = $this->get_gap_text_list($index);
			if ($cloze_type == CLOZE_TEXT)
			{
				$strType = "text";
				$strOldType = "select";
			}
				else
			{
				$strType = "select";
				$strOldType = "text";
			}
			if (preg_match("/" . "<gap[^<]*?" . preg_quote("type=\"$strOldType\"") . ".*?>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", $this->cloze_text)) {
				// change the type attribute
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)" . preg_quote("type=\"$strOldType\"") . "([^<]*?)>" . preg_quote($gaptext, "/") . preg_quote($this->end_tag, "/") . "/", "<gap\$1type=\"$strType\"\$2>$gaptext</gap>", $this->cloze_text);
			} else {
				// create a type attribute
				$this->cloze_text = preg_replace("/" . "<gap([^<]*?)>" . preg_quote($gaptext, "/") . "/", "<gap type=\"$strType\"\$1>$gaptext", $this->cloze_text);
			}
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
* Sets the shuffle state of a gap
*
* Sets the shuffle state of a gap with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th gap
* @param integer $shuffle Turn shuffle on (=1) or off (=0)
* @access public
* @see $gaps
*/
  function set_gap_shuffle($index = 0, $shuffle = 1) {
    if ($index < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index >= count($this->gaps)) return;
    foreach ($this->gaps[$index] as $key => $value) {
      $this->gaps[$index][$key]->set_shuffle($shuffle);
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
* Returns the points, a learner has reached answering the question
*
* Returns the points, a learner has reached answering the question
*
* @param integer $user_id The database ID of the learner
* @param integer $test_id The database Id of the test containing the question
* @access public
*/
  function getReachedPoints($user_id, $test_id) {
    $found_value1 = array();
    $found_value2 = array();
    $query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->quote($user_id),
      $this->ilias->db->quote($test_id),
      $this->ilias->db->quote($this->getId())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_value1, $data->value1);
      array_push($found_value2, $data->value2);
    }
    $points = 0;
    $counter = 0;
    foreach ($found_value1 as $key => $value) {
      if ($this->gaps[$value][0]->get_cloze_type() == CLOZE_TEXT) {
        foreach ($this->gaps[$value] as $k => $v) {
          if (strcmp($v->get_answertext(), $found_value2[$key]) == 0) {
            $points += $v->get_points();
          }
        }
      } else {
        if (($this->gaps[$value][$found_value2[$key]])&&($this->gaps[$value][$found_value2[$key]]->is_true())) {
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
      $this->ilias->db->quote($user_id),
      $this->ilias->db->quote($test_id),
      $this->ilias->db->quote($this->getId())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_value1, $data->value1);
      array_push($found_value2, $data->value2);
    }
    $counter = 1;
		$user_result = array();
    foreach ($found_value1 as $key => $value) {
      if ($this->gaps[$value][0]->get_cloze_type() == CLOZE_TEXT) {
				$solution = array(
					"gap" => "$counter",
					"points" => 0,
					"true" => 0,
					"value" => $found_value2[$key]
				);
        foreach ($this->gaps[$value] as $k => $v) {
          if (strcmp($v->get_answertext(), $found_value2[$key]) == 0) {
						$solution = array(
							"gap" => "$counter",
							"points" => $v->get_points(),
							"true" => 1,
							"value" => $found_value2[$key]
						);
          }
        }
      } else {
				$solution = array(
					"gap" => "$counter",
					"points" => 0,
					"true" => 0,
					"value" => $found_value2[$key]
				);
        if ($this->gaps[$value][$found_value1[$key]]->is_true()) {
					$solution["points"] = $this->gaps[$value][$found_value1[$key]]->get_points();
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
  function getMaximumPoints() {
    $points = 0;
    foreach ($this->gaps as $key => $value) {
      if ($value[0]->get_cloze_type() == CLOZE_TEXT) {
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
  function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT) {
    global $ilDB;
		global $ilUser;
    $db =& $ilDB->db;

    $query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $db->quote($ilUser->id),
      $db->quote($test_id),
      $db->quote($this->getId())
    );
    $result = $db->query($query);

    foreach ($_POST as $key => $value) {
      if (preg_match("/gap_(\d+)/", $key, $matches)) {
        $query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
          $db->quote($this->getId()),
          $db->quote($matches[1]),
          $db->quote($value)
        );
        $result = $db->query($query);
      }
    }
    //parent::saveWorkingData($limit_to);
  }
}

?>
