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

require_once "./assessment/classes/class.assQuestion.php";
require_once "./assessment/classes/class.assAnswerCloze.php";
require_once "./assessment/classes/class.ilQTIUtils.php";

define("CLOZE_TEXT", "0");
define("CLOZE_SELECT", "1");
define("CLOZE_TEST_IDENTIFIER", "CLOZE QUESTION");

/**
* Class for cloze tests
*
* ASS_ClozeText is a class for cloze tests using text or select gaps.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>* @version	$Id$
* @version	$Id$
* @module   class.assClozeTest.php
* @modulegroup   Assessment
*/
class ASS_ClozeTest extends ASS_Question
{
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
	function saveToDb($original_id = "")
	{
		global $ilias;

		$db =& $ilias->db;
		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
		$shuffle = 1;

		if (!$this->shuffle)
		{
			$shuffle = 0;
		}

		if ($original_id)
		{
			$original_id = $db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, shuffle, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote(3),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->cloze_text),
				$db->quote($estw_time),
				$db->quote("$this->shuffle"),
				$db->quote("$complete"),
				$db->quote($created),
				$original_id
			);
			$result = $db->query($query);
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();

				// create page object of question
				$this->createPageObject();

				// Falls die Frage in einen Test eingef�gt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, shuffle = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
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

		if ($result == DB_OK)
		{
			// Antworten schreiben

			// delete old answers
			$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);
			// Anworten wegschreiben
			foreach ($this->gaps as $key => $value)
			{
				foreach ($value as $answer_id => $answer_obj)
				{
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, name, shuffle, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
						$db->quote($this->id),
						$db->quote($key),
						$db->quote($answer_obj->get_answertext() . ""),
						$db->quote($answer_obj->get_points() . ""),
						$db->quote($answer_obj->get_order() . ""),
						$db->quote($answer_obj->get_cloze_type() . ""),
						$db->quote($answer_obj->get_name() . ""),
						$db->quote($answer_obj->get_shuffle() . ""),
						$db->quote($answer_obj->getState() . "")
						);
					$answer_result = $db->query($query);
				}
			}
		}
		parent::saveToDb($original_id);
	}

