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
require_once "class.assAnswerOrdering.php";

/**
* Class for ordering questions
*
* ASS_OrderingQuestion is a class for ordering questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assOrderingQuestion.php
* @modulegroup   Assessment
*/
class ASS_OrderingQuestion extends ASS_Question {
/**
* The question text
*
* The question text of the ordering question.
*
* @var string
*/
  var $question;

/**
* The possible answers of the ordering question
*
* $answers is an array of the predefined answers of the ordering question
*
* @var array
*/
  var $answers;

/**
* Points for solving the ordering question
*
* Enter the number of points the user gets when he/she enters the correct order of the ordering
* question. This value overrides the point values of single answers when set different
* from zero.
*
* @var double
*/
  var $points;

/**
* ASS_OrderingQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_OrderingQuestion object.
*
* @param string $title A title string to describe the question
* @param string $comment A comment string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @param string $materials An uri to additional materials
* @param string $question The question string of the ordering test
* @param points double The points for solving the ordering question
* @access public
*/
  function ASS_OrderingQuestion (
    $title = "",
    $comment = "",
    $author = "",
    $owner = -1,
    $materials = "",
    $question = "",
    $points = 0.0
  )
  {
    $this->ASS_Question($title, $comment, $author, $owner, $materials);
    $this->answers = array();
    $this->question = $question;
    $this->points = $points;
  }

/**
* Saves a ASS_OrderingQuestion object to a database
*
* Saves a ASS_OrderingQuestion object to a database (experimental)
*
* @param object $db A pear DB object
* @access public
*/
  function save_to_db()
  {
    global $ilias;
    $db =& $ilias->db->db;

    if ($this->id == -1) {
      // Neuen Datensatz schreiben
      $id = $db->nextId('qpl_questions');
      $now = getdate();
      $question_type = 5;
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, ref_fi, title, comment, author, owner, question_text, points, materials, created, TIMESTAMP) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote($id),
        $db->quote($question_type),
        $db->quote($this->ref_id),
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->owner),
        $db->quote($this->question),
        $db->quote($this->points),
        $db->quote($this->materials),
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
      $query = sprintf("UPDATE qpl_questions SET title = %s, comment = %s, author = %s, question_text = %s, points = %s, materials = %s WHERE question_id = %s",
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->question),
        $db->quote($this->points),
        $db->quote($this->materials),
        $db->quote($this->id)
      );
      $result = $db->query($query);
    }

    if ($result == DB_OK) {
      // Antworten schreiben
      // alte Antworten löschen
      $query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
        $db->quote($this->id)
      );
      $result = $db->query($query);
      // Anworten wegschreiben
      foreach ($this->answers as $key => $value) {
        $answer_obj = $this->answers[$key];
        $query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, `order`, solution_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
          $db->quote($this->id),
          $db->quote($answer_obj->get_answertext()),
          $db->quote($answer_obj->get_points()),
          $db->quote($answer_obj->get_order()),
          $db->quote($answer_obj->get_solution_order())
        );
        $answer_result = $db->query($query);
      }
    }
  }

/**
* Loads a ASS_OrderingQuestion object from a database
*
* Loads a ASS_OrderingQuestion object from a database (experimental)
*
* @param object $db A pear DB object
* @param integer $question_id A unique key which defines the multiple choice test in the database
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
        $this->title = $data->title;
        $this->ref_id = $data->ref_fi;
        $this->comment = $data->comment;
        $this->author = $data->author;
        $this->owner = $data->owner;
        $this->question = $data->question_text;
        $this->points = $data->points;
        $this->materials = $data->materials;
      }
      $query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY `order` ASC",
        $db->quote($question_id)
      );
      $result = $db->query($query);
      if (strcmp(get_class($result), db_result) == 0) {
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          array_push($this->answers, new ASS_AnswerOrdering($data->answertext, $data->points, $data->order, $data->solution_order));
        }
      }
    }
  }

/**
* Sets the ordering question text
*
* Sets the ordering question text
*
* @param string $question The question text
* @access public
* @see $question
*/
  function set_question($question = "") {
    $this->question = $question;
  }

/**
* Returns the question text
*
* Returns the question text
*
* @return string The question text string
* @access public
* @see $question
*/
  function get_question() {
    return $this->question;
  }

