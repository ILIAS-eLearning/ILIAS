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

require_once "PEAR.php";
require_once "assessment/classes/class.assMultipleChoice.php";
require_once "assessment/classes/class.assClozeTest.php";
require_once "assessment/classes/class.assMatchingQuestion.php";
require_once "assessment/classes/class.assOrderingQuestion.php";

/**
* GUI Handler class for every assessment question type
* 
* The ASS_QuestionGUI class defines and encapsulates basic methods and attributes 
* communicating between Ilias3 assessment objects and the Ilias3 GUI
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_QuestionGUI extends PEAR {
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
* An alias to an assessment question object
*
* An alias to an assessment question object
*
* @var object
*/
  var $question;
  
/**
* ASS_QuestionGUI constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_QuestionGUI object.
*
* @access public
*/
  function ASS_QuestionGUI() 
  {
		global $ilias;
    global $lng;
    global $tpl;

		$this->ilias =& $ilias;
    $this->lng =& $lng;
    $this->tpl =& $tpl;
  }
  
/**
* Creates a question
* 
* Creates a question and returns the alias to the question
*
* @param string $question_type The question type as it is used in the language database
* @param integer $question_id The database ID of an existing question to load it into ASS_QuestionGUI
* @return object The alias to the question object
* @access public
*/
  function &create_question($question_type, $question_id = -1) {
    if ((!$question_type) and ($question_id > 0)) {
      $question_type = $this->get_question_type_from_db($question_id);
    }
    switch ($question_type) {
      case "qt_multiple_choice_sr":
        $this->question =& new ASS_MultipleChoice();
        $this->question->set_response(RESPONSE_SINGLE);
        break;
      case "qt_multiple_choice_mr":
        $this->question =& new ASS_MultipleChoice();
        $this->question->set_response(RESPONSE_MULTIPLE);
        break;
      case "qt_cloze":
        $this->question =& new ASS_ClozeTest();
        break;
      case "qt_matching":
        $this->question =& new ASS_MatchingQuestion();
        break;
      case "qt_ordering":
        $this->question =& new ASS_OrderingQuestion();
        break;
    }
    if ($question_id > 0) {
      $this->question->load_from_db($question_id);
    }
    return $this->question;
  }

  function get_question_type_from_db($question_id) {
    $query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_question_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $this->ilias->db->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
    $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $data->type_tag;
  }

/**
* Returns the question type as it is used in the language database
* 
* Returns the question type as it is used in the language database
*
* @return string The question type as it is used in the language database
* @access public
*/
  function get_question_type() {
    switch (get_class($this->question)) {
      case "ass_multiplechoice":
        if ($this->question->get_response() == RESPONSE_SINGLE) {
          return "qt_multiple_choice_sr";
        } else {
          return "qt_multiple_choice_mr";
        }
        break;
      case "ass_clozetest":
        return "qt_cloze";
        break;
      case "ass_orderingquestion":
        return "qt_ordering";
        break;
      case "ass_matchingquestion":
        return "qt_matching";
        break;
    }
  }
  
/**
* Cancels actions editing this question
* 
* Cancels actions editing this question
*
* @access private
*/
  function cancel_action() {
    header("location:" . $_SERVER["PHP_SELF"] . $this->get_add_parameter());
  }

  function get_add_parameter() {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }

/**
* Sets the fields of a multiple choice create/edit form
* 
* Sets the fields of a multiple choice create/edit form
*
* @access private
*/
  function out_multiple_choice_data() {
    if ($this->question->get_response() == RESPONSE_SINGLE) {
      $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_mc_sr.html", true);
  
      // output of existing single response answers
      for ($i = 0; $i < $this->question->get_answer_count(); $i++) {
        $this->tpl->setCurrentBlock("deletebutton");
        $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
        $this->tpl->setVariable("ANSWER_ORDER", $i);
        $this->tpl->parseCurrentBlock();
        $this->tpl->setCurrentBlock("answers");
        $answer = $this->question->get_answer($i);
        $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $answer->get_order() + 1);
        $this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
        $this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
        $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
        $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
        $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", sprintf("%d", $answer->get_points()));
        $this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
        if ($answer->is_true()) {
          $this->tpl->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
        }
        $this->tpl->parseCurrentBlock();
      }
        
      if (strlen($_POST["cmd"]["add"]) > 0) {
        // Create template for a new answer
        $this->tpl->setCurrentBlock("answers");
        $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->question->get_answer_count() + 1);
        $this->tpl->setVariable("ANSWER_ORDER", $this->question->get_answer_count());
        $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
        $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
        $this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
        $this->tpl->parseCurrentBlock();
      }
        
      $this->tpl->setCurrentBlock("question_data");
   
      $this->tpl->setVariable("MULTIPLE_CHOICE_ID", $this->question->get_id());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_TITLE", $this->question->get_title());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_COMMENT", $this->question->get_comment());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_AUTHOR", $this->question->get_author());
      $this->tpl->setVariable("VALUE_QUESTION", $this->question->get_question());
      $this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
      $this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
      $this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
      $this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
      $this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
      $this->tpl->setVariable("SAVE",$this->lng->txt("save"));
      $this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
      $this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
      $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
      $this->tpl->setVariable("ACTION_MULTIPLE_CHOICE_TEST", $_SERVER["PHP_SELF"] . $this->get_add_parameter() . "&sel_question_types=qt_multiple_choice_sr");
      $this->tpl->parseCurrentBlock();
    } else {
      $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_mc_mr.html", true);
    
      // output of existing multiple response answers
      for ($i = 0; $i < $this->question->get_answer_count(); $i++) {
        $this->tpl->setCurrentBlock("deletebutton");
        $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
        $this->tpl->setVariable("ANSWER_ORDER", $i);
        $this->tpl->parseCurrentBlock();
        $this->tpl->setCurrentBlock("answers");
        $answer = $this->question->get_answer($i);
        $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
        $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $answer->get_order() + 1);
        $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", sprintf("%d", $answer->get_points()));
        $this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
        $this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
        $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
        $this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
        if ($answer->is_true()) {
          $this->tpl->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
        }
        $this->tpl->parseCurrentBlock();
      }
        
      if (strlen($_POST["cmd"]["add"]) > 0) {
        // Create template for a new answer
        $this->tpl->setCurrentBlock("answers");
        $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
        $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->question->get_answer_count() + 1);
        $this->tpl->setVariable("ANSWER_ORDER", $this->question->get_answer_count());
        $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
        $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
        $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS", "0");
        $this->tpl->setVariable("VALUE_TRUE", $this->lng->txt("true"));
        $this->tpl->parseCurrentBlock();
      }
        
      $this->tpl->setCurrentBlock("question_data");
    
      $this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
      $this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
      $this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
      $this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
      $this->tpl->setVariable("MULTIPLE_CHOICE_ID", $this->question->get_id());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_TITLE", $this->question->get_title());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_COMMENT", $this->question->get_comment());
      $this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_AUTHOR", $this->question->get_author());
      $this->tpl->setVariable("VALUE_QUESTION", $this->question->get_question());
      $this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
      $this->tpl->setVariable("SAVE",$this->lng->txt("save"));
      $this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
      $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
      $this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
      $this->tpl->setVariable("ACTION_MULTIPLE_CHOICE_TEST", $_SERVER["PHP_SELF"] . $this->get_add_parameter() . "&sel_question_types=qt_multiple_choice_mr");
      $this->tpl->parseCurrentBlock();
    }
  }
  
