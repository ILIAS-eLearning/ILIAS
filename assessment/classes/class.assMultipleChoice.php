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
require_once "./assessment/classes/class.assAnswerBinaryState.php";
require_once "./assessment/classes/class.ilQTIUtils.php";

define("RESPONSE_SINGLE", "0");
define("RESPONSE_MULTIPLE", "1");

define("OUTPUT_ORDER", "0");
define("OUTPUT_RANDOM", "1");

define("MULTIPLE_CHOICE_QUESTION_IDENTIFIER", "MULTIPLE CHOICE QUESTION");

/**
* Class for multiple choice tests
*
* ASS_MultipleChoice is a class for multiple choice tests. It
* supports single and multiple response.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMultipleChoice.php
* @modulegroup   Assessment
*/
class ASS_MultipleChoice extends ASS_Question
{
	/**
	* Question string
	*
	* The question string of the multiple choice question
	*
	* @var string
	*/
	var $question;

	/**
	* The given answers of the multiple choice question
	*
	* $answers is an array of the given answers of the multiple choice question
	*
	* @var array
	*/
	var $answers;

	/**
	* Response type
	*
	* This is the response type of the multiple choice question. You can select
	* RESPONSE_SINGLE (=0) or RESPONSE_MULTI (=1).
	*
	* @var integer
	*/
	var $response;

	/**
	* Output type
	*
	* This is the output type for the answers of the multiple choice question. You can select
	* OUTPUT_ORDER(=0) or OUTPUT_RANDOM (=1). The default output type is OUTPUT_ORDER
	*
	* @var integer
	*/
	var $output_type;

	/**
	* ASS_MultipleChoice constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_MultipleChoice object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the multiple choice question
	* @param integer $response Indicates the response type of the multiple choice question
	* @param integer $output_type The output order of the multiple choice answers
	* @access public
	* @see ASS_Question:ASS_Question()
	*/
	function ASS_MultipleChoice(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$response = RESPONSE_SINGLE,
		$output_type = OUTPUT_ORDER
	  )
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->question = $question;
		$this->response = $response;
		$this->output_type = $output_type;
		$this->answers = array();
	}

	/**
	* Returns true, if a multiple choice question is complete for use
	*
	* Returns true, if a multiple choice question is complete for use
	*
	* @return boolean True, if the multiple choice question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers)))
		{
			return true;
		}
			else
		{
			return false;
		}
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
								$this->set_question($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_lid") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								if (strcmp($ident, "MCSR") == 0)
								{
									$this->set_response(RESPONSE_SINGLE);
								}
								else
								{
									$this->set_response(RESPONSE_MULTIPLE);
								}
								$shuffle = "";
								
								$subnodes = $flownode->child_nodes();
								foreach ($subnodes as $node_type)
								{
									switch ($node_type->node_name())
									{
										case "render_choice":
											$render_choice = $node_type;
											if (strcmp($render_choice->node_name(), "render_choice") == 0)
											{
												// select gap
												$shuffle = $render_choice->get_attribute("shuffle");
												$labels = $render_choice->child_nodes();
												foreach ($labels as $lidx => $response_label)
												{
													$material = $response_label->first_child();
													$mattext = $material->first_child();
													$shuf = 0;
													if (strcmp(strtolower($shuffle), "yes") == 0)
													{
														$shuf = 1;
													}
													$this->add_answer($mattext->get_content(), 0, 0,  $response_label->get_attribute("ident"));
												}
											}
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
														$this->setSuggestedSolution($suggested_solution, 0, true);
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
								$this->answers[$respcondition_array["conditionvar"]["value"]]->set_points($respcondition_array["setvar"]["points"]);
								if ($respcondition_array["conditionvar"]["selected"])
								{
									$this->answers[$respcondition_array["conditionvar"]["value"]]->setChecked();
								}
								$feedbacks[$respcondition_array["displayfeedback"]["linkrefid"]] = array(
									"type" => $respcondition_array["conditionvar"]["respident"],
									"value" => $respcondition_array["conditionvar"]["value"],
									"not" => $respcondition_array["conditionvar"]["not"],
									"points" => $respcondition_array["setvar"]["points"],
									"feedback" => ""
								);
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
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false)
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml_header .= "<questestinterop></questestinterop>\n";
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
		$qtiCommentText = $this->domxml->create_text_node("Questiontype=".MULTIPLE_CHOICE_QUESTION_IDENTIFIER);
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
		// add material with question text to presentation
		$qtiMaterial = $this->domxml->create_element("material");
		$qtiMatText = $this->domxml->create_element("mattext");
		$qtiMatTextText = $this->domxml->create_text_node($this->get_question());
		$qtiMatText->append_child($qtiMatTextText);
		$qtiMaterial->append_child($qtiMatText);
		$qtiFlow->append_child($qtiMaterial);
		// add answers to presentation
		$qtiResponseLid = $this->domxml->create_element("response_lid");
		if ($this->response == RESPONSE_SINGLE)
		{
			$qtiResponseLid->set_attribute("ident", "MCSR");
			$qtiResponseLid->set_attribute("rcardinality", "Single");
		}
			else
		{
			$qtiResponseLid->set_attribute("ident", "MCMR");
			$qtiResponseLid->set_attribute("rcardinality", "Multiple");
		}
		$solution = $this->getSuggestedSolution(0);
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
				$qtiResponseLid->append_child($qtiMaterial);
			}
		}
		$qtiRenderChoice = $this->domxml->create_element("render_choice");
		// shuffle output
		if ($this->getShuffle())
		{
			$qtiRenderChoice->set_attribute("shuffle", "yes");
		}
		else
		{
			$qtiRenderChoice->set_attribute("shuffle", "no");
		}

		$akeys = array_keys($this->answers);
		if ($this->getshuffle() && $a_shuffle)
		{
			$akeys = $this->pcArrayShuffle($akeys);
		}

		// add answers
		foreach ($akeys as $index)
		{
			$answer = $this->answers[$index];
			$qtiResponseLabel = $this->domxml->create_element("response_label");
			$qtiResponseLabel->set_attribute("ident", $index);
			$qtiMaterial = $this->domxml->create_element("material");
			$qtiMatText = $this->domxml->create_element("mattext");
			$qtiMatTextText = $this->domxml->create_text_node($answer->get_answertext());
			$qtiMatText->append_child($qtiMatTextText);
			$qtiMaterial->append_child($qtiMatText);
			$qtiResponseLabel->append_child($qtiMaterial);
			$qtiRenderChoice->append_child($qtiResponseLabel);
		}
		$qtiResponseLid->append_child($qtiRenderChoice);
		$qtiFlow->append_child($qtiResponseLid);
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);
		// PART II: qti resprocessing
		$qtiResprocessing = $this->domxml->create_element("resprocessing");
		$qtiOutcomes = $this->domxml->create_element("outcomes");
		$qtiDecvar = $this->domxml->create_element("decvar");
		$qtiOutcomes->append_child($qtiDecvar);
		$qtiResprocessing->append_child($qtiOutcomes);
		// add response conditions
		foreach ($this->answers as $index => $answer)
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
			if ($this->response == RESPONSE_SINGLE)
			{
				$qtiVarequal->set_attribute("respident", "MCSR");
			}
				else
			{
				$qtiVarequal->set_attribute("respident", "MCMR");
			}
			$qtiVarequalText = $this->domxml->create_text_node($index);
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
			if ($answer->isStateChecked())
			{
				if ($this->response == RESPONSE_SINGLE)
				{
					$linkrefid = "True";
				}
					else
				{
					$linkrefid = "True_$index";
				}
			}
			  else
			{
				$linkrefid = "False_$index";
			}
			$qtiDisplayfeedback->set_attribute("linkrefid", $linkrefid);
			$qtiRespcondition->append_child($qtiConditionvar);
			$qtiRespcondition->append_child($qtiSetvar);
			$qtiRespcondition->append_child($qtiDisplayfeedback);
			$qtiResprocessing->append_child($qtiRespcondition);
		}
		$qtiIdent->append_child($qtiResprocessing);

		// PART III: qti itemfeedback
		foreach ($this->answers as $index => $answer)
		{
			$qtiItemfeedback = $this->domxml->create_element("itemfeedback");
			$linkrefid = "";
			if ($answer->isStateChecked())
			{
				if ($this->response == RESPONSE_SINGLE)
				{
					$linkrefid = "True";
				}
					else
				{
					$linkrefid = "True_$index";
				}
			}
			  else
			{
				$linkrefid = "False_$index";
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
	* Saves a ASS_MultipleChoice object to a database
	*
	* Saves a ASS_MultipleChoice object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		$db = & $ilias->db;

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

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
			if ($this->response == RESPONSE_SINGLE)
			{
				$question_type = 1;
			}
			else
			{
				$question_type = 2;
			}
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, shuffle, choice_response, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote("$this->shuffle"),
				$db->quote($this->response),
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

				// Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
				$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, shuffle = %s, choice_response = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote("$this->shuffle"),
				$db->quote($this->response),
				$db->quote("$complete"),
				$db->quote($this->id)
			);
			$result = $db->query($query);
		}
		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten l�schen
			$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);

			// Anworten wegschreiben
			foreach ($this->answers as $key => $value)
			{
				$answer_obj = $this->answers[$key];
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$db->quote($this->id),
				$db->quote($answer_obj->get_answertext()),
				$db->quote($answer_obj->get_points() . ""),
				$db->quote($answer_obj->get_order() . ""),
				$db->quote($answer_obj->getState() . "")
				);
				$answer_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_MultipleChoice object from a database
	*
	* Loads a ASS_MultipleChoice object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;

		$db = & $ilias->db;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
		$db->quote($question_id));
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->question = $data->question_text;
				$this->response = $data->choice_response;
				$this->setShuffle($data->shuffle);
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
				$db->quote($question_id));

			$result = $db->query($query);

			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->answers, new ASS_AnswerBinaryState($data->answertext, $data->points, $data->aorder, $data->correctness));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an ASS_MultipleChoiceQuestion
	*
	* Duplicates an ASS_MultipleChoiceQuestion
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
	* Gets the multiple choice question
	*
	* Gets the question string of the ASS_MultipleChoice object
	*
	* @return string The question string of the ASS_MultipleChoice object
	* @access public
	* @see $question
	*/
	function get_question()
	{
		return $this->question;
	}

	/**
	* Sets the multiple choice question
	*
	* Sets the question string of the ASS_MultipleChoice object
	*
	* @param string $question A string containing the multiple choice question
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Gets the multiple choice response type
	*
	* Gets the multiple choice response type which is either RESPONSE_SINGLE (=0) or RESPONSE_MULTI (=1).
	*
	* @return integer The response type of the ASS_MultipleChoice object
	* @access public
	* @see $response
	*/
	function get_response()
	{
		return $this->response;
	}

	/**
	* Sets the multiple choice response type
	*
	* Sets the response type of the ASS_MultipleChoice object
	*
	* @param integer $response A nonnegative integer value specifying the response type. It is RESPONSE_SINGLE (=0) or RESPONSE_MULTI (=1).
	* @access public
	* @see $response
	*/
	function set_response($response = "")
	{
		$this->response = $response;
	}

	/**
	* Gets the multiple choice output type
	*
	* Gets the multiple choice output type which is either OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
	*
	* @return integer The output type of the ASS_MultipleChoice object
	* @access public
	* @see $output_type
	*/
	function get_output_type()
	{
		return $this->output_type;
	}

	/**
	* Sets the multiple choice output type
	*
	* Sets the output type of the ASS_MultipleChoice object
	*
	* @param integer $output_type A nonnegative integer value specifying the output type. It is OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
	* @access public
	* @see $response
	*/
	function set_output_type($output_type = OUTPUT_ORDER)
	{
		$this->output_type = $output_type;
	}

	/**
	* Adds a possible answer for a multiple choice question
	*
	* Adds a possible answer for a multiple choice question. A ASS_AnswerBinaryState object will be
	* created and assigned to the array $this->answers.
	*
	* @param string $answertext The answer text
	* @param double $points The points for selecting the answer (even negative points can be used)
	* @param boolean $state Defines the answer as correct (TRUE) or incorrect (FALSE)
	* @param integer $order A possible display order of the answer
	* @access public
	* @see $answers
	* @see ASS_AnswerBinaryState
	*/
	function add_answer(
		$answertext = "",
		$points = 0.0,
		$state = 0,
		$order = 0
	)
	{
		$found = -1;
		foreach ($this->answers as $key => $value)
		{
			if ($value->get_order() == $order)
			{
				$found = $order;
			}
		}
		if ($found >= 0)
		{
			// Antwort einfügen
			$answer = new ASS_AnswerBinaryState($answertext, $points, $found, $state);
			array_push($this->answers, $answer);
			for ($i = $found + 1; $i < count($this->answers); $i++)
			{
				$this->answers[$i] = $this->answers[$i-1];
			}
			$this->answers[$found] = $answer;
		}
		else
		{
			// Anwort anhängen
			$answer = new ASS_AnswerBinaryState($answertext, $points, count($this->answers), $state);
			array_push($this->answers, $answer);
		}
	}

	/**
	* Returns the number of answers
	*
	* Returns the number of answers
	*
	* @return integer The number of answers of the multiple choice question
	* @access public
	* @see $answers
	*/
	function get_answer_count()
	{
		return count($this->answers);
	}

	/**
	* Returns an answer
	*
	* Returns an answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @return object ASS_AnswerBinaryState-Object containing the answer
	* @access public
	* @see $answers
	*/
	function get_answer($index = 0)
	{
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
	function delete_answer($index = 0)
	{
		if ($index < 0) return;
		if (count($this->answers) < 1) return;
		if ($index >= count($this->answers)) return;
		unset($this->answers[$index]);
		$this->answers = array_values($this->answers);
		for ($i = 0; $i < count($this->answers); $i++)
		{
			if ($this->answers[$i]->get_order() > $index)
			{
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
	function flush_answers()
	{
		$this->answers = array();
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		$points = 0;
		foreach ($this->answers as $key => $value)
		{
			$points += $value->get_points();
		}
		return $points;
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
	function getReachedPoints($user_id, $test_id)
	{
		global $ilDB;

		$found_values = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$ilDB->quote($user_id),
			$ilDB->quote($test_id),
			$ilDB->quote($this->getId())
		);
		$result = $ilDB->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strcmp($data->value1, "") != 0)
			{
				array_push($found_values, $data->value1);
			}
		}
		$points = 0;
		if (count($found_values) > 0)
		{
			foreach ($this->answers as $key => $answer)
			{
				if ($answer->isStateChecked())
				{
					if (in_array($key, $found_values))
					{
						$points += $answer->get_points();
					}
				}
				else
				{
					if (!in_array($key, $found_values))
					{
						$points += $answer->get_points();
					}
				}
			}
		}
		return $points;
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	* Note: This method should be able to be invoked static - do not
	* use $this inside this class
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function _getReachedPoints($user_id, $test_id)
	{
		return 0;
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
	function getReachedInformation($user_id, $test_id)
	{
		$found_values = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
		$this->ilias->db->quote($user_id),
		$this->ilias->db->quote($test_id),
		$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_values, $data->value1);
		}
		$counter = 1;
		$user_result = array();
		foreach ($found_values as $key => $value)
		{
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"value" => "",
				);
			if (strlen($value) > 0)
			{
				$solution["value"] = $value;
				$solution["points"] = $this->answers[$value]->get_points();
				if ($this->answers[$value]->isStateChecked())
				{
					$solution["true"] = 1;
				}
			}
			$counter++;
			$user_result[$value] = $solution;
		}
		return $user_result;
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
	function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT)
	{
		global $ilDB;
		global $ilUser;

		$db =& $ilDB->db;

		if ($this->response == RESPONSE_SINGLE)
		{
			$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$db->quote($ilUser->id),
				$db->quote($test_id),
				$db->quote($this->getId())
			);
			$result = $db->query($query);
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$update = $row->solution_id;
			if ($update)
			{
				$query = sprintf("UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
					$db->quote($_POST["multiple_choice_result"]),
					$db->quote($update));
			}
			else
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($_POST["multiple_choice_result"])
				);
			}
			$result = $db->query($query);
		}
		else
		{
			$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$db->quote($ilUser->id),
				$db->quote($test_id),
				$db->quote($this->getId())
			);
			$result = $db->query($query);
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^multiple_choice_result_(\d+)/", $key, $matches))
				{
					$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
						$db->quote($ilUser->id),
						$db->quote($test_id),
						$db->quote($this->getId()),
						$db->quote($value)
					);
					$result = $db->query($query);
				}
			}
		}
		//parent::saveWorkingData($limit_to);
		return true;
	}

	function syncWithOriginal()
	{
		global $ilias;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
			$db = & $ilias->db;
	
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, shuffle = %s, choice_response = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->shuffle. ""),
				$db->quote($this->response. ""),
				$db->quote($complete. ""),
				$db->quote($this->original_id. "")
			);
			$result = $db->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
					$db->quote($this->original_id)
				);
				$result = $db->query($query);
	
				foreach ($this->answers as $key => $value)
				{
					$answer_obj = $this->answers[$key];
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->original_id. ""),
					$db->quote($answer_obj->get_answertext(). ""),
					$db->quote($answer_obj->get_points() . ""),
					$db->quote($answer_obj->get_order() . ""),
					$db->quote($answer_obj->getState() . "")
					);
					$answer_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}

	function createRandomSolution($test_id, $user_id)
	{
		$answer = mt_rand(0, count($this->answers)-1);

		global $ilDB;
		global $ilUser;

		$db =& $ilDB->db;

		if ($this->response == RESPONSE_SINGLE)
		{
			$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$db->quote($user_id),
				$db->quote($test_id),
				$db->quote($this->getId())
			);
			$result = $db->query($query);
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$update = $row->solution_id;
			if ($update)
			{
				$query = sprintf("UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
					$db->quote($answer),
					$db->quote($update));
			}
			else
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
					$db->quote($user_id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($answer)
				);
			}
			$result = $db->query($query);
		}
		else
		{
			$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$db->quote($user_id),
				$db->quote($test_id),
				$db->quote($this->getId())
			);
			$result = $db->query($query);
			$answerarray = array();
			for ($i = 0; $i < $answer; $i++)
			{
				$manswer = mt_rand(0, count($this->answers)-1);
				$answerarray[$manswer]++;
			}
			foreach ($answerarray as $key => $value)
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
					$db->quote($user_id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($key)
				);
				$result = $db->query($query);
			}
		}
	}
}

?>
