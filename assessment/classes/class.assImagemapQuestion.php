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
require_once "./assessment/classes/class.assAnswerImagemap.php";

define ("IMAGEMAP_QUESTION_IDENTIFIER", "IMAGE MAP QUESTION");

/**
* Class for image map questions
*
* ASS_ImagemapQuestion is a class for imagemap question.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assImagemapQuestion.php
* @modulegroup   Assessment
*/
class ASS_ImagemapQuestion extends ASS_Question {

/**
* The imagemap_Question containing the question
*
* The imagemap_Question containing the question.
*
* @var string
*/
  var $question;

/**
* The possible answers of the imagemap question
*
* $answers is an array of the predefined answers of the imagemap question
*
* @var array
*/
  var $answers;

/**
* Points for solving the imagemap question
*
* Enter the number of points the user gets when he/she solves the imagemap
* question. This value overrides the point values of single answers when set different
* from zero.
*
* @var double
*/
  var $points;

/**
* The imagemap file containing the name of imagemap file
*
* The imagemap file containing the name of imagemap file
*
* @var string
*/
  var $imagemap_filename;

/**
* The image file containing the name of image file
*
* The image file containing the name of image file
*
* @var string
*/
  var $image_filename;

/**
* The variable containing contents of an imagemap file
*
* The variable containing contents of an imagemap file
*
* @var string
*/
  var $imagemap_contents;
	var $coords;

/**
* ASS_ImagemapQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_ImagemapQuestion object.
*
* @param string $title A title string to describe the question
* @param string $comment A comment string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @param string $imagemap_file The imagemap file name of the imagemap question
* @param string $image_file The image file name of the imagemap question
* @param string $question The question string of the imagemap question
* @access public
*/
  function ASS_ImagemapQuestion(
    $title = "",
    $comment = "",
    $author = "",
    $owner = -1,
    $question = "",
    $imagemap_filename = "",
    $image_filename = ""

  )
  {
    $this->ASS_Question($title, $comment, $author, $owner);
    $this->question = $question;
    $this->imagemap_filename = $imagemap_filename;
    $this->image_filename = $image_filename;
    $this->answers = array();
		$this->points = $points;
		$this->coords = array();
  }

/**
* Returns true, if a imagemap question is complete for use
*
* Returns true, if a imagemap question is complete for use
*
* @return boolean True, if the imagemap question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->image_filename) and (count($this->answers)))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Saves a ASS_ImagemapQuestion object to a database
	*
	* Saves a ASS_ImagemapQuestion object to a database (experimental)
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
			$question_type = 6;
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, points, image_file, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->points),
				$db->quote($this->image_filename),
				$db->quote("$complete"),
				$db->quote($created),
				$original_id
			);
			$result = $db->query($query);
			if ($result == DB_OK)
			{
				$this->id = $db->getLastInsertId();

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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, points = %s, image_file = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->points),
				$db->quote($this->image_filename),
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
				//print "id:".$this->id." answer tex:".$answer_obj->get_answertext()." answer_obj->get_order():".$answer_obj->get_order()." answer_obj->get_coords():".$answer_obj->get_coords()." answer_obj->get_area():".$answer_obj->get_area();
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, coords, area, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->id),
					$db->quote($answer_obj->get_answertext() . ""),
					$db->quote($answer_obj->get_points() . ""),
					$db->quote($answer_obj->get_order() . ""),
					$db->quote($answer_obj->getState() . ""),
					$db->quote($answer_obj->get_coords() . ""),
					$db->quote($answer_obj->get_area() . "")
					);
				$answer_result = $db->query($query);
				}
		}
		parent::saveToDb($original_id);
	}

/**
* Duplicates an ASS_ImagemapQuestion
*
* Duplicates an ASS_ImagemapQuestion
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
		$clone->duplicateImage($original_id);
		return $clone->id;
	}

	function duplicateImage($question_id)
	{
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		if (!file_exists($imagepath)) {
			ilUtil::makeDirParents($imagepath);
		}
		$filename = $this->get_image_filename();
		if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
			print "image could not be duplicated!!!! ";
		}
	}

/**
* Loads a ASS_ImagemapQuestion object from a database
*
* Loads a ASS_ImagemapQuestion object from a database (experimental)
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
        $this->author = $data->author;
				$this->original_id = $data->original_id;
				$this->solution_hint = $data->solution_hint;
        $this->owner = $data->owner;
        $this->question = $data->question_text;
        $this->image_filename = $data->image_file;
        $this->points = $data->points;
        $this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
      }
      $query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
        $db->quote($question_id)
      );
      $result = $db->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) {
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          array_push($this->answers, new ASS_AnswerImagemap($data->answertext, $data->points, $data->aorder, $data->correctness, $data->coords, $data->area));
        }
      }
    }
		parent::loadFromDb($question_id);
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
			$filename = "";
			$image = "";
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
							elseif (strcmp($flownode->node_name(), "response_xy") == 0)
							{
								$ident = $flownode->get_attribute("ident");
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
										case "render_hotspot":
											$render_hotspot = $node_type;
											$labels = $render_hotspot->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												if (strcmp($response_label->node_name(), "material") == 0)
												{
													// image map image
													$mattype = $response_label->first_child();
													if (strcmp($mattype->node_name(), "matimage") == 0)
													{
														$filename = $mattype->get_attribute("label");
														$image = base64_decode($mattype->get_content());
													}
												}
												else
												{
													$material_children = $response_label->child_nodes();
													switch ($response_label->get_attribute("rarea"))
													{
														case "Ellipse":
															$materials[$response_label->get_attribute("ident")]["area"] = "circle";
															break;
														case "Bounded":
															$materials[$response_label->get_attribute("ident")]["area"] = "poly";
															break;
														case "Rectangle":
															$materials[$response_label->get_attribute("ident")]["area"] = "rect";
															break;
													}
													foreach ($material_children as $midx => $childnode)
													{
														if (strcmp($childnode->node_name(), "#text") == 0)
														{
															$materials[$response_label->get_attribute("ident")]["coords"] = $childnode->get_content();
														}
														elseif (strcmp($childnode->node_name(), "material") == 0)
														{
															$materials[$response_label->get_attribute("ident")]["answertext"] = $childnode->get_content();
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
								foreach ($materials as $index => $material)
								{
									if (strcmp($material["coords"], $respcondition_array["conditionvar"]["value"]) == 0)
									{
										$this->add_answer(
											$material["answertext"], 
											$respcondition_array["setvar"]["points"],
											$respcondition_array["conditionvar"]["selected"],
											$index,
											$material["coords"],
											$material["area"]
										);
									}
								}
							}
						}
						break;
				}
			}
			if ($filename)
			{
				$this->saveToDb();
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
				$this->image_filename = $filename;
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
		$qtiCommentText = $this->domxml->create_text_node("Questiontype=".IMAGEMAP_QUESTION_IDENTIFIER);
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
		$qtiResponseXy = $this->domxml->create_element("response_xy");
		$qtiResponseXy->set_attribute("ident", "IM");
		$qtiResponseXy->set_attribute("rcardinality", "Single");
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
				$qtiResponseXy->append_child($qtiMaterial);
			}
		}
		$qtiRenderHotspot = $this->domxml->create_element("render_hotspot");
		$qtiMaterial = $this->domxml->create_element("material");
		$qtiMatImage = $this->domxml->create_element("matimage");
		$qtiMatImage->set_attribute("imagtype", "image/jpeg");
		$qtiMatImage->set_attribute("label", $this->get_image_filename());
		if ($a_include_binary)
		{
			$qtiMatImage->set_attribute("embedded", "base64");
			$imagepath = $this->getImagePath() . $this->get_image_filename();
			$fh = fopen($imagepath, "rb");
			if ($fh == false)
			{
				global $ilErr;
				$ilErr->raiseError($this->lng->txt("error_open_image_file"), $ilErr->WARNING);
				return;
			}
			$imagefile = fread($fh, filesize($imagepath));
			fclose($fh);
			$base64 = base64_encode($imagefile);
			$qtiBase64Data = $this->domxml->create_text_node($base64);
			$qtiMatImage->append_child($qtiBase64Data);
		}
		$qtiMaterial->append_child($qtiMatImage);
		$qtiRenderHotspot->append_child($qtiMaterial);
		// add answers
		foreach ($this->answers as $index => $answer)
		{
			$qtiResponseLabel = $this->domxml->create_element("response_label");
			$qtiResponseLabel->set_attribute("ident", $index);
			switch ($answer->get_area())
			{
				case "rect":
					$qtiResponseLabel->set_attribute("rarea", "Rectangle");
					break;
				case "circle":
					$qtiResponseLabel->set_attribute("rarea", "Ellipse");
					break;
				case "poly":
					$qtiResponseLabel->set_attribute("rarea", "Bounded");
					break;
			}
			$qtiResponseLabelCoords = $this->domxml->create_text_node($answer->get_coords());
			$qtiMaterial = $this->domxml->create_element("material");
			$qtiMatText = $this->domxml->create_element("mattext");
			$qtiMatTextText = $this->domxml->create_text_node($answer->get_answertext());
			$qtiMatText->append_child($qtiMatTextText);
			$qtiMaterial->append_child($qtiMatText);
			$qtiResponseLabel->append_child($qtiResponseLabelCoords);
			$qtiResponseLabel->append_child($qtiMaterial);
			$qtiRenderHotspot->append_child($qtiResponseLabel);
		}
		$qtiResponseXy->append_child($qtiRenderHotspot);
		$qtiFlow->append_child($qtiResponseXy);
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
			$qtiVarinside = $this->domxml->create_element("varinside");
			$qtiVarinside->set_attribute("respident", "IM");
			switch ($answer->get_area())
			{
				case "rect":
					$qtiVarinside->set_attribute("areatype", "Rectangle");
					break;
				case "circle":
					$qtiVarinside->set_attribute("areatype", "Ellipse");
					break;
				case "poly":
					$qtiVarinside->set_attribute("areatype", "Bounded");
					break;
			}
			$qtiVarinsideText = $this->domxml->create_text_node($answer->get_coords());
			$qtiVarinside->append_child($qtiVarinsideText);
			if (!$answer->isStateSet())
			{
				$qtiConditionvar->append_child($qtinot);
				$qtinot->append_child($qtiVarinside);
			}
			else
			{
				$qtiConditionvar->append_child($qtiVarinside);
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
				$linkrefid = "True";
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
			if ($answer->isStateSet())
			{
				$linkrefid = "True";
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
* Gets the imagemap question
*
* Gets the question string of the ASS_ImagemapQuestion object
*
* @return string The question string of the ASS_ImagemapQuestion object
* @access public
* @see $question
*/
  function get_question() {
    return $this->question;
  }

/**
* Sets the imagemap question
*
* Sets the question string of the ASS_ImagemapQuestion object
*
* @param string $question A string containing the imagemap question
* @access public
* @see $question
*/
  function set_question($question = "") {
    $this->question = $question;
  }

/**
* Gets the imagemap file name
*
* Gets the imagemap file name
*
* @return string The imagemap file of the ASS_ImagemapQuestion object
* @access public
* @see $imagemap_filename
*/
  function get_imagemap_filename() {
    return $this->imagemap_filename;
  }

/**
* Sets the imagemap file name
*
* Sets the imagemap file name
*
* @param string $imagemap_file.
* @access public
* @see $imagemap_filename
*/
  function set_imagemap_filename($imagemap_filename, $imagemap_tempfilename = "") {
    if (!empty($imagemap_filename)) {
      $this->imagemap_filename = $imagemap_filename;
    }
    if (!empty($imagemap_tempfilename)) {
 	    $fp = fopen($imagemap_tempfilename, "r");
 	    $contents = fread($fp, filesize($imagemap_tempfilename));
      fclose($fp);
			if (preg_match_all("/<area(.+)>/siU", $contents, $matches)) {
		  	for ($i=0; $i< count($matches[1]); $i++) {
		    	preg_match("/alt\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $alt);
		    	preg_match("/coords\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $coords);
		    	preg_match("/shape\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $shape);
					$this->add_answer($alt[1], 0.0, FALSE, count($this->answers), $coords[1], $shape[1]);
		  	}
			}
    }
	}

/**
* Gets the image file name
*
* Gets the image file name
*
* @return string The image file name of the ASS_ImagemapQuestion object
* @access public
* @see $image_filename
*/
  function get_image_filename() {
    return $this->image_filename;
  }

/**
* Sets the image file name
*
* Sets the image file name
*
* @param string $image_file name.
* @access public
* @see $image_filename
*/
  function set_image_filename($image_filename, $image_tempfilename = "") {

    if (!empty($image_filename)) {
      $this->image_filename = $image_filename;
    }
		if (!empty($image_tempfilename)) {
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename)) {
				print "image not uploaded!!!! ";
			} else {
				// create thumbnail file
				$size = 100;
				$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
				$convert_cmd = ilUtil::getConvertCmd() . " $imagepath$image_filename -resize $sizex$size $thumbpath";
				system($convert_cmd);
			}
		}
  }

/**
* Gets the imagemap file contents
*
* Gets the imagemap file contents
*
* @return string The imagemap file contents of the ASS_ImagemapQuestion object
* @access public
* @see $imagemap_contents
*/
  function get_imagemap_contents($href = "#") {
		$imagemap_contents = "<map name=\"".$this->title."\"> ";
		for ($i = 0; $i < count($this->answers); $i++) {
	 		$imagemap_contents .= "<area alt=\"".$this->answers[$i]->get_answertext()."\" ";
	 		$imagemap_contents .= "shape=\"".$this->answers[$i]->get_area()."\" ";
	 		$imagemap_contents .= "coords=\"".$this->answers[$i]->get_coords()."\" ";
	 		$imagemap_contents .= "href=\"$href&selimage=" . $this->answers[$i]->get_order() . "\" /> ";
		}
		$imagemap_contents .= "</map>";
    return $imagemap_contents;
  }

/**
* Gets the points
*
* Gets the points for solving the question of the ASS_ImagemapQuestion object
*
* @return double The points for solving the imagemap question
* @access public
* @see $points
*/
  function get_points() {
    return $this->points;
  }

/**
* Sets the points
*
* Sets the points for solving the question of the ASS_ImagemapQuestion object
*
* @param points double The points for solving the imagemap question
* @access public
* @see $points
*/
  function set_points($points = 0.0) {
    $this->points = $points;
  }

/**
* Adds a possible answer for a imagemap question
*
* Adds a possible answer for a imagemap question. A ASS_AnswerImagemap object will be
* created and assigned to the array $this->answers.
*
* @param string $answertext The answer text
* @param double $points The points for selecting the answer (even negative points can be used)
* @param integer $status The state of the answer (set = 1 or unset = 0)
* @param integer $order A possible display order of the answer
* @access public
* @see $answers
* @see ASS_AnswerImagemap
*/
  function add_answer(
    $answertext = "",
    $points = 0.0,
    $status = 0,
    $order = 0,
    $coords="",
    $area=""
  )
  {
    if (array_key_exists($order, $this->answers)) {
      // Antwort einf�gen
      $answer = new ASS_AnswerImagemap($answertext, $points, $found, $status, $coords, $area);
			for ($i = count($this->answers) - 1; $i >= $order; $i--) {
				$this->answers[$i+1] = $this->answers[$i];
				$this->answers[$i+1]->set_order($i+1);
			}
			$this->answers[$order] = $answer;
    } else {
      // Anwort anh�ngen
      $answer = new ASS_AnswerImagemap($answertext, $points, count($this->answers), $status, $coords, $area);
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
  function get_answer_count() {
    return count($this->answers);
  }

/**
* Returns an answer
*
* Returns an answer with a given index. The index of the first
* answer is 0, the index of the second answer is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @return object ASS_AnswerImagemap-Object containing the answer
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
* Deletes an area with a given index. The index of the first
* area is 0, the index of the second area is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @access public
* @see $answers
*/
  function deleteArea($index = 0) {
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
* Returns the maximum points, a learner can reach answering the question
*
* Returns the maximum points, a learner can reach answering the question
*
* @access public
* @see $points
*/
  function getMaximumPoints() {
		$points = array("set" => 0, "unset" => 0);
		foreach ($this->answers as $key => $value) {
			if ($value->isStateChecked())
			{
				if ($value->get_points() > $points["set"])
				{
					$points["set"] = $value->get_points();
				}
			}
			else
			{
				$points["unset"] += $value->get_points();
			}
		}
		return $points["set"] + $points["unset"];
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
    $found_values = array();
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
  function getReachedInformation($user_id, $test_id) {
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
			array_push($user_result, $solution);
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

		$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
			$db->quote($ilUser->id),
			$db->quote($test_id),
			$db->quote($this->getId()),
			$db->quote($_GET["selImage"])
		);
		$result = $db->query($query);
//    parent::saveWorkingData($limit_to);
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, points = %s, image_file = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->question . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->points . ""),
				$db->quote($this->image_filename . ""),
				$db->quote($complete . ""),
				$db->quote($this->original_id . "")
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
					$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, coords, area, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
						$db->quote($this->original_id . ""),
						$db->quote($answer_obj->get_answertext() . ""),
						$db->quote($answer_obj->get_points() . ""),
						$db->quote($answer_obj->get_order() . ""),
						$db->quote($answer_obj->getState() . ""),
						$db->quote($answer_obj->get_coords() . ""),
						$db->quote($answer_obj->get_area() . "")
						);
					$answer_result = $db->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}
}

?>