/**
* Sets the fields of a cloze question create/edit form
* 
* Sets the fields of a cloze question create/edit form
*
* @access private
*/
  function out_cloze_question_data() {
    if ($this->question->get_cloze_type() == CLOZE_TEXT) {
      $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_cloze_text.html", true);
    } else {
      $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_cloze_select.html", true);
    }
    if ($this->question->get_cloze_type() == CLOZE_TEXT)
    {
      for ($i = 0; $i < $this->question->get_gap_count(); $i++)
      {
        $this->tpl->setCurrentBlock("textgap_value");
        $gap_text = $this->question->get_gap($i);
        foreach ($gap_text as $key => $value) {
          $this->tpl->setVariable("VALUE_TEXT_GAP", $value->get_answertext());
          $this->tpl->setVariable("TEXT_POSSIBLE_GAP_TEXT", $this->lng->txt("possible_gap_text"));
          $this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
          $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
          $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock("textgap");
        $this->tpl->setVariable("TEXT_TEXT_GAP", $this->lng->txt("text_gap"));
        $this->tpl->setVariable("VALUE_GAP_COUNTER", $i+1);
        $answer_array = $this->question->get_gap($i);
        $answer_points = $answer_array[0]->get_points();
        $this->tpl->setVariable("VALUE_TEXT_GAP_POINTS", sprintf("%d", $answer_points));
        $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
        $this->tpl->parseCurrentBlock();
      }
    } elseif ($this->question->get_cloze_type() == CLOZE_SELECT) {
      for ($i = 0; $i < $this->question->get_gap_count(); $i++)
      {
        $this->tpl->setCurrentBlock("selectgap_value");
        $gap_text = $this->question->get_gap($i);
        foreach ($gap_text as $key => $value) {
          $this->tpl->setVariable("TEXT_GAP_SELECTION", $this->lng->txt("gap_selection"));
          $this->tpl->setVariable("VALUE_SELECT_GAP", $value->get_answertext());
          $this->tpl->setVariable("VALUE_GAP_COUNTER", "$i" . "_" . "$key");
          $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
          $this->tpl->setVariable("IMG_UP", ilUtil::getImagePath("up.gif", true));
          $this->tpl->setVariable("IMG_DOWN", ilUtil::getImagePath("down.gif", true));
          $this->tpl->setVariable("VALUE_SELECT_GAP_ORDER", sprintf("%d", $value->get_order()));
          $this->tpl->setVariable("VALUE_GAP", $i);
          $this->tpl->setVariable("TEXT_TRUE", $this->lng->txt("true"));
          $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
          $this->tpl->setVariable("VALUE_INDEX", $key);
          $this->tpl->setVariable("VALUE_SELECT_GAP_POINTS", sprintf("%d", $value->get_points()));
          if ($value->is_true()) {
            $this->tpl->setVariable("SELECTED_CORRECTNESS_TRUE", " checked=\"checked\"");
          }
          $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock("selectgap");
        $this->tpl->setVariable("TEXT_SELECT_GAP", $this->lng->txt("select_gap"));
        $this->tpl->setVariable("VALUE_GAP_COUNTER", $i+1);
        $this->tpl->parseCurrentBlock();
      }
    }

    $this->tpl->setCurrentBlock("question_data");
    $this->tpl->setVariable("VALUE_CLOZE_TITLE", $this->question->get_title());
    $this->tpl->setVariable("VALUE_CLOZE_COMMENT", $this->question->get_comment());
    $this->tpl->setVariable("VALUE_CLOZE_AUTHOR", $this->question->get_author());
    $this->tpl->setVariable("VALUE_CLOZE_TEXT", $this->question->get_cloze_text());
    $this->tpl->setVariable("TEXT_CREATE_GAPS", $this->lng->txt("create_gaps"));
    $this->tpl->setVariable("CLOZE_ID", $this->question->get_id());
    if ($this->question->get_cloze_type() == CLOZE_SELECT)
    {
      $this->tpl->setVariable("SELECTED_SELECT_GAP", " selected=\"selected\"");
    } else
    {
      $this->tpl->setVariable("SELECTED_TEXT_GAP", " selected=\"selected\"");
    }
    $this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
    $this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
    $this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
    $this->tpl->setVariable("TEXT_CLOZE_TEXT", $this->lng->txt("cloze_text"));
    $this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("type"));
    $this->tpl->setVariable("TEXT_TEXT_GAP", $this->lng->txt("text_gap"));
    $this->tpl->setVariable("TEXT_SELECT_GAP", $this->lng->txt("select_gap"));
    $this->tpl->setVariable("SAVE",$this->lng->txt("save"));
    $this->tpl->setVariable("APPLY","Apply");
    $this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
    $this->tpl->setVariable("ACTION_CLOZE_TEST", $_SERVER["PHP_SELF"] . $this->get_add_parameter() . "&sel_question_types=qt_cloze");
    $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    $this->tpl->parseCurrentBlock();
  }

/**
* Sets the fields of an ordering question create/edit form
* 
* Sets the fields of an ordering question create/edit form
*
* @access private
*/
  function out_ordering_question_data() {
    $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_ordering.html", true);

    // Output of existing answers
    for ($i = 0; $i < $this->question->get_answer_count(); $i++) {
      $this->tpl->setCurrentBlock("deletebutton");
      $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
      $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
      $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
      $this->tpl->setVariable("ANSWER_ORDER", $i);
      $this->tpl->parseCurrentBlock();
      $this->tpl->setCurrentBlock("answers");
      $answer = $this->question->get_answer($i);
      $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $answer->get_order() + 1);
      $this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
      $this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
      $this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
      $this->tpl->setVariable("VALUE_ORDER", $answer->get_solution_order());
      $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
      $this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", $answer->get_points()));
      $this->tpl->parseCurrentBlock();
    }

    if (strlen($_POST["cmd"]["add"]) > 0) {
      // Create an empty answer
      $this->tpl->setCurrentBlock("answers");
      $this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
      $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
      $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->question->get_answer_count() + 1);
      $this->tpl->setVariable("ANSWER_ORDER", $this->question->get_answer_count());
      $this->tpl->setVariable("TEXT_SOLUTION_ORDER", $this->lng->txt("solution_order"));
      $this->tpl->setVariable("VALUE_ORDER", $this->question->get_max_solution_order() + 1);
      $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
      $this->tpl->setVariable("VALUE_ORDERING_POINTS", sprintf("%d", 0));
      $this->tpl->parseCurrentBlock();
    }

    $this->tpl->setCurrentBlock("question_data");

    $this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
    $this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
    $this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
    $this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
    $this->tpl->setVariable("ORDERING_ID", $this->question->get_id());
    $this->tpl->setVariable("VALUE_ORDERING_TITLE", $this->question->get_title());
    $this->tpl->setVariable("VALUE_ORDERING_COMMENT", $this->question->get_comment());
    $this->tpl->setVariable("VALUE_ORDERING_AUTHOR", $this->question->get_author());
    $this->tpl->setVariable("VALUE_QUESTION", $this->question->get_question());
    $this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_answer"));
    $this->tpl->setVariable("SAVE", $this->lng->txt("save"));
    $this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
    $this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
    $this->tpl->setVariable("ACTION_ORDERING_QUESTION", $_SERVER["PHP_SELF"] . $this->get_add_parameter() . "&sel_question_types=qt_ordering");
    $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    $this->tpl->parseCurrentBlock();
  }

/**
* Sets the fields of a matching question create/edit form
* 
* Sets the fields of a matching question create/edit form
*
* @access private
*/
  function out_matching_question_data() {
    $this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_matching.html", true);

    // Vorhandene Anworten ausgeben
    for ($i = 0; $i < $this->question->get_matchingpair_count(); $i++) {
      $this->tpl->setCurrentBlock("deletebutton");
      $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
      $this->tpl->setVariable("ANSWER_ORDER", $i);
      $this->tpl->parseCurrentBlock();
      $this->tpl->setCurrentBlock("answers");
      $thispair = $this->question->get_matchingpair($i);
      $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $i + 1);
      $this->tpl->setVariable("ANSWER_ID", $thispair->get_order());
      $this->tpl->setVariable("ANSWER_ORDER", $i);
      $this->tpl->setVariable("VALUE_LEFT", $thispair->get_answertext());
      $this->tpl->setVariable("MATCHING_ID", $thispair->get_matchingtext_order());
      $this->tpl->setVariable("VALUE_RIGHT", $thispair->get_matchingtext());
      $this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
      $this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
      $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
      $this->tpl->setVariable("VALUE_MATCHING_POINTS", sprintf("%d", $thispair->get_points()));
      $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
      $this->tpl->parseCurrentBlock();
    }

    if (strlen($_POST["cmd"]["add"]) > 0) {
      // Template für neue Antwort erzeugen
      $this->tpl->setCurrentBlock("answers");
      $this->tpl->setVariable("VALUE_ANSWER_COUNTER", $this->question->get_matchingpair_count() + 1);
      $this->tpl->setVariable("MATCHING_ID", $this->question->get_random_id("matching"));
      $this->tpl->setVariable("ANSWER_ID", $this->question->get_random_id("answer"));
      $this->tpl->setVariable("ANSWER_ORDER", $this->question->get_matchingpair_count());
      $this->tpl->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
      $this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
      $this->tpl->setVariable("VALUE_MATCHING_POINTS", sprintf("%d", 0));
      $this->tpl->setVariable("TEXT_MATCHING_PAIR", $this->lng->txt("matching_pair"));
      $this->tpl->setVariable("TEXT_ANSWER", $this->lng->txt("answer"));
      $this->tpl->parseCurrentBlock();
    }

    $this->tpl->setCurrentBlock("question_data");
    $this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
    $this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
    $this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
    $this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
    $this->tpl->setVariable("MATCHING_ID", $this->question->get_id());
    $this->tpl->setVariable("VALUE_MATCHING_TITLE", $this->question->get_title());
    $this->tpl->setVariable("VALUE_MATCHING_COMMENT", $this->question->get_comment());
    $this->tpl->setVariable("VALUE_MATCHING_AUTHOR", $this->question->get_author());
    $this->tpl->setVariable("VALUE_QUESTION", $this->question->get_question());
    $this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add_matching_pair"));
    $this->tpl->setVariable("SAVE", $this->lng->txt("save"));
    $this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
    $this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
    $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    $this->tpl->setVariable("ACTION_MATCHING_QUESTION", $_SERVER["PHP_SELF"] . $this->get_add_parameter() . "&sel_question_types=qt_matching");
    $this->tpl->parseCurrentBlock();
  }
  
/**
* Sets the content of a question from a posted create/edit form
* 
* Sets the content of a question from a posted create/edit form
*
* @access private
*/
  function set_template_from_question_data($question_type) {
    switch ($question_type) {
      case "qt_multiple_choice_sr":
      case "qt_multiple_choice_mr":
        $this->out_multiple_choice_data();
        break;
      case "qt_cloze":
        $this->out_cloze_question_data();
        break;
      case "qt_matching":
        $this->out_matching_question_data();
        break;
      case "qt_ordering":
        $this->out_ordering_question_data();
        break;
    }
  }

/**
* Sets the content of a muliple choice question from a posted create/edit form
* 
* Sets the content of a muliple choice question from a posted create/edit form
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function set_question_data_from_multiple_choice_template() {
    $result = 0;
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;
    $this->question->set_title(ilUtil::stripSlashes($_POST["title"]));
    $this->question->set_author(ilUtil::stripSlashes($_POST["author"]));
    $this->question->set_comment(ilUtil::stripSlashes($_POST["comment"]));
    $this->question->set_question(ilUtil::stripSlashes($_POST["question"]));
    
    // Delete all existing answers and create new answers from the form data
    $this->question->flush_answers();
    
    // Add all answers from the form into the object
    if ($this->question->get_response() == RESPONSE_SINGLE) {
      // ...for multiple choice with single response
      foreach ($_POST as $key => $value) {
        if (preg_match("/answer_(\d+)/", $key, $matches)) {
          if ($_POST["radio"] == $matches[1]) {
            $is_true = TRUE;
          } else {
            $is_true = FALSE;
          }
          $this->question->add_answer(
            ilUtil::stripSlashes($_POST["$key"]), 
            ilUtil::stripSlashes($_POST["points_$matches[1]"]), 
            ilUtil::stripSlashes($is_true), 
            ilUtil::stripSlashes($matches[1]));
        }
      }
    } else {
      // ...for multiple choice with multiple response
      foreach ($_POST as $key => $value) {
        if (preg_match("/answer_(\d+)/", $key, $matches)) {
          if ($_POST["checkbox_$matches[1]"] == $matches[1]) {
            $is_true = TRUE;
          } else {
            $is_true = FALSE;
          }
          $this->question->add_answer(
            ilUtil::stripSlashes($_POST["$key"]), 
            ilUtil::stripSlashes($_POST["points_$matches[1]"]), 
            ilUtil::stripSlashes($is_true), 
            ilUtil::stripSlashes($matches[1]));
        }
      }
    }
    
    // After adding all questions from the form we have to check if the learner pressed a delete button
    foreach ($_POST as $key => $value) {
      // was one of the answers deleted
      if (preg_match("/delete_(\d+)/", $key, $matches)) {
        $this->question->delete_answer($matches[1]);
      }
    }
    
    // Set the question id from a hidden form parameter
    if ($_POST["multiple_choice_id"] > 0)
      $this->question->set_id($_POST["multiple_choice_id"]);

    return $result;
  }

/**
* Sets the content of a cloze question from a posted create/edit form
* 
* Sets the content of a cloze question from a posted create/edit form
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function set_question_data_from_cloze_question_template() {
    $result = 0;
    // Delete all existing gaps and create new gaps from the form data
    $this->question->flush_gaps();
    
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["clozetext"]))
      $result = 1;
      
    $this->question->set_title(ilUtil::stripSlashes($_POST["title"]));
    $this->question->set_author(ilUtil::stripSlashes($_POST["author"]));
    $this->question->set_comment(ilUtil::stripSlashes($_POST["comment"]));
    $this->question->set_cloze_text(ilUtil::stripSlashes($_POST["clozetext"]));
    $this->question->set_cloze_type(ilUtil::stripSlashes($_POST["clozetype"]));
    
    
    if (strlen($_POST["creategaps"]) == 0) {
      // Create gaps wasn't activated => check gaps for changes and/or deletions
      if ($this->question->get_cloze_type() == CLOZE_TEXT) {  // check text gaps
        // Check for changed values
        foreach ($_POST as $key => $value) {
          // Set gap values
          if (preg_match("/textgap_(\d+)_(\d+)/", $key, $matches)) {
            $answer_array = $this->question->get_gap($matches[1]);
            if (strlen($value) > 0) {
              // Only change gap values <> empty string
              if (strcmp($value, $answer_array[$matches[2]]->get_answertext()) != 0) {
                $this->question->set_answertext(
                  ilUtil::stripSlashes($matches[1]), 
                  ilUtil::stripSlashes($matches[2]), 
                  ilUtil::stripSlashes($value));
              }
            } else {
              // Display errormessage: You've tried to set an gap value to an empty string!
            }
          }
          // Set gap points
          if (preg_match("/points_(\d+)/", $key, $matches)) {
            $points = $value or 0.0;
            $this->question->set_gap_points($matches[1]-1, $value);
          }
        }
        
        foreach ($_POST as $key => $value) {
          // Check, if one of the gap values was deleted
          if (preg_match("/delete_(\d+)_(\d+)/", $key, $matches)) {
            $textgap = "textgap_" . $matches[1] . "_" . $matches[2];
            $this->question->delete_answertext($matches[1], $_POST["$textgap"]);
            $skip_check_changes = TRUE;
          }
        }
      } elseif ($this->question->get_cloze_type() == CLOZE_SELECT) { // check select gaps
        // Check for changed values
        foreach ($_POST as $key => $value) {
          // Set gap values
          if (preg_match("/selectgap_(\d+)_(\d+)/", $key, $matches)) {
            $answer_array = $this->question->get_gap($matches[1]);
            if (strlen($value) > 0) {
              // Only change gap values <> empty string
              if (strcmp($value, $answer_array[$matches[2]]->get_answertext()) != 0) {
                $this->question->set_answertext(
                  ilUtil::stripSlashes($matches[1]), 
                  ilUtil::stripSlashes($matches[2]), 
                  ilUtil::stripSlashes($value));
              }
            } else {
              // Display errormessage: You've tried to set an gap value to an empty string!
            }
          }
          // Set gap points
          if (preg_match("/points_(\d+)_(\d+)/", $key, $matches)) {
            $points = $value or 0.0;
            $this->question->set_single_answer_points($matches[1], $matches[2], $value);
          }
          // Set correctness values
          if (preg_match("/correctness_(\d+)/", $key, $matches)) {
            $this->question->set_single_answer_correctness($matches[1], $value, TRUE);
          }
        }
        foreach ($_POST as $key => $value) {
          // check for order up pressed
          if (preg_match("/order_up_(\d+)_(\d+)_x/", $key, $matches)) {
            $answer_array = $this->question->get_gap($matches[1]);
            $this->question->answer_move_up($answer_array[$matches[2]]);
          }
          // check for order down pressed
          if (preg_match("/order_down_(\d+)_(\d+)_x/", $key, $matches)) {
            $answer_array = $this->question->get_gap($matches[1]);
            $this->question->answer_move_down($answer_array[$matches[2]]);
          }
        }
        
        foreach ($_POST as $key => $value) {
          // Check, if one of the gap values was deleted
          if (preg_match("/delete_(\d+)_(\d+)/", $key, $matches)) {
            $selectgap = "selectgap_" . $matches[1] . "_" . $matches[2];
            $this->question->delete_answertext($matches[1], $_POST["$selectgap"]);
            $skip_check_changes = TRUE;
          }
        }
      }
    }
    return $result;
  }

/**
* Sets the content of a matching question from a posted create/edit form
* 
* Sets the content of a matching question from a posted create/edit form
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function set_question_data_from_matching_question_template() {
    $result = 0;
    
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;
      
    $this->question->set_title(ilUtil::stripSlashes($_POST["title"]));
    $this->question->set_author(ilUtil::stripSlashes($_POST["author"]));
    $this->question->set_comment(ilUtil::stripSlashes($_POST["comment"]));
    $this->question->set_question(ilUtil::stripSlashes($_POST["question"]));
    
    // Delete all existing answers and create new answers from the form data
    $this->question->flush_matchingpairs();
    
    // Add all answers from the form into the object
    foreach ($_POST as $key => $value) {
      if (preg_match("/left_(\d+)_(\d+)/", $key, $matches)) {
        foreach ($_POST as $key2 => $value2) {
          if (preg_match("/right_$matches[1]_(\d+)/", $key2, $matches2)) {
            $matchingtext_id = $matches2[1];
          }
        }
        $this->question->add_matchingpair(
          ilUtil::stripSlashes($_POST["$key"]), 
          ilUtil::stripSlashes($_POST["right_$matches[1]_$matchingtext_id"]), 
          ilUtil::stripSlashes($_POST["points_$matches[1]"]), 
          ilUtil::stripSlashes($matches[2]), 
          ilUtil::stripSlashes($matchingtext_id));
      }
    }

    // Delete a matching pair if the delete button was pressed
    foreach ($_POST as $key => $value) {
      if (preg_match("/delete_(\d+)/", $key, $matches)) {
        $this->question->delete_matchingpair($matches[1]);
      }
    }
    return $result;
  }

/**
* Sets the content of a ordering question from a posted create/edit form
* 
* Sets the content of a ordering question from a posted create/edit form
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function set_question_data_from_ordering_question_template() {
    $result = 0;
    // Delete all existing answers and create new answers from the form data
    $this->question->flush_answers();

    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;

    $this->question->set_title(ilUtil::stripSlashes($_POST["title"]));
    $this->question->set_author(ilUtil::stripSlashes($_POST["author"]));
    $this->question->set_comment(ilUtil::stripSlashes($_POST["comment"]));
    $this->question->set_question(ilUtil::stripSlashes($_POST["question"]));
    // Add answers from the form
    foreach ($_POST as $key => $value) {
      if (preg_match("/answer_(\d+)/", $key, $matches)) {
        $this->question->add_answer(
          ilUtil::stripSlashes($_POST["$key"]), 
          ilUtil::stripSlashes($_POST["points_$matches[1]"]), 
          ilUtil::stripSlashes($matches[1]), 
          ilUtil::stripSlashes($_POST["order_$matches[1]"]));
      }
    }

    // Delete an answer if the delete button was pressed
    foreach ($_POST as $key => $value) {
      if (preg_match("/delete_(\d+)/", $key, $matches)) {
        $this->question->delete_answer($matches[1]);
      }
    }
    return $result;
  }

/**
* Sets the content of a question from a posted create/edit form
* 
* Sets the content of a question from a posted create/edit form
*
* @param string $question_type The question type string
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function set_question_data_from_template($question_type) {
    $result = 0;
    switch ($question_type) {
      case "qt_multiple_choice_sr":
      case "qt_multiple_choice_mr":
        $result = $this->set_question_data_from_multiple_choice_template();
        break;
      case "qt_cloze":
        $result = $this->set_question_data_from_cloze_question_template();
        break;
      case "qt_matching":
        $result = $this->set_question_data_from_matching_question_template();
        break;
      case "qt_ordering":
        $result = $this->set_question_data_from_ordering_question_template();
        break;
    }
    return $result;
  }

/**
* Creates the create/edit template form of a question
*
* Creates the create/edit template form of a question and fills it with
* that data of the question.
*
* @access public
*/
  function set_edit_template() {
    $missing_required_fields = 0;
    
    if (strlen($_POST["cmd"]["cancel"]) > 0) {
      // Cancel
      $this->cancel_action();
      exit();
    }
    
    $this->question->set_ref_id($_GET["ref_id"]);
    $question_type = $this->get_question_type($this->question);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_question.html", true);

    if ($_POST["id"] > 0) {
      // First of all: Load question data from database
      $this->question->load_from_db($_POST["id"]);
    }
  
    if (!$_GET["edit"]) {
      $missing_required_fields = $this->set_question_data_from_template($question_type);
    }
    if (strlen($_POST["cmd"]["save"]) > 0) {
      // Save and back to question pool
      if (!$missing_required_fields) {
        $this->question->save_to_db();
        $this->cancel_action();
        exit();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }
    if (strlen($_POST["cmd"]["apply"]) > 0) {
      // Save and continue editing
      if (!$missing_required_fields) {
        $this->question->save_to_db();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }

    $this->set_template_from_question_data($question_type);
    
    $this->tpl->setCurrentBlock("adm_content");
    if ($this->question->id > 0) {
      $this->tpl->setVariable("TXT_PAGEHEADLINE", $this->lng->txt("edit") . " " . $this->lng->txt($question_type));
    } else {
      $this->tpl->setVariable("TXT_PAGEHEADLINE", $this->lng->txt("create_new") . " " . $this->lng->txt($question_type));
    }
    $this->tpl->parseCurrentBlock();
  }
  
/**
* Creates the learners output of a multiple choice question
*
* Creates the learners output of a multiple choice question
*
* @access public
*/
  function out_working_multiple_choice_question() {
    $this->tpl->addBlockFile("MULTIPLE_CHOICE_QUESTION", "multiple_choice", "tpl.il_as_execute_multiple_choice_question.html", true);
    if ($this->question->response == RESPONSE_SINGLE) {
      $this->tpl->setCurrentBlock("single");
      foreach ($this->question->answers as $key => $value) {
        $this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_VALUE", $key);
        $this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_TEXT", $value->get_answertext());
        $this->tpl->parseCurrentBlock();
      }
    } else {
      $this->tpl->setCurrentBlock("multiple");
      foreach ($this->question->answers as $key => $value) {
        $this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_VALUE", $key);
        $this->tpl->setVariable("MULTIPLE_CHOICE_ANSWER_TEXT", $value->get_answertext());
        $this->tpl->parseCurrentBlock();
      }
    }

    $this->tpl->setCurrentBlock("multiple_choice");
    $this->tpl->setVariable("MULTIPLE_CHOICE_HEADLINE", $this->question->get_title());
    $this->tpl->setVariable("MULTIPLE_CHOICE_QUESTION", $this->question->get_question());
    $this->tpl->parseCurrentBlock();
  }
  
/**
* Creates the learners output of a cloze question
*
* Creates the learners output of a cloze question
*
* @access public
*/
  function out_working_cloze_question() {
    $this->tpl->addBlockFile("CLOZE_TEST", "cloze_test", "tpl.il_as_execute_cloze_test.html", true);
    if ($this->question->cloze_type == CLOZE_TEXT) {
      $this->tpl->setCurrentBlock("cloze");
      $output = $this->question->get_cloze_text();
      for ($gapIndex = 0; $gapIndex < $this->question->get_gap_count(); $gapIndex++) {
        $output = preg_replace("/" . preg_quote($this->question->get_start_tag()) . preg_quote($this->question->get_gap_text_list($gapIndex)) . preg_quote($this->question->get_end_tag()) . "/", "<input type=\"text\" name=\"gap_$gapIndex\" value=\"\" size=\"20\" />", $output);
      }
      $this->tpl->setVariable("TEXT", $output);
      $this->tpl->parseCurrentBlock();
    } else {
      $this->tpl->setCurrentBlock("cloze");
      $output = $this->question->get_cloze_text();
      for ($gapIndex = 0; $gapIndex < $this->question->get_gap_count(); $gapIndex++) {
        $select = "<select name=\"gap_$gapIndex\">";
        $gap = $this->question->get_gap($gapIndex);
        foreach ($gap as $key => $value) {
          $select .= "<option value=\"" . $value->get_order() . "\">" . $value->get_answertext() . "</option>";
        }
        $select .= "</select>";
        $output = preg_replace("/" . preg_quote($this->question->get_start_tag()) . preg_quote($this->question->get_gap_text_list($gapIndex)) . preg_quote($this->question->get_end_tag()) . "/", $select, $output);
      }
      $this->tpl->setVariable("TEXT", $output);
      $this->tpl->parseCurrentBlock();
    }

    $this->tpl->setCurrentBlock("cloze_test");
    $this->tpl->setVariable("CLOZE_TEST_HEADLINE", $this->question->get_title());
    $this->tpl->parseCurrentBlock();
  }

/**
* Creates the learners output of a matching question
*
* Creates the learners output of a matching question
*
* @access public
*/
  function out_working_matching_question() {
    foreach ($this->question->matchingpairs as $key => $value) {
      $array_answer[$value->get_order()] = $value->get_answertext();
      $array_matching[$value->get_matchingtext_order()] = $value->get_matchingtext();
    }
    asort($array_answer);
    asort($array_matching);
    
    $this->tpl->addBlockFile("MATCHING_QUESTION", "matching", "tpl.il_as_execute_matching_question.html", true);
    $this->tpl->setCurrentBlock("matching_question");
    foreach ($this->question->matchingpairs as $key => $value) {
      $this->tpl->setCurrentBlock("answer_combo");
      foreach ($array_answer as $answer_key => $answer_value) {
        $this->tpl->setVariable("COMBO_ANSWER_VALUE", $answer_value);
        $this->tpl->setVariable("COMBO_ANSWER", $answer_key);
        $this->tpl->parseCurrentBlock();
      }
      $this->tpl->setCurrentBlock("matching_combo");
      foreach ($array_matching as $match_key => $match_value) {
        $this->tpl->setVariable("COMBO_MATCHING_VALUE", $match_value);
        $this->tpl->setVariable("COMBO_MATCHING", $match_key);
        $this->tpl->parseCurrentBlock();
      }
      $this->tpl->setVariable("COUNTER", $key);
      $this->tpl->setVariable("TEXT_MATCHES", "matches");
      $this->tpl->parse("matching_question");
    }

    $this->tpl->setCurrentBlock("matching");
    $this->tpl->setVariable("MATCHING_QUESTION_HEADLINE", $this->question->get_title());
    $this->tpl->setVariable("MATCHING_QUESTION", $this->question->get_question());
    $this->tpl->parseCurrentBlock();
  }
  
/**
* Creates the learners output of an ordering question
*
* Creates the learners output of an ordering question
*
* @access public
*/
  function out_working_ordering_question() {
    $this->tpl->addBlockFile("ORDERING_QUESTION", "ordering", "tpl.il_as_execute_ordering_question.html", true);
    $this->tpl->setCurrentBlock("orderingQuestion");
    foreach ($this->question->answers as $key => $value) {
      $this->tpl->setVariable("ORDERING_QUESTION_ANSWER_VALUE", $key);
      $this->tpl->setVariable("ORDERING_QUESTION_ANSWER_TEXT", $value->get_answertext());
      $this->tpl->parseCurrentBlock();
    }

    $this->tpl->setCurrentBlock("ordering");
    $this->tpl->setVariable("ORDERING_QUESTION_HEADLINE", $this->question->get_title());
    $this->tpl->setVariable("ORDERING_QUESTION", $this->question->get_question());
    $this->tpl->parseCurrentBlock();
  }
  
/**
* Creates the learners output of a question
*
* Creates the learners output of a question
*
* @access public
*/
  function out_working_question() {
    $question_type = $this->get_question_type($this->question);
    
    switch($question_type)
    {
      case "qt_cloze":
        $this->out_working_cloze_question();
        break;
      case "qt_multiple_choice_sr":
      case "qt_multiple_choice_mr":
        $this->out_working_multiple_choice_question();
        break;
      case "qt_ordering":
        $this->out_working_ordering_question();
        break;
      case "qt_matching":
        $this->out_working_matching_question();
        break;
    }
  }
  
/**
* Creates a preview of a question using the preview template
*
* Creates a preview of a question using the preview template
*
* @access public
*/
  function out_preview() {
    $question_type = $this->get_question_type($this->question);
    
    $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_preview.html", true);
    switch($question_type)
    {
      case "qt_cloze":
        $this->out_working_cloze_question();
        break;
      case "qt_multiple_choice_sr":
      case "qt_multiple_choice_mr":
        $this->out_working_multiple_choice_question();
        break;
      case "qt_ordering":
        $this->out_working_ordering_question();
        break;
      case "qt_matching":
        $this->out_working_matching_question();
        break;
    }
    $this->tpl->setCurrentBlock("adm_content");
    $this->tpl->setVariable("ACTION_PREVIEW", $_SERVER["PHP_SELF"] . $this->get_add_parameter());
    $this->tpl->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
    $this->tpl->parseCurrentBlock();
  }
}

?>