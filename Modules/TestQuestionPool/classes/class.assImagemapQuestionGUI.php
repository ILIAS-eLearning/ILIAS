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

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Image map question GUI representation
*
* The assImagemapQuestionGUI class encapsulates the GUI representation
* for image map questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assImagemapQuestionGUI extends assQuestionGUI
{
	/**
	* assImagemapQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assImagemapQuestionGUI object.
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function assImagemapQuestionGUI(
			$id = -1
	)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assImagemapQuestion.php";
		$this->assQuestionGUI();
		$this->object = new assImagemapQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (isset($_POST["imagemap"]) ||
		isset($_POST["imagemap_x"]) ||
		isset($_POST["imagemap_y"]))
		{
			$this->ctrl->setCmd("getCoords");
			$cmd = "getCoords";
		}

		return $cmd;
	}


	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion()
	{
		include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_imagemap_question.html", "Modules/TestQuestionPool");
		if (($this->ctrl->getCmd() == "addArea" or $this->ctrl->getCmd() == "getCoords") and ($this->ctrl->getCmd() != "saveShape"))
		{
			foreach ($this->object->coords as $key => $value)
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "coords_$key");
				$this->tpl->setVariable("HIDDEN_VALUE", $value);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "newarea");
			$this->tpl->setVariable("HIDDEN_VALUE", $_POST["newarea"]);
			$this->tpl->parseCurrentBlock();

			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->get_image_filename());
			foreach ($this->object->answers as $index => $answer)
			{
				$preview->addArea($index, $answer->getArea(), $answer->getCoords(), $answer->getAnswertext(), "", "", true);
			}
			$hidearea = false;
			$disabled_save = " disabled=\"disabled\"";
			$coords = "";
			switch ($_POST["newarea"])
			{
				case "rect":
					if (count($this->object->coords) == 0)
					{
						ilUtil::sendInfo($this->lng->txt("rectangle_click_tl_corner"));
					}
					else if (count($this->object->coords) == 1)
					{
						ilUtil::sendInfo($this->lng->txt("rectangle_click_br_corner"));
					}
					else if (count($this->object->coords) == 2)
					{
						$coords = join($this->object->coords, ",");
						$hidearea = true;
						$disabled_save = "";
					}
					break;
				case "circle":
					if (count($this->object->coords) == 0)
					{
						ilUtil::sendInfo($this->lng->txt("circle_click_center"));
					}
					else if (count($this->object->coords) == 1)
					{
						ilUtil::sendInfo($this->lng->txt("circle_click_circle"));
					}
					else if (count($this->object->coords) == 2)
					{
						if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $this->object->coords[0] . " " . $this->object->coords[1], $matches))
						{
							$coords = "$matches[1],$matches[2]," . (int)sqrt((($matches[3]-$matches[1])*($matches[3]-$matches[1]))+(($matches[4]-$matches[2])*($matches[4]-$matches[2])));
						}
						$hidearea = true;
						$disabled_save = "";
					}
					break;
				case "poly":
					if (count($this->object->coords) == 0)
					{
						ilUtil::sendInfo($this->lng->txt("polygon_click_starting_point"));
					}
					else if (count($this->object->coords) == 1)
					{
						ilUtil::sendInfo($this->lng->txt("polygon_click_next_point"));
					}
					else if (count($this->object->coords) > 1)
					{
						ilUtil::sendInfo($this->lng->txt("polygon_click_next_or_save"));
						$disabled_save = "";
						$coords = join($this->object->coords, ",");
					}
					break;
			}
			if ($coords)
			{
				$preview->addArea($preview->getAreaCount(), $_POST["newarea"], $coords, $_POST["shapetitle"], "", "", true, "blue");
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->get_image_filename());
			if (!$hidearea)
			{
				$this->tpl->setCurrentBlock("maparea");
				$this->tpl->setVariable("IMAGE_SOURCE", "$imagepath");
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("imagearea");
				$this->tpl->setVariable("IMAGE_SOURCE", "$imagepath");
				$this->tpl->setVariable("ALT_IMAGE", $this->lng->txt("imagemap"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("imagemapeditor");
			$this->tpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap"));
			$this->tpl->setVariable("VALUE_SHAPETITLE", $_POST["shapetitle"]);
			$this->tpl->setVariable("TEXT_SHAPETITLE", $this->lng->txt("name"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("DISABLED_SAVE", $disabled_save);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("IMAGEMAP_ID", $this->object->getId());
			$this->ctrl->setParameter($this, "sel_question_types", "assImagemapQuestion");
			$this->ctrl->setParameter($this, "editmap", "1");
			$this->tpl->setVariable("ACTION_IMAGEMAP_QUESTION",	$this->ctrl->getFormaction($this));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$tblrow = array("tblrow1top", "tblrow2top");
			for ($i = 0; $i < $this->object->getAnswerCount(); $i++)
			{
				$this->tpl->setCurrentBlock("answers");
				$answer = $this->object->getAnswer($i);
				$this->tpl->setVariable("ANSWER_ORDER", $answer->getOrder());
				$this->tpl->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($answer->getAnswertext()));
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				if ((strcmp($_GET["markarea"], "") != 0) && ($_GET["markarea"] == $i))
				{
					$this->tpl->setVariable("CLASS_FULLWIDTH", "fullwidth_marked");
				}
				else
				{
					$this->tpl->setVariable("CLASS_FULLWIDTH", "fullwidth");
				}
				$this->tpl->setVariable("VALUE_IMAGEMAP_POINTS", $answer->getPoints());
				$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
				$coords = "";
				switch ($answer->getArea())
				{
					case "poly":
					case "rect":
						$coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $answer->getCoords());
						break;
					case "circle":
						$coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $answer->getCoords());
						break;
				}
				$this->tpl->setVariable("COORDINATES", $coords);
				$this->tpl->setVariable("AREA", $answer->getArea());
				$this->tpl->setVariable("TEXT_SHAPE", strtoupper($answer->getArea()));
				$this->tpl->parseCurrentBlock();
			}
			if ($this->object->getAnswerCount())
			{
				$this->tpl->setCurrentBlock("selectall");
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$i++;
				$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("DELETE_AREA", $this->lng->txt("delete_area"));
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("answerheader");
				$this->tpl->setVariable("TEXT_NAME", $this->lng->txt("name"));
				$this->tpl->setVariable("TEXT_TRUE", $this->lng->txt("true"));
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("TEXT_SHAPE", $this->lng->txt("shape"));
				$this->tpl->setVariable("TEXT_COORDINATES", $this->lng->txt("coordinates"));
				$this->tpl->parseCurrentBlock();
			}
			// call to other question data i.e. estimated working time block
			$this->outOtherQuestionData();
			// image block

			$internallinks = array(
				"lm" => $this->lng->txt("obj_lm"),
				"st" => $this->lng->txt("obj_st"),
				"pg" => $this->lng->txt("obj_pg"),
				"glo" => $this->lng->txt("glossary_term")
			);
			foreach ($internallinks as $key => $value)
			{
				$this->tpl->setCurrentBlock("internallink");
				$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
				$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
				$this->tpl->parseCurrentBlock();
			}

			if (strcmp($this->object->get_image_filename(), "") != 0)
			{
				$this->tpl->setCurrentBlock("addarea");
				$this->tpl->setVariable("ADD_AREA", $this->lng->txt("add_area"));
				$this->tpl->setVariable("TEXT_RECT", $this->lng->txt("rectangle"));
				$this->tpl->setVariable("TEXT_CIRCLE", $this->lng->txt("circle"));
				$this->tpl->setVariable("TEXT_POLY", $this->lng->txt("polygon"));
				if (array_key_exists("newarea", $_POST))
				{
					switch ($_POST["newarea"])
					{
						case "circle":
							$this->tpl->setVariable("SELECTED_CIRCLE", " selected=\"selected\"");
							break;
						case "poly":
							$this->tpl->setVariable("SELECTED_POLY", " selected=\"selected\"");
							break;
						case "rect":
							$this->tpl->setVariable("SELECTED_RECT", " selected=\"selected\"");
							break;
					}
				}
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("HeadContent");
			$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
			$javascript = "<script type=\"text/javascript\">ilAddOnLoad(initialSelect);\n".
				"function initialSelect() {\n%s\n}</script>";
			if (strcmp($_GET["markarea"], "") != 0)
			{
				$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_imagemap.answer_".$_GET["markarea"].".focus(); document.frm_imagemap.answer_".$_GET["markarea"].".scrollIntoView(\"true\");"));
			}
			else
			{
				switch ($this->ctrl->getCmd())
				{
					case "saveShape":
					case "deletearea":
						if ($this->object->getAnswerCount() > 0)
						{
							$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_imagemap.answer_".($this->object->getAnswerCount() - 1).".focus(); document.frm_imagemap.answer_".($this->object->getAnswerCount() - 1).".scrollIntoView(\"true\");"));
						}
						else
						{
							$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_imagemap.title.focus();"));
						}
						break;
					default:
						$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_imagemap.title.focus();"));
						break;
				}
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("question_data");
			$img = $this->object->get_image_filename();
			$this->tpl->setVariable("TEXT_IMAGE", $this->lng->txt("image"));
			if (!empty($img))
			{
				$this->tpl->setVariable("IMAGE_FILENAME", $img);
				$this->tpl->setVariable("VALUE_IMAGE_UPLOAD", $this->lng->txt("change"));
				$this->tpl->setCurrentBlock("imageupload");
				//$this->tpl->setVariable("UPLOADED_IMAGE", $img);
				$this->tpl->parse("imageupload");
				$map = "";
				if (count($this->object->answers))
				{
					$preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->get_image_filename());
					foreach ($this->object->answers as $index => $answer)
					{
						$preview->addArea($index, $answer->getArea(), $answer->getCoords(), $answer->getAnswertext(), $this->ctrl->getLinkTarget($this, "editQuestion") . "&markarea=$index", "", true);
					}
					$preview->createPreview();
					$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->get_image_filename());
					$map = $preview->getImagemap("imagemap_" . $this->object->getId());
				}
				else
				{
					$imagepath = $this->object->getImagePathWeb() . $img;
				}
				$size = GetImageSize ($this->object->getImagePath() . $this->object->get_image_filename());
				if ($map)
				{
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath\" alt=\"$img\" border=\"0\" " . $size[3] . " usemap=\"" . "#imagemap_" . $this->object->getId(). "\" />\n$map\n");
				}
				else
				{
					$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath\" alt=\"$img\" border=\"0\" " . $size[3] . " />");
				}
			}
			else
			{
				$this->tpl->setVariable("VALUE_IMAGE_UPLOAD", $this->lng->txt("upload"));
			}

			// imagemap block
			$imgmap = $this->object->get_imagemap_filename();
			$this->tpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap_file"));
			$this->tpl->setVariable("VALUE_IMAGEMAP_UPLOAD", $this->lng->txt("add_imagemap"));
			$this->tpl->setCurrentBlock("questioneditor");
			$this->tpl->setVariable("VALUE_IMAGEMAP_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
			$this->tpl->setVariable("VALUE_IMAGEMAP_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
			$this->tpl->setVariable("VALUE_IMAGEMAP_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
			$questiontext = $this->object->getQuestion();
			$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($questiontext)));
			$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
			$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
			$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("TEXT_SOLUTION_HINT", $this->lng->txt("solution_hint"));
			if (count($this->object->suggested_solutions))
			{
				$solution_array = $this->object->getSuggestedSolution(0);
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
				$this->tpl->setVariable("TEXT_VALUE_SOLUTION_HINT", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a> ");
				$this->tpl->setVariable("BUTTON_REMOVE_SOLUTION", $this->lng->txt("remove"));
				$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("change"));
				$this->tpl->setVariable("VALUE_SOLUTION_HINT", $solution_array["internal_link"]);
			}
			else
			{
				$this->tpl->setVariable("BUTTON_ADD_SOLUTION", $this->lng->txt("add"));
			}

			$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
			$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
			$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("assImagemapQuestion"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$this->ctrl->setParameter($this, "sel_question_types", "assImagemapQuestion");
			$this->tpl->setVariable("ACTION_IMAGEMAP_QUESTION",	$this->ctrl->getFormaction($this));
			$this->tpl->setVariable("IMAGEMAP_ID", $this->object->getId());
			$this->tpl->parseCurrentBlock();
			include_once "./Services/RTE/classes/class.ilRTE.php";
			$rtestring = ilRTE::_getRTEClassname();
			include_once "./Services/RTE/classes/class.$rtestring.php";
			$rte = new $rtestring();
			$rte->addPlugin("latex");
			$rte->addButton("latex"); $rte->addButton("pastelatex");
			include_once "./classes/class.ilObject.php";
			$obj_id = $_GET["q_id"];
			$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
			$rte->addRTESupport($obj_id, $obj_type, "assessment");
			$this->tpl->setCurrentBlock("adm_content");
			//$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
			$this->tpl->parseCurrentBlock();
		}
	}

	function getCoords()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	function back()
	{
		$this->editQuestion();
	}

	function saveShape()
	{
		$this->save();
	}

	function addArea()
	{
		$_SESSION["last_area"] = $_POST["newarea"];
		if ($this->writePostData())
		{
      ilUtil::sendInfo($this->getErrorMessage());
			$this->ctrl->setCmd("");
		}
		$this->editQuestion();
	}

	function uploadingImage()
	{
		if ($_GET["q_id"] < 1)
		{
			$this->save();
		}
		else
		{
			$this->writePostData();
			$this->editQuestion();
		}
	}

	function uploadingImagemap()
	{
		if ($_GET["q_id"] < 1)
		{
			$this->save();
		}
		else
		{
			$this->writePostData();
			$this->editQuestion();
		}
	}

	function deleteArea()
	{
		$this->writePostData();
		$checked_areas = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				array_push($checked_areas, $matches[1]);
			}
		}
		rsort($checked_areas, SORT_NUMERIC);
		foreach ($checked_areas as $index)
		{
			$this->object->deleteArea($index);
		}
		$this->editQuestion();
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
		$result = 0;
		$saved = false;

		if ($_GET["editmap"])
		{
			$this->object->coords = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/coords_(\d+)/", $key, $matches))
				{
					$this->object->coords[$matches[1]] = $value;
				}
			}
			if (isset($_POST["imagemap_x"]))
			{
				array_push($this->object->coords, $_POST["imagemap_x"] . "," . $_POST["imagemap_y"]);
			}
			if ($this->ctrl->getCmd() == "saveShape")
			{
				$coords = "";
				switch ($_POST["newarea"])
				{
					case "rect":
						$coords = join($this->object->coords, ",");
						break;
					case "circle":
						if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $this->object->coords[0] . " " . $this->object->coords[1], $matches))
						{
							$coords = "$matches[1],$matches[2]," . (int)sqrt((($matches[3]-$matches[1])*($matches[3]-$matches[1]))+(($matches[4]-$matches[2])*($matches[4]-$matches[2])));
						}
						break;
					case "poly":
						$coords = join($this->object->coords, ",");
						break;
				}
				$this->object->addAnswer($_POST["shapetitle"], 0, count($this->object->answers), $coords, $_POST["newarea"]);
			}
		}
		else
		{
			if (!$this->checkInput())
			{
				$result = 1;
			}
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setQuestion($questiontext);
			$this->object->setSuggestedSolution($_POST["solution_hint"], 0);
			$this->object->setShuffle($_POST["shuffle"]);

			// adding estimated working time
			$saved = $this->writeOtherPostData($result);

			if (($_POST["id"] > 0) or ($result != 1))
			{
				// Question is already saved, so imagemaps and images can be uploaded
				//setting image file

				if (strlen($_FILES['imageName']['tmp_name']) == 0)
				{
					$this->object->setImageFilename(ilUtil::stripSlashes($_POST["uploaded_image"]));
				}
				else
				{
					if ($this->object->getId() <= 0)
					{
						$this->object->saveToDb();
						$this->ctrl->setParameter($this, "q_id", $this->object->getId());
						$saved = true;
						ilUtil::sendInfo($this->lng->txt("question_saved_for_upload"));
					}
					$this->object->setImageFilename($_FILES['imageName']['name'], $_FILES['imageName']['tmp_name']);
				}

				//setting imagemap
				if (empty($_FILES['imagemapName']['tmp_name']))
				{
					$this->object->setImagemapFilename(ilUtil::stripSlashes($_POST['uploaded_imagemap']));
					// Add all answers from the form into the object
					$this->object->flushAnswers();
					foreach ($_POST as $key => $value)
					{
						if (preg_match("/answer_(\d+)/", $key, $matches))
						{
							$points = $_POST["points_$matches[1]"];

							$this->object->addAnswer(
								ilUtil::stripSlashes($_POST["$key"]),
								ilUtil::stripSlashes($points),
								$matches[1],
								ilUtil::stripSlashes($_POST["coords_$matches[1]"]),
								ilUtil::stripSlashes($_POST["area_$matches[1]"])
							);
						}
					}
				}
				else
				{
					if ($this->object->getId() <= 0)
					{
						$this->object->saveToDb();
						$this->ctrl->setParameter($this, "q_id", $this->object->getId());
						$saved = TRUE;
						ilUtil::sendInfo($this->lng->txt("question_saved_for_upload"), TRUE);
					}
					$this->object->setImagemapFilename($_FILES['imagemapName']['name'], $_FILES['imagemapName']['tmp_name']);
				}
			}
			else
			{
				if (($this->ctrl->getCmd() == "uploadingImage") and (!empty($_FILES['imageName']['tmp_name'])))
				{
					ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_upload_image"));
				}
				else if (($_POST["cmd"]["uploadingImagemap"]) and (!empty($_FILES['imagemapName']['tmp_name'])))
				{
					ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_upload_imagemap"));
				}
			}
		}
		if ($this->ctrl->getCmd() == "addArea")
		{
			$this->object->saveToDb();
			$saved = true;
		}
		if ($saved)
		{
			$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		}
		return $result;
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);

		$this->ctrl->setParameterByClass("ilTestOutputGUI", "formtimestamp", time());
		$formaction = $this->ctrl->getLinkTargetByClass("ilTestOutputGUI", "selectImagemapRegion");
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
		{
			$pass = ilObjTest::_getPass($active_id);
			$info =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			$info =& $this->object->getSolutionValues($active_id, NULL);
		}
		if (count($info))
		{
			if (strcmp($info[0]["value1"], "") != 0)
			{
				$formaction .= "&selImage=" . $info[0]["value1"];
			}
		}
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE)
	{
		$imagepath = $this->object->getImagePathWeb() . $this->object->get_image_filename();
		$solutions = array();
		if ($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if ((!$showsolution) && !ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			$found_index = -1;
			$max_points = 0;
			foreach ($this->object->answers as $index => $answer)
			{
				if ($answer->getPoints() > $max_points)
				{
					$max_points = $answer->getPoints();
					$found_index = $index;
				}
			}
			array_push($solutions, array("value1" => $found_index));
		}
		if (is_array($solutions))
		{
			include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->get_image_filename());
			foreach ($solutions as $idx => $solution_value)
			{
				if (strcmp($solution_value["value1"], "") != 0)
				{
					$preview->addArea($solution_value["value1"], $this->object->answers[$solution_value["value1"]]->getArea(), $this->object->answers[$solution_value["value1"]]->getCoords(), $this->object->answers[$solution_value["value1"]]->getAnswertext(), "", "", true);
				}
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->get_image_filename());
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		if ($active_id)
		{
			if ($graphicalOutput)
			{
				// output of ok/not ok icons for user entered solutions
				$reached_points = $this->object->getReachedPoints($active_id, $pass);
				if ($reached_points == $this->object->getMaximumPoints())
				{
					$template->setCurrentBlock("icon_ok");
					$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
					$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("icon_ok");
					if ($reached_points > 0)
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
					}
					else
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
					}
					$template->parseCurrentBlock();
				}
			}
		}
			
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->getILIASPage();
			$solutionoutput = "<div class=\"ilias_content\">" . preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", "</div><div class=\"ilc_Question\">" . $solutionoutput . "</div><div class=\"ilias_content\">", $pageoutput) . "</div>";
		}
		return $solutionoutput;
	}
	
	function getPreview($show_question_only = FALSE)
	{
		$imagepath = $this->object->getImagePathWeb() . $this->object->get_image_filename();
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$formaction = "#";
		foreach ($this->object->answers as $answer_id => $answer)
		{
			$template->setCurrentBlock("imagemap_area");
			$template->setVariable("HREF_AREA", $formaction);
			$template->setVariable("SHAPE", $answer->getArea());
			$template->setVariable("COORDS", $answer->getCoords());
			$template->setVariable("ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->setVariable("TITLE", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$pageoutput = $this->getILIASPage();
			$questionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $questionoutput, $pageoutput);
		}
		else
		{
			$questionoutput = preg_replace("/\<div[^>]*?>(.*)\<\/div>/is", "\\1", $questionoutput);
		}

		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// get page object output
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id);

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}

		$imagepath = $this->object->getImagePathWeb() . $this->object->get_image_filename();
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if ((!$showsolution) && !ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
			$preview = new ilImagemapPreview($this->object->getImagePath().$this->object->get_image_filename());
			foreach ($solutions as $idx => $solution_value)
			{
				if (strcmp($solution_value["value1"], "") != 0)
				{
					$preview->addArea($solution_value["value1"], $this->object->answers[$solution_value["value1"]]->getArea(), $this->object->answers[$solution_value["value1"]]->getCoords(), $this->object->answers[$solution_value["value1"]]->getAnswertext(), "", "", true);
				}
			}
			$preview->createPreview();
			$imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->get_image_filename());
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$this->ctrl->setParameterByClass("ilTestOutputGUI", "formtimestamp", time());
		$formaction = $this->ctrl->getLinkTargetByClass("ilTestOutputGUI", "selectImagemapRegion");
		foreach ($this->object->answers as $answer_id => $answer)
		{
			$template->setCurrentBlock("imagemap_area");
			$template->setVariable("HREF_AREA", $formaction . "&amp;selImage=$answer_id");
			$template->setVariable("SHAPE", $answer->getArea());
			$template->setVariable("COORDS", $answer->getCoords());
			$template->setVariable("ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->setVariable("TITLE", ilUtil::prepareFormOutput($answer->getAnswertext()));
			$template->parseCurrentBlock();
			if ($show_feedback)
			{
				if (strlen($user_solution) && $user_solution == $answer_id)
				{
					$feedback = $this->object->getFeedbackSingleAnswer($user_solution);
					if (strlen($feedback))
					{
						$template->setCurrentBlock("feedback");
						$template->setVariable("FEEDBACK", $feedback);
						$template->parseCurrentBlock();
					}
				}
			}
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$template->setVariable("IMG_SRC", "$imagepath");
		$template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
		$template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
		$questionoutput = $template->get();
		$questionoutput = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">\<\/div>)/ims", $questionoutput, $pageoutput);
		return $questionoutput;
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			return false;
		}
		return true;
	}

	function addSuggestedSolution()
	{
		$_SESSION["subquestion_index"] = 0;
		if ($_POST["cmd"]["addSuggestedSolution"])
		{
			if ($this->writePostData())
			{
				ilUtil::sendInfo($this->getErrorMessage());
				$this->editQuestion();
				return;
			}
			if (!$this->checkInput())
			{
				ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
				$this->editQuestion();
				return;
			}
		}
		$this->object->saveToDb();
		$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->getQuestionTemplate();
		parent::addSuggestedSolution();
	}

	/**
	* Saves the feedback for a single choice question
	*
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		foreach ($this->object->answers as $index => $answer)
		{
			$this->object->saveFeedbackSingleAnswer($index, ilUtil::stripSlashes($_POST["feedback_answer_$index"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		}
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_imagemap_feedback.html", "Modules/TestQuestionPool");
		foreach ($this->object->answers as $index => $answer)
		{
			$this->tpl->setCurrentBlock("feedback_answer");
			$this->tpl->setVariable("FEEDBACK_TEXT_ANSWER", $this->lng->txt("feedback"));
			$text = strtoupper($answer->getArea() . " (" . $answer->getCoords() . ")");
			if (strlen($answer->getAnswertext()))
			{
				$text = $answer->getAnswertext() . ": " . $text;
			}
			$this->tpl->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($text, TRUE));
			$this->tpl->setVariable("ANSWER_ID", $index);
			$this->tpl->setVariable("VALUE_FEEDBACK_ANSWER", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackSingleAnswer($index)), FALSE));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("FEEDBACK_ANSWERS", $this->lng->txt("feedback_answers"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			if (array_key_exists("imagemap_x", $_POST))
			{
				$force_active = true;
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", "addSuggestedSolution",
					 "cancelExplorer", "linkChilds", "removeSuggestedSolution",
					 "uploadingImage", "uploadingImagemap", "addArea",
					"deletearea", "saveShape", "back", "saveEdit"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
}
?>