/**
* Adds an answer for an ordering question
*
* Adds an answer for an ordering choice question. The students have to fill in an order for the answer.
* The answer is an ASS_AnswerOrdering object that will be created and assigned to the array $this->answers.
*
* @param string $answertext The answer text
* @param double $points The points for selecting the answer (even negative points can be used)
* @param integer $order A possible display order of the answer
* @param integer $solution_order An unique integer value representing the correct order of that answer in the solution of a question
* @access public
* @see $answers
* @see ASS_AnswerOrdering
*/
  function add_answer(
    $answertext = "",
    $points = 0.0,
    $order = 0,
    $solution_order = 0
  )
  {
    $found = -1;
    foreach ($this->answers as $key => $value) {
      if ($value->get_order() == $order) {
        $found = $order;
      }
    }
    if ($found >= 0) {
      // Antwort einfügen
      $answer = new ASS_AnswerOrdering($answertext, $points, $found, $solution_order);
      array_push($this->answers, $answer);
      for ($i = $found + 1; $i < count($this->answers); $i++) {
        $this->answers[$i] = $this->answers[$i-1];
      }
      $this->answers[$found] = $answer;
    } else {
      // Anwort anhängen
      $answer = new ASS_AnswerOrdering($answertext, $points, count($this->answers), $solution_order);
      array_push($this->answers, $answer);
    }
  }

/**
* Returns an ordering answer
*
* Returns an ordering answer with a given index. The index of the first
* answer is 0, the index of the second answer is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @return object ASS_AnswerOrdering-Object
* @access public
* @see $answers
*/
  function get_answer($index = 0) {
    if ($index < 0) return NULL;
    if (count($this->answers) < 1) return NULL;
    if ($index >= count($this->answers)) return NULL;
    return $this->answers[$index];
  }

/**
* Deletes an answer
*
* Deletes an answer with a given index. The index of the first
* answer is 0, the index of the second answer is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @access public
* @see $answers
*/
  function delete_answer($index = 0) {
    if ($index < 0) return;
    if (count($this->answers) < 1) return;
    if ($index >= count($this->answers)) return;
    unset($this->answers[$index]);
    $this->answers = array_values($this->answers);
    for ($i = 0; $i < count($this->answers); $i++) {
      if ($this->answers[$i]->get_order() > $index) {
        $this->answers[$i]->set_order($i);
      }
    }
  }

/**
* Deletes all answers
* 
* Deletes all answers
*
* @access public
* @see $answers
*/
  function flush_answers() {
    $this->answers = array();
  }
  
/**
* Returns the number of answers
*
* Returns the number of answers
*
* @return integer The number of answers of the ordering question
* @access public
* @see $answers
*/
  function get_answer_count() {
    return count($this->answers);
  }

/**
* Gets the points
*
* Gets the points for entering the correct order of the ASS_OrderingQuestion object
*
* @return double The points for entering the correct order of the ordering question
* @access public
* @see $points
*/
  function get_points() {
    return $this->points;
  }

/**
* Sets the points
*
* Sets the points for entering the correct order of the ASS_OrderingQuestion object
*
* @param points double The points for entering the correct order of the ordering question
* @access public
* @see $points
*/
  function set_points($points = 0.0) {
    $this->points = $points;
  }

/**
* Returns the maximum solution order
*
* Returns the maximum solution order of all ordering answers
*
* @return integer The maximum solution order of all ordering answers
* @access public
* @see $points
*/
  function get_max_solution_order() {
    if (count($this->answers) == 0) {
      $max = 0;
    } else {
      $max = $this->answers[0]->get_solution_order();
    }
    foreach ($this->answers as $key => $value) {
      if ($value->get_solution_order() > $max) {
        $max = $value->get_solution_order();
      }
    }
    return $max;
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
    $query = sprintf("SELECT * FROM dum_assessment_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
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
      if ($this->answers[$value]->get_solution_order() == $found_value2[$key]) {
        $counter++;
      }
    }
    if (count($this->answers) == $counter) {
      $points = $this->points;
    }
    return $points;
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
    return $this->points;
  }

/**
* Saves the learners input of the question to the database
* 
* Saves the learners input of the question to the database
*
* @access public
* @see $answers
*/
  function save_working_data($limit_to = LIMIT_NO_LIMIT) {
    global $ilias;
    $db =& $ilias->db->db;
    
    $query = sprintf("DELETE FROM dum_assessment_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $db->quote($this->ilias->account->id),
      $db->quote($_GET["test"]),
      $db->quote($this->get_id())
    );
    $result = $db->query($query);
    
    foreach ($_POST as $key => $value) {
      if (preg_match("/order_(\d+)/", $key, $matches)) {
        $query = sprintf("INSERT INTO dum_assessment_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
          $db->quote($this->ilias->account->id),
          $db->quote($_GET["test"]),
          $db->quote($this->get_id()),
          $db->quote($matches[1]),
          $db->quote($value)
        );
        $result = $db->query($query);
      }
    }
    parent::save_working_data($limit_to);
  }
}


?>