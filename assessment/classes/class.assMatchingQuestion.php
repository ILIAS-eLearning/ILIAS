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
require_once "./assessment/classes/class.assAnswerMatching.php";

define ("MT_TERMS_PICTURES", 0);
define ("MT_TERMS_DEFINITIONS", 1);
define ("MATCHING_QUESTION_IDENTIFIER", "MATCHING QUESTION");
/**
* Class for matching questions
*
* ASS_MatchingQuestion is a class for matching questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assMatchingQuestion.php
* @modulegroup   Assessment
*/
class ASS_MatchingQuestion extends ASS_Question
{
	/**
	* The question text
	*
	* The question text of the matching question.
	*
	* @var string
	*/
	var $question;

	/**
	* The possible matching pairs of the matching question
	*
	* $matchingpairs is an array of the predefined matching pairs of the matching question
	*
	* @var array
	*/
	var $matchingpairs;

	/**
	* Type of matching question
	*
	* There are two possible types of matching questions: Matching terms and definitions (=1)
	* and Matching terms and pictures (=0).
	*
	* @var integer
	*/
	var $matching_type;

	/**
	* Points for solving the matching question
	*
	* Enter the number of points the user gets when he/she enters the correct matching pairs
	* This value overrides the point values of single matching pairs when set different
	* from zero.
	*
	* @var double
	*/
	var $points;

	/**
	* ASS_MatchingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_MatchingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the matching question
	* @param points double The points for solving the matching question
	* @access public
	*/
	function ASS_MatchingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$points = 0.0,
		$matching_type = MT_TERMS_DEFINITIONS
	)
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->matchingpairs = array();
		$this->question = $question;
		$this->points = $points;
		$this->matching_type = $matching_type;
	}

	/**
	* Returns true, if a matching question is complete for use
	*
	* Returns true, if a matching question is complete for use
	*
	* @return boolean True, if the matching question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->matchingpairs)))
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
			$materials = array();
			$images = array();
			$shuffle = "";
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
							elseif (strcmp($flownode->node_name(), "response_grp") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								if (strcmp($ident, "MQT") == 0)
								{
									$this->set_matching_type(MT_TERMS_DEFINITIONS);
								}
								elseif (strcmp($ident, "MQP") == 0)
								{
									$this->set_matching_type(MT_TERMS_PICTURES);
								}
								$subnodes = $flownode->child_nodes();
								foreach ($subnodes as $node_type)
								{
									switch ($node_type->node_name())
									{
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
										case "render_choice":
											$render_choice = $node_type;
											$shuffle = $render_choice->get_attribute("shuffle");
											$labels = $render_choice->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												$material = $response_label->first_child();
												if ($this->get_matching_type() == MT_TERMS_PICTURES)
												{
													$mattype = $material->first_child();
													if (strcmp($mattype->node_name(), "matimage") == 0)
													{
														$filename = $mattype->get_attribute("label");
														$image = base64_decode($mattype->get_content());
														$images["$filename"] = $image;
														$materials[$response_label->get_attribute("ident")] = $filename;
													}
													else
													{
														$materials[$response_label->get_attribute("ident")] = $mattype->get_content();
													}
												}
												else
												{
													$mattext = $material->first_child();
													$materials[$response_label->get_attribute("ident")] = $mattext->get_content();
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
								$pair = split(",", $respcondition_array["conditionvar"]["value"]);
								$this->add_matchingpair($materials[$pair[0]], $materials[$pair[1]], $respcondition_array["setvar"]["points"], $pair[0], $pair[1]);
							}
						}
						break;
				}
			}
			if (count($images))
			{
				$this->saveToDb();
				foreach ($images as $filename => $image)
				{
					if ($filename)
					{
						$imagepath = $this->getImagePath();
						if (!file_exists($imagepath))
						{
							ilUtil::makeDirParents($imagepath);
						}
						$imagepath .=  $filename;
						$fh = fopen($imagepath, "wb");
						if ($fh == false)
						{
							global $ilErr;
							$ilErr->raiseError($this->lng->txt("error_save_image_file") . ": $php_errormsg", $ilErr->WARNING);
							return;
						}
						$imagefile = fwrite($fh, $image);
						fclose($fh);
						// create thumbnail file
						$thumbpath = $imagepath . "." . "thumb.jpg";
						ilUtil::convertImage($imagepath, $thumbpath, "JPEG", 100);
					}
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
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<questestinterop></questestinterop>\n";
		$this->domxml = domxml_open_mem($xml_header);
		$root = $this->domxml->document_element();

		// qti comment with version information
		$qtiComment = $this->domxml->create_element("qticomment");

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
		$qtiCommentText = $this->domxml->create_text_node("Questiontype=".MATCHING_QUESTION_IDENTIFIER);
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
		$qtiResponseGrp = $this->domxml->create_element("response_grp");
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$qtiResponseGrp->set_attribute("ident", "MQP");
			$qtiResponseGrp->set_attribute("rcardinality", "Multiple");
			if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
			{
				$qtiResponseGrp->set_attribute("output", "javascript");
			}
		}
		else
		{
			$qtiResponseGrp->set_attribute("ident", "MQT");
			$qtiResponseGrp->set_attribute("rcardinality", "Multiple");
			if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
			{
				$qtiResponseGrp->set_attribute("output", "javascript");
			}
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
				$qtiResponseGrp->append_child($qtiMaterial);
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

		// add answertext
		$matchingtext_orders = array();
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			array_push($matchingtext_orders, $matchingpair->getTermId());
		}

		// shuffle it
		$pkeys = array_keys($this->matchingpairs);
		if ($this->getshuffle() && $a_shuffle)
		{
			$pkeys = $this->pcArrayShuffle($pkeys);
		}

		// add answers
		foreach ($pkeys as $index)
		{
			$matchingpair = $this->matchingpairs[$index];

			$qtiResponseLabel = $this->domxml->create_element("response_label");
			$qtiResponseLabel->set_attribute("ident", $matchingpair->getDefinitionId());
			$qtiResponseLabel->set_attribute("match_max", "1");
			$qtiResponseLabel->set_attribute("match_group", join($matchingtext_orders, ","));
			$qtiMaterial = $this->domxml->create_element("material");
			if ($this->get_matching_type() == MT_TERMS_PICTURES)
			{
				$qtiMatImage = $this->domxml->create_element("matimage");
				$qtiMatImage->set_attribute("imagtype", "image/jpeg");
				$qtiMatImage->set_attribute("label", $matchingpair->getPicture());
				$qtiMatImage->set_attribute("embedded", "base64");
				$imagepath = $this->getImagePath() . $matchingpair->getPicture();
				$fh = @fopen($imagepath, "rb");
				if ($fh == false)
				{
					//global $ilErr;
					//$ilErr->raiseError($this->lng->txt("error_open_image_file"), $ilErr->WARNING);
					//return;
				}
				else
				{
					$imagefile = fread($fh, filesize($imagepath));
					fclose($fh);
					$base64 = base64_encode($imagefile);
					$qtiBase64Data = $this->domxml->create_text_node($base64);
					$qtiMatImage->append_child($qtiBase64Data);
					$qtiMaterial->append_child($qtiMatImage);
				}
			}
			else
			{
				$qtiMatText = $this->domxml->create_element("mattext");
				$qtiMatTextText = $this->domxml->create_text_node($matchingpair->getDefinition());
				$qtiMatText->append_child($qtiMatTextText);
				$qtiMaterial->append_child($qtiMatText);
			}
			$qtiResponseLabel->append_child($qtiMaterial);
			$qtiRenderChoice->append_child($qtiResponseLabel);
		}

		if ($a_shuffle)
		{
			$pkeys = $this->pcArrayShuffle($pkeys);
		}

		// add matchingtext
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			$qtiResponseLabel = $this->domxml->create_element("response_label");
			$qtiResponseLabel->set_attribute("ident", $matchingpair->getTermId());
			$qtiMaterial = $this->domxml->create_element("material");
			$qtiMatText = $this->domxml->create_element("mattext");
			$qtiMatTextText = $this->domxml->create_text_node($matchingpair->getTerm());
			$qtiMatText->append_child($qtiMatTextText);
			$qtiMaterial->append_child($qtiMatText);
			$qtiResponseLabel->append_child($qtiMaterial);
			$qtiRenderChoice->append_child($qtiResponseLabel);
		}
		$qtiResponseGrp->append_child($qtiRenderChoice);
		$qtiFlow->append_child($qtiResponseGrp);
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);

		// PART II: qti resprocessing
		$qtiResprocessing = $this->domxml->create_element("resprocessing");
		$qtiOutcomes = $this->domxml->create_element("outcomes");
		$qtiDecvar = $this->domxml->create_element("decvar");
		$qtiOutcomes->append_child($qtiDecvar);
		$qtiResprocessing->append_child($qtiOutcomes);

		// add response conditions
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			$qtiRespcondition = $this->domxml->create_element("respcondition");
			$qtiRespcondition->set_attribute("continue", "Yes");
			// qti conditionvar
			$qtiConditionvar = $this->domxml->create_element("conditionvar");
			$qtiVarsubset = $this->domxml->create_element("varsubset");
			if ($this->get_matching_type() == MT_TERMS_PICTURES)
			{
				$qtiVarsubset->set_attribute("respident", "MQP");
			}
			else
			{
				$qtiVarsubset->set_attribute("respident", "MQT");
			}
			$qtiVarsubsetText = $this->domxml->create_text_node($matchingpair->getTermId() . "," . $matchingpair->getDefinitionId());
			$qtiVarsubset->append_child($qtiVarsubsetText);
			$qtiConditionvar->append_child($qtiVarsubset);
			// qti setvar
			$qtiSetvar = $this->domxml->create_element("setvar");
			$qtiSetvar->set_attribute("action", "Add");
			$qtiSetvarText = $this->domxml->create_text_node($matchingpair->getPoints());
			$qtiSetvar->append_child($qtiSetvarText);
			// qti displayfeedback
			$qtiDisplayfeedback = $this->domxml->create_element("displayfeedback");
			$qtiDisplayfeedback->set_attribute("feedbacktype", "Response");
			$qtiDisplayfeedback->set_attribute("linkrefid", "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId());
			$qtiRespcondition->append_child($qtiConditionvar);
			$qtiRespcondition->append_child($qtiSetvar);
			$qtiRespcondition->append_child($qtiDisplayfeedback);
			$qtiResprocessing->append_child($qtiRespcondition);
		}
		$qtiIdent->append_child($qtiResprocessing);

		// PART III: qti itemfeedback
		foreach ($this->matchingpairs as $index => $matchingpair)
		{
			$qtiItemfeedback = $this->domxml->create_element("itemfeedback");
			$qtiItemfeedback->set_attribute("ident", "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId());
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
	* Saves a ASS_MatchingQuestion object to a database
	*
	* Saves a ASS_MatchingQuestion object to a database (experimental)
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
			$question_type = 4;
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, matching_type, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type. ""),
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->owner. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->matching_type. ""),
				$db->quote($this->points. ""),
				$db->quote($complete. ""),
				$db->quote($created. ""),
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, matching_type = %s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->matching_type. ""),
				$db->quote($this->points. ""),
				$db->quote($complete. ""),
				$db->quote($this->id. "")
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
			foreach ($this->matchingpairs as $key => $value)
			{
				$matching_obj = $this->matchingpairs[$key];
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->id),
					$db->quote($matching_obj->getTerm() . ""),
					$db->quote($matching_obj->getPoints() . ""),
					$db->quote($matching_obj->getTermId() . ""),
					$db->quote($matching_obj->getDefinition() . ""),
					$db->quote($matching_obj->getDefinitionId() . "")
				);
				$matching_result = $db->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_MatchingQuestion object from a database
	*
	* Loads a ASS_MatchingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
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
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->author = $data->author;
				$this->solution_hint = $data->solution_hint;
				$this->obj_id = $data->obj_fi;
				$this->original_id = $data->original_id;
				$this->owner = $data->owner;
				$this->matching_type = $data->matching_type;
				$this->question = $data->question_text;
				$this->points = $data->points;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY answer_id ASC",
				$db->quote($question_id)
			);
			$result = $db->query($query);
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->matchingpairs, new ASS_AnswerMatching($data->answertext, $data->points, $data->aorder, $data->matchingtext, $data->matching_order));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an ASS_MatchingQuestion
	*
	* Duplicates an ASS_MatchingQuestion
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

		// duplicate the image
		$clone->duplicateImages($original_id);
		return $clone->id;
	}

	function duplicateImages($question_id)
	{
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $imagepath);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				if (!copy($imagepath_original . $filename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	/**
	* Sets the matching question text
	*
	* Sets the matching question text
	*
	* @param string $question The question text
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Sets the matching question type
	*
	* Sets the matching question type
	*
	* @param integer $matching_type The question matching type
	* @access public
	* @see $matching_type
	*/
	function set_matching_type($matching_type = MT_TERMS_DEFINITIONS)
	{
		$this->matching_type = $matching_type;
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
	function get_question()
	{
		return $this->question;
	}

	/**
	* Returns the matching question type
	*
	* Returns the matching question type
	*
	* @return integer The matching question type
	* @access public
	* @see $matching_type
	*/
	function get_matching_type()
	{
		return $this->matching_type;
	}

	/**
	* Adds an matching pair for an matching question
	*
	* Adds an matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param string $answertext The answer text
	* @param string $matchingtext The matching text of the answer text
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @param integer $order A possible display order of the matching pair
	* @access public
	* @see $matchingpairs
	* @see ASS_AnswerMatching
	*/
	function add_matchingpair(
		$term = "",
		$picture_or_definition = "",
		$points = 0.0,
		$term_id = 0,
		$picture_or_definition_id = 0
	)
	{
		// append answer
		if ($term_id == 0)
		{
			$term_id = $this->get_random_id();
		}

		if ($picture_or_definition_id == 0)
		{
			$picture_or_definition_id = $this->get_random_id();
		}
		$matchingpair = new ASS_AnswerMatching($term, $points, $term_id, $picture_or_definition, $picture_or_definition_id);
		array_push($this->matchingpairs, $matchingpair);
	}

	/**
	* Returns a matching pair
	*
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair($index = 0)
	{
		if ($index < 0)
		{
			return NULL;
		}
		if (count($this->matchingpairs) < 1)
		{
			return NULL;
		}
		if ($index >= count($this->matchingpairs))
		{
			return NULL;
		}
		return $this->matchingpairs[$index];
	}

	/**
	* Deletes a matching pair
	*
	* Deletes a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @access public
	* @see $matchingpairs
	*/
	function delete_matchingpair($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->matchingpairs) < 1)
		{
			return;
		}
		if ($index >= count($this->matchingpairs))
		{
			return;
		}
		unset($this->matchingpairs[$index]);
		$this->matchingpairs = array_values($this->matchingpairs);
	}

	/**
	* Deletes all matching pairs
	*
	* Deletes all matching pairs
	*
	* @access public
	* @see $matchingpairs
	*/
	function flush_matchingpairs()
	{
		$this->matchingpairs = array();
	}

	/**
	* Returns the number of matching pairs
	*
	* Returns the number of matching pairs
	*
	* @return integer The number of matching pairs of the matching question
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair_count()
	{
		return count($this->matchingpairs);
	}

	/**
	* Gets the points
	*
	* Gets the points for entering the correct order of the ASS_MatchingQuestion object
	*
	* @return double The points for entering the correct order of the matching question
	* @access public
	* @see $points
	*/
	function get_points()
	{
		return $this->points;
	}

	/**
	* Sets the points
	*
	* Sets the points for entering the correct order of the ASS_MatchingQuestion object
	*
	* @param points double The points for entering the correct order of the matching question
	* @access public
	* @see $points
	*/
	function set_points($points = 0.0)
	{
		$this->points = $points;
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
		$found_value1 = array();
		$found_value2 = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strcmp($data->value1, "") != 0)
			{
				array_push($found_value1, $data->value1);
				array_push($found_value2, $data->value2);
			}
		}
		$points = 0;
		foreach ($found_value2 as $key => $value)
		{
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $value) and ($answer_value->getTermId() == $found_value1[$key]))
				{
					$points += $answer_value->getPoints();
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
	function getReachedInformation($user_id, $test_id)
	{
		$found_value1 = array();
		$found_value2 = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_value1, $data->value1);
			array_push($found_value2, $data->value2);
		}
		$counter = 1;
		$user_result = array();
		foreach ($found_value1 as $key => $value)
		{
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"term" => "",
				"definition" => ""
			);
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $found_value2[$key]) and ($answer_value->getTermId() == $value))
				{
					$points += $answer_value->getPoints();
					$solution["points"] = $answer_value->getPoints();
					$solution["term"] = $value;
					$solution["definition"] = $found_value2[$key];
					$solution["true"] = 1;
				}
				else
				{
					$solution["term"] = $value;
					$solution["definition"] = $found_value2[$key];
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
	function getMaximumPoints()
	{
		$points = 0;
		foreach ($this->matchingpairs as $key => $value)
		{
			if ($value->getPoints() > 0)
			{
				$points += $value->getPoints();
			}
		}
		return $points;
	}

	/**
	* Sets the image file
	*
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param string $image_filename Name of the original image file
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function set_image_file($image_filename, $image_tempfilename = "")
	{
		$result = 0;
		if (!empty($image_tempfilename))
		{
			require_once "./content/classes/Media/class.ilObjMediaObject.php";
			$mimetype = ilObjMediaObject::getMimeType($image_tempfilename);
			if (!preg_match("/^image/", $mimetype) and (strcmp($mimetype, "") != 0))
			{
				$result = 1;
			}
			else
			{
				$imagepath = $this->getImagePath();
				if (!file_exists($imagepath))
				{
					ilUtil::makeDirParents($imagepath);
				}
				if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename))
				{
					$result = 2;
				}
				else
				{
					// create thumbnail file
					$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
					ilUtil::convertImage($imagepath.$image_filename, $thumbpath, "JPEG", 100);
				}
			}
		}
		return $result;
	}

	/**
	* Checks the data to be saved for consistency
	*
	* Checks the data to be saved for consistency
	*
  * @return boolean True, if the check was ok, False otherwise
	* @access public
	* @see $answers
	*/
	function checkSaveData()
	{
		$result = true;
		$matching_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
			{
				if ((strcmp($value, "") != 0) && ($value != -1))
				{
					array_push($matching_values, $value);
				}
			}
		}
		$check_matching = array_flip($matching_values);
		if (count($check_matching) != count($matching_values))
		{
			// duplicate matching values!!!
			$result = false;
			sendInfo($this->lng->txt("duplicate_matching_values_selected"));
		}
		return $result;
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
		$saveWorkingDataResult = $this->checkSaveData();
		if ($saveWorkingDataResult)
		{
			$db =& $ilDB->db;
	
			$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$db->quote($ilUser->id),
				$db->quote($test_id),
				$db->quote($this->getId())
			);
			$result = $db->query($query);
	
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
				{
					$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($value),
					$db->quote($matches[1])
					);
					$result = $db->query($query);
				}
			}
			$saveWorkingDataResult = true;
		}
		//    parent::saveWorkingData($limit_to);
		return $saveWorkingDataResult;
	}

	function get_random_id()
	{
		$random_number = mt_rand(1, 100000);
		$found = FALSE;
		while ($found)
		{
			$found = FALSE;
			foreach ($this->matchingpairs as $key => $value)
			{
				if (($value->getTermId() == $random_number) || ($value->getDefinitionId() == $random_number))
				{
					$found = TRUE;
					$random_number++;
				}
			}
		}
		return $random_number;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, matching_type = %s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->matching_type. ""),
				$db->quote($this->points. ""),
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
	
				foreach ($this->matchingpairs as $key => $value)
				{
					$matching_obj = $this->matchingpairs[$key];
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
						$db->quote($this->original_id . ""),
						$db->quote($matching_obj->getTerm() . ""),
						$db->quote($matching_obj->getPoints() . ""),
						$db->quote($matching_obj->getTermId() . ""),
						$db->quote($matching_obj->getDefinition() . ""),
						$db->quote($matching_obj->getDefinitionId() . "")
					);
					$matching_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}
}

?>