/**
* Loads a ASS_ClozeTest object from a database
*
* Loads a ASS_ClozeTest object from a database
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
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $question_id;
        $this->obj_id = $data->obj_fi;
        $this->title = $data->title;
        $this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
        $this->author = $data->author;
        $this->owner = $data->owner;
        $this->cloze_text = $data->question_text;
				$this->shuffle = $data->shuffle;
        $this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
      }

      $query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
        $db->quote($question_id)
      );
      $result = $db->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) {
        $counter = -1;
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          if ($data->gap_id != $counter) {
            $answer_array = array();
            array_push($this->gaps, $answer_array);
            $counter = $data->gap_id;
          }
          array_push($this->gaps[$counter], new ASS_AnswerCloze($data->answertext, $data->points, $data->aorder, $data->correctness, $data->cloze_type, $data->name, $data->shuffle, $data->answer_id));
        }
      }
    }
		parent::loadFromDb($question_id);
  }

/**
* Duplicates an ASS_ClozeTest
*
* Duplicates an ASS_ClozeTest
*
* @access public
*/
	function duplicate($for_test = true, $title = "", $author = "", $owner = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		$original_id = $this->id;
		if ($original_id <= 0)
		{
			$original_id = "";
		}
		$clone->id = -1;
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
		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

		// copy question page content
		$clone->copyPageOfQuestion($original_id);

		return $clone->id;
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
			$materials = array();
			$gapcounter = 0;
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (!(preg_match("/ILIAS Version\=/is", $comment, $matches) or preg_match("/Questiontype\=/is", $comment, $matches)))
						{
							$this->setComment($comment);
						}
						break;
					case "duration":
						$iso8601period = $node->get_content();
						if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
						{
							$this->setEstimatedWorkingTime($matches[4], $matches[5], $matches[6]);
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
								array_push($materials, $mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_str") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$this->gaps["$ident"] = array();
								$shuffle = "";
								$subnodes = $flownode->child_nodes();
								foreach ($subnodes as $node_type)
								{
									switch ($node_type->node_name())
									{
										case "render_choice":
											$render_type = $node_type;
											if (strcmp($render_type->node_name(), "render_choice") == 0)
											{
												// select gap
												$shuffle = $render_type->get_attribute("shuffle");
												$labels = $render_type->child_nodes();
												foreach ($labels as $lidx => $response_label)
												{
													$material = $response_label->first_child();
													$mattext = $material->first_child();
													$shuf = 0;
													if (strcmp(strtolower($shuffle), "yes") == 0)
													{
														$shuf = 1;
													}
													array_push($this->gaps["$ident"], new ASS_AnswerCloze($mattext->get_content(), 0, count($this->gaps["$ident"]), 0, CLOZE_SELECT, $ident, $shuf));
												}
											}
											break;
										case "render_fib":
											break;
										case "material":
											$matlabel = $node_type->get_attribute("label");
											if (strcmp($matlabel, "suggested_solution") == 0)
											{
												$mattype = $node_type->first_child();
												if (strcmp($mattype->node_name(), "mattext") == 0)
												{
													$suggested_solution = $mattype->get_content();
													if ($suggested_solution)
													{
														if ($this->getId() < 1)
														{
															$this->saveToDb();
														}
														if (preg_match("/gap_(\d+)/", $ident, $matches))
														{
															$this->setSuggestedSolution($suggested_solution, $matches[1], true);
														}
													}
												}
											}
											break;
									}
								}
							}
						}
						break;
					case "resprocessing":
						$resproc_nodes = $node->child_nodes();
						foreach ($resproc_nodes as $index => $respcondition)
						{
							if (strcmp($respcondition->node_name(), "respcondition") == 0)
							{
								$respcondition_array =& ilQTIUtils::_getRespcondition($respcondition);
								$found_answer = 0;
								foreach ($this->gaps[$respcondition_array["conditionvar"]["respident"]] as $key => $value)
								{
									if (strcmp($value->get_answertext(), $respcondition_array["conditionvar"]["value"]) == 0)
									{
										$found_answer = 1;
										$this->gaps[$respcondition_array["conditionvar"]["respident"]][$key]->set_points($respcondition_array["setvar"]["points"]);
										if ($respcondition_array["conditionvar"]["selected"])
										{
											$this->gaps[$respcondition_array["conditionvar"]["respident"]][$key]->setChecked();
										}
									}
								}
								if (!$found_answer)
								{
									// text gap
									array_push($this->gaps[$respcondition_array["conditionvar"]["respident"]], new ASS_AnswerCloze($respcondition_array["conditionvar"]["value"], $respcondition_array["setvar"]["points"], count($this->gaps[$respcondition_array["conditionvar"]["respident"]]), 1, CLOZE_TEXT, $respcondition_array["conditionvar"]["respident"], 0));
								}
							}
						}
						break;
				}
			}
			$this->gaps = array_values($this->gaps);
			$i = 0;
			foreach ($materials as $key => $value)
			{
				$this->cloze_text .= $value;
				$gaptext = $this->get_gap_text_list($i);
				if ($gaptext)
				{
					$type = " type=\"select\"";
					if ($this->gaps[$i][0]->get_cloze_type() == CLOZE_TEXT)
					{
						$type = " type=\"text\"";
					}
					$shuffle = " shuffle=\"yes\"";
					if (!$this->gaps[$i][0]->get_shuffle())
					{
						$shuffle = " shuffle=\"no\"";
					}
					$this->cloze_text .= "<gap$type$shuffle>$gaptext</gap>";
				}
				$i++;
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
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false)
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<questestinterop></questestinterop>\n";
		$this->domxml = domxml_open_mem($xml_header);
		$root = $this->domxml->document_element();
		// qti ident
		$qtiIdent = $this->domxml->create_element("item");
		$qtiIdent->set_attribute("ident", "il_".IL_INST_ID."_qst_".$this->getId());
		$qtiIdent->set_attribute("title", $this->getTitle());
		$root->append_child($qtiIdent);
		// add qti comment
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node($this->getComment());
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node("ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node("Questiontype=".CLOZE_TEST_IDENTIFIER);
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		// add estimated working time
		$qtiDuration = $this->domxml->create_element("duration");
		$workingtime = $this->getEstimatedWorkingTime();
		$qtiDurationText = $this->domxml->create_text_node(sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]));
		$qtiDuration->append_child($qtiDurationText);
		$qtiIdent->append_child($qtiDuration);
		// PART I: qti presentation
		$qtiPresentation = $this->domxml->create_element("presentation");
		$qtiPresentation->set_attribute("label", $this->getTitle());
		// add flow to presentation
		$qtiFlow = $this->domxml->create_element("flow");

		$text_parts = preg_split("/\<gap.*?\<\/gap\>/", $this->get_cloze_text());
		// add material with question text to presentation
		for ($i = 0; $i <= $this->get_gap_count(); $i++)
		{
			// n-th text part
			$qtiMaterial = $this->domxml->create_element("material");
			$qtiMatText = $this->domxml->create_element("mattext");
			$qtiMatTextText = $this->domxml->create_text_node($text_parts[$i]);
			$qtiMatText->append_child($qtiMatTextText);
			$qtiMaterial->append_child($qtiMatText);
			$qtiFlow->append_child($qtiMaterial);

			if ($i < $this->get_gap_count())
			{
				// add gap
				$gap = $this->get_gap($i);
				if ($gap[0]->get_cloze_type() == CLOZE_SELECT)
				{
					// comboboxes
					$qtiResponseStr = $this->domxml->create_element("response_str");
					$qtiResponseStr->set_attribute("ident", "gap_$i");
					$qtiResponseStr->set_attribute("rcardinality", "Single");
					$solution = $this->getSuggestedSolution($i);
					if (count($solution))
					{
						if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
						{
							$qtiMaterial = $this->domxml->create_element("material");
							$qtiMaterial->set_attribute("label", "suggested_solution");
							$qtiMatText = $this->domxml->create_element("mattext");
							$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
							if (strcmp($matches[1], "") != 0)
							{
								$intlink = $solution["internal_link"];
							}
							$qtiMatTextText = $this->domxml->create_text_node($intlink);
							$qtiMatText->append_child($qtiMatTextText);
							$qtiMaterial->append_child($qtiMatText);
							$qtiResponseStr->append_child($qtiMaterial);
						}
					}
					
					$qtiRenderChoice = $this->domxml->create_element("render_choice");
					// shuffle output
					if ($gap[0]->get_shuffle())
					{
						$qtiRenderChoice->set_attribute("shuffle", "Yes");
					}
					else
					{
						$qtiRenderChoice->set_attribute("shuffle", "No");
					}

					$gkeys = array_keys($gap);
					if ($this->getshuffle() && $a_shuffle)
					{
						$gkeys = $this->pcArrayShuffle($gkeys);
					}

					// add answers
					foreach ($gkeys as $key)
					{
						$value = $gap[$key];
						$qtiResponseLabel = $this->domxml->create_element("response_label");
						$qtiResponseLabel->set_attribute("ident", $key);
						$qtiMaterial = $this->domxml->create_element("material");
						$qtiMatText = $this->domxml->create_element("mattext");
						$tmpvalue = $value->get_answertext();
						$qtiMatTextText = $this->domxml->create_text_node($tmpvalue);
						$qtiMatText->append_child($qtiMatTextText);
						$qtiMaterial->append_child($qtiMatText);
						$qtiResponseLabel->append_child($qtiMaterial);
						$qtiRenderChoice->append_child($qtiResponseLabel);
					}
					$qtiResponseStr->append_child($qtiRenderChoice);
					$qtiFlow->append_child($qtiResponseStr);
				}
				else
				{
					// text fields
					$qtiResponseStr = $this->domxml->create_element("response_str");
					$qtiResponseStr->set_attribute("ident", "gap_$i");
					$qtiResponseStr->set_attribute("rcardinality", "Single");
					$solution = $this->getSuggestedSolution($i);
					if (count($solution))
					{
						if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
						{
							$qtiMaterial = $this->domxml->create_element("material");
							$qtiMaterial->set_attribute("label", "suggested_solution");
							$qtiMatText = $this->domxml->create_element("mattext");
							$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
							if (strcmp($matches[1], "") != 0)
							{
								$intlink = $solution["internal_link"];
							}
							$qtiMatTextText = $this->domxml->create_text_node($intlink);
							$qtiMatText->append_child($qtiMatTextText);
							$qtiMaterial->append_child($qtiMatText);
							$qtiResponseStr->append_child($qtiMaterial);
						}
					}
					$qtiRenderFib = $this->domxml->create_element("render_fib");
					$qtiRenderFib->set_attribute("fibtype", "String");
					$qtiRenderFib->set_attribute("prompt", "Box");
					$qtiResponseLabel = $this->domxml->create_element("response_label");
					$qtiResponseLabel->set_attribute("ident", $i);
					$qtiRenderFib->append_child($qtiResponseLabel);
					$qtiResponseStr->append_child($qtiRenderFib);
					$qtiFlow->append_child($qtiResponseStr);
				}
			}
		}
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);

		// PART II: qti resprocessing
		$qtiResprocessing = $this->domxml->create_element("resprocessing");
		$qtiOutcomes = $this->domxml->create_element("outcomes");
		$qtiDecvar = $this->domxml->create_element("decvar");
		$qtiOutcomes->append_child($qtiDecvar);
		$qtiResprocessing->append_child($qtiOutcomes);
		// add response conditions
		for ($i = 0; $i < $this->get_gap_count(); $i++)
		{
			$gap = $this->get_gap($i);
			if ($gap[0]->get_cloze_type() == CLOZE_SELECT)
			{
				foreach ($gap as $index => $answer)
				{
					$qtiRespcondition = $this->domxml->create_element("respcondition");
					$qtiRespcondition->set_attribute("continue", "Yes");
					// qti conditionvar
					$qtiConditionvar = $this->domxml->create_element("conditionvar");

					if (!$answer->isStateSet())
					{
						$qtinot = $this->domxml->create_element("not");
					}
					
					$qtiVarequal = $this->domxml->create_element("varequal");
					$qtiVarequal->set_attribute("respident", "gap_$i");
					$qtiVarequalText = $this->domxml->create_text_node($answer->get_answertext());
					$qtiVarequal->append_child($qtiVarequalText);
					if (!$answer->isStateSet())
					{
						$qtiConditionvar->append_child($qtinot);
						$qtinot->append_child($qtiVarequal);
					}
					else
					{
						$qtiConditionvar->append_child($qtiVarequal);
					}
					// qti setvar
					$qtiSetvar = $this->domxml->create_element("setvar");
					$qtiSetvar->set_attribute("action", "Add");
					$qtiSetvarText = $this->domxml->create_text_node($answer->get_points());
					$qtiSetvar->append_child($qtiSetvarText);
					// qti displayfeedback
					$qtiDisplayfeedback = $this->domxml->create_element("displayfeedback");
					$qtiDisplayfeedback->set_attribute("feedbacktype", "Response");
					$linkrefid = "";
					if ($answer->isStateSet())
					{
						$linkrefid = "$i" . "_True";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
					}
					$qtiDisplayfeedback->set_attribute("linkrefid", $linkrefid);
					$qtiRespcondition->append_child($qtiConditionvar);
					$qtiRespcondition->append_child($qtiSetvar);
					$qtiRespcondition->append_child($qtiDisplayfeedback);
					$qtiResprocessing->append_child($qtiRespcondition);
				}
			}
			else
			{
				foreach ($gap as $index => $answer)
				{
					$qtiRespcondition = $this->domxml->create_element("respcondition");
					$qtiRespcondition->set_attribute("continue", "Yes");
					// qti conditionvar
					$qtiConditionvar = $this->domxml->create_element("conditionvar");
					$qtiVarequal = $this->domxml->create_element("varequal");
					$qtiVarequal->set_attribute("respident", "gap_$i");
					$qtiVarequalText = $this->domxml->create_text_node($answer->get_answertext());
					$qtiVarequal->append_child($qtiVarequalText);
					$qtiConditionvar->append_child($qtiVarequal);
					// qti setvar
					$qtiSetvar = $this->domxml->create_element("setvar");
					$qtiSetvar->set_attribute("action", "Add");
					$qtiSetvarText = $this->domxml->create_text_node($answer->get_points());
					$qtiSetvar->append_child($qtiSetvarText);
					// qti displayfeedback
					$qtiDisplayfeedback = $this->domxml->create_element("displayfeedback");
					$qtiDisplayfeedback->set_attribute("feedbacktype", "Response");
					$qtiDisplayfeedback->set_attribute("linkrefid", "$i" . "_True_$index");
					$qtiRespcondition->append_child($qtiConditionvar);
					$qtiRespcondition->append_child($qtiSetvar);
					$qtiRespcondition->append_child($qtiDisplayfeedback);
					$qtiResprocessing->append_child($qtiRespcondition);
				}
			}
		}
		$qtiIdent->append_child($qtiResprocessing);

		// PART III: qti itemfeedback
		for ($i = 0; $i < $this->get_gap_count(); $i++)
		{
			$gap = $this->get_gap($i);
			if ($gap[0]->get_cloze_type() == CLOZE_SELECT)
			{
				foreach ($gap as $index => $answer)
				{
					$qtiItemfeedback = $this->domxml->create_element("itemfeedback");
					$linkrefid = "";
					if ($answer->isStateSet())
					{
						$linkrefid = "$i" . "_True";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
					}
					$qtiItemfeedback->set_attribute("ident", $linkrefid);
					$qtiItemfeedback->set_attribute("view", "All");
					// qti flow_mat
					$qtiFlowmat = $this->domxml->create_element("flow_mat");
					$qtiMaterial = $this->domxml->create_element("material");
					$qtiMattext = $this->domxml->create_element("mattext");
					// Insert response text for right/wrong answers here!!!
					$qtiMattextText = $this->domxml->create_text_node("");
					$qtiMattext->append_child($qtiMattextText);
					$qtiMaterial->append_child($qtiMattext);
					$qtiFlowmat->append_child($qtiMaterial);
					$qtiItemfeedback->append_child($qtiFlowmat);
					$qtiIdent->append_child($qtiItemfeedback);
				}
			}
			else
			{
				foreach ($gap as $index => $answer)
				{
					$qtiItemfeedback = $this->domxml->create_element("itemfeedback");
					$linkrefid = "";
					if ($answer->isStateSet())
					{
						$linkrefid = "$i" . "_True_$index";
					}
						else
					{
						$linkrefid = "$i" . "_False_$index";
					}
					$qtiItemfeedback->set_attribute("ident", $linkrefid);
					$qtiItemfeedback->set_attribute("view", "All");
					// qti flow_mat
					$qtiFlowmat = $this->domxml->create_element("flow_mat");
					$qtiMaterial = $this->domxml->create_element("material");
					$qtiMattext = $this->domxml->create_element("mattext");
					// Insert response text for right/wrong answers here!!!
					$qtiMattextText = $this->domxml->create_text_node("");
					$qtiMattext->append_child($qtiMattextText);
					$qtiMaterial->append_child($qtiMattext);
					$qtiFlowmat->append_child($qtiMaterial);
					$qtiItemfeedback->append_child($qtiFlowmat);
					$qtiIdent->append_child($qtiItemfeedback);
				}
			}
		}

		$xml = $this->domxml->dump_mem(true);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
//echo htmlentities($xml);
		return $xml;

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
	function set_cloze_text($cloze_text = "")
	{
		$this->cloze_text = $cloze_text;
		preg_match_all("/" . "<gap(.*?)>" . "(.*?)" . preg_quote($this->end_tag, "/") . "/", $cloze_text, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[2] as $key => $value)
		{
			$value = str_replace("\,", "__VAR__comma__VAR__", $value);
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
			if ($type == CLOZE_TEXT)
			{
				$default_state = 1;
			}
			else
			{
				$default_state = 0;
			}
			foreach ($cloze_words as $index => $text)
			{
				$text = str_replace("__VAR__comma__VAR__", ",", $text);
				array_push($answer_array, new ASS_AnswerCloze($text, 0, $index, $default_state, $type, $name, $shuffle));
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
    	if (strlen($value) == 0) {
				$this->cloze_text = preg_replace("/>" . preg_quote($value) . "</", ">".$this->get_gap_text_list($key)."<", $this->cloze_text);
    	}
    	else {
   			$this->cloze_text = preg_replace("/" . preg_quote($value) . "/", $this->get_gap_text_list($key), $this->cloze_text);
   		}
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
			array_push($result, str_replace(",", "\,", $value->get_answertext()));
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

    $this->cloze_text = preg_replace("/" . "<gap[^<]*?>" . preg_quote($old_text, "/") . preg_quote($this->end_tag, "/") . "/", "", $this->cloze_text);
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
		$answertext = str_replace("\,", ",", $answertext);
  	if ($add_gaptext == 1)    {
    	$arr = $this->gaps[$index][0];
    	if (strlen($this->gaps[$index][count($this->gaps[$index])-1]->get_answertext()) != 0) {
				$default_state = 0;
				if ($arr->get_cloze_type() == CLOZE_TEXT)
				{
					$default_state = 1;
				}
    		array_push($this->gaps[$index], new ASS_AnswerCloze($answertext, 0, count($this->gaps[$index]),
    			$default_state, $arr->get_cloze_type(),
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
					$this->gaps[$index][$key]->setState(1);
				} else {
					$this->gaps[$index][$key]->setState(0);
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
* Sets the state of a gap answer
*
* Sets the state of a gap answer with a given index. The index of the first
* gap is 0, the index of the second gap is 1 and so on.
*
* @param integer $index_gaps A nonnegative index of the n-th gap
* @param integer $index_answerobject A nonnegative index of the n-th answer in the specified gap
* @param boolean $state The state of the answer
* @access public
* @see $gaps
*/
  function set_single_answer_state($index_gaps = 0, $index_answerobject = 0, $state = 0) {
    if ($index_gaps < 0) return;
    if (count($this->gaps) < 1) return;
    if ($index_gaps >= count($this->gaps)) return;
    if ($index_answerobject < 0) return;
    if (count($this->gaps[$index_gaps]) < 1) return;
    if ($index_answerobject >= count($this->gaps[$index_gaps])) return;
    $this->gaps[$index_gaps][$index_answerobject]->setState($state);
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
		$user_result = array();
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			if (strcmp($data->value2, "") != 0)
			{
				$user_result[$data->value1] = array(
					"gap_id" => $data->value1,
					"value" => $data->value2
				);
			}
    }
    $points = 0;
    $counter = 0;
		foreach ($user_result as $gap_id => $value) {
      if ($this->gaps[$gap_id][0]->get_cloze_type() == CLOZE_TEXT) {
        foreach ($this->gaps[$gap_id] as $k => $v) {
          if (strcmp(strtolower($v->get_answertext()), strtolower($value["value"])) == 0) {
            $points += $v->get_points();
          }
        }
      } else {
				foreach ($this->gaps[$gap_id] as $answerkey => $answer)
				{
					if ($answer->isStateChecked())
					{
						if ($value["value"] == $answerkey)
						{
							$points += $answer->get_points();
						}
					}
					else
					{
						if ($value["value"] != $answerkey)
						{
							$points += $answer->get_points();
						}
					}
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
  function getReachedInformation($user_id, $test_id) {
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
          if (strcmp(strtolower($v->get_answertext()), strtolower($found_value2[$key])) == 0) {
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
        if ($this->gaps[$value][$found_value1[$key]]->isStateSet()) {
					$solution["points"] = $this->gaps[$value][$found_value1[$key]]->get_points();
					$solution["true"] = 1;
        }
      }
			$counter++;
			$user_result[$value] = $solution;
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
					$points += $value2->get_points();
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
* @return boolean Indicates the save status (true if saved successful, false otherwise)
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
      if (preg_match("/^gap_(\d+)/", $key, $matches)) {
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
		return true;
  }

}

?>
