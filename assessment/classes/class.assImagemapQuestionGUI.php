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

require_once "./assessment/classes/class.assQuestionGUI.php";
require_once "./assessment/classes/class.assImagemapQuestion.php";
require_once "./assessment/classes/class.ilImagemapPreview.php";

/**
* Image map question GUI representation
*
* The ASS_ImagemapQuestionGUI class encapsulates the GUI representation
* for image map questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assImagemapQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_ImagemapQuestionGUI extends ASS_QuestionGUI {
/**
* ASS_ImagemapQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_ImagemapQuestionGUI object.
*
* @param integer $id The database id of a image map question object
* @access public
*/
  function ASS_ImagemapQuestionGUI(
		$id = -1
  )

  {
		$this->ASS_QuestionGUI();
		$this->object = new ASS_ImagemapQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

/**
* Returns the question type string
*
* Returns the question type string
*
* @result string The question type string
* @access public
*/
	function getQuestionType()
	{
		return "qt_imagemap";
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
		$this->getQuestionTemplate("qt_imagemap");
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_imagemap_question.html", true);
		$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);
		if (($_POST["cmd"]["addarea"] or $_GET["editmap"]) and (!$_POST["cmd"]["back"]) and (!$_POST["cmd"]["saveshape"]))
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
			$preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->get_image_filename());
			foreach ($this->object->answers as $index => $answer)
			{
				$preview->addArea($answer->get_area(), $answer->get_coords(), $answer->get_answertext(), "", "", true);
			}
			$hidearea = false;
			$disabled_save = " disabled=\"disabled\"";
			$coords = "";
			switch ($_POST["newarea"])
			{
				case "rect":
					if (count($this->object->coords) == 0)
					{
						sendInfo($this->lng->txt("rectangle_click_tl_corner"));
					}
					else if (count($this->object->coords) == 1)
					{
						sendInfo($this->lng->txt("rectangle_click_br_corner"));
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
						sendInfo($this->lng->txt("circle_click_center"));
					}
					else if (count($this->object->coords) == 1)
					{
						sendInfo($this->lng->txt("circle_click_circle"));
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
						sendInfo($this->lng->txt("polygon_click_starting_point"));
					}
					else if (count($this->object->coords) == 1)
					{
						sendInfo($this->lng->txt("polygon_click_next_point"));
					}
					else if (count($this->object->coords) > 1)
					{
						sendInfo($this->lng->txt("polygon_click_next_or_save"));
						$disabled_save = "";
						$coords = join($this->object->coords, ",");
					}
					break;
			}
			if ($coords)
			{
				$preview->addArea($_POST["newarea"], $coords, $_POST["shapetitle"], "", "", true, "blue");
			}
			$preview->createPreview();
			if (count($preview->areas))
			{
				$imagepath = "displaytempimage.php?gfx=" . $preview->getPreviewFilename();
			}
			else
			{
				$imagepath = $this->object->getImagePathWeb() . $this->object->get_image_filename();
			}
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
			$this->ctrl->setParameter($this, "sel_question_types", "qt_imagemap");
			$this->ctrl->setParameter($this, "editmap", "1");
			$this->tpl->setVariable("ACTION_IMAGEMAP_QUESTION",
				$this->ctrl->getFormaction($this));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			if ($this->object->get_answer_count())
			{
				$this->tpl->setCurrentBlock("deletebutton");
				$this->tpl->setVariable("DELETE_AREA", $this->lng->txt("delete_area"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("answerheader");
				$this->tpl->setVariable("TEXT_NAME", $this->lng->txt("name"));
				$this->tpl->setVariable("TEXT_TRUE", $this->lng->txt("true"));
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("TEXT_SHAPE", $this->lng->txt("shape"));
				$this->tpl->setVariable("TEXT_COORDINATES", $this->lng->txt("coordinates"));
				$this->tpl->parseCurrentBlock();
			}
			$tblrow = array("tblrow1", "tblrow2");
			for ($i = 0; $i < $this->object->get_answer_count(); $i++)
			{
				$this->tpl->setCurrentBlock("answers");
				$answer = $this->object->get_answer($i);
				$this->tpl->setVariable("ANSWER_ORDER", $answer->get_order());
				$this->tpl->setVariable("VALUE_ANSWER", $answer->get_answertext());
				$this->tpl->setVariable("TEXT_POINTS", $this->lng->txt("points"));
				$this->tpl->setVariable("VALUE_IMAGEMAP_POINTS", $answer->get_points());
				$this->tpl->setVariable("COLOR_CLASS", $tblrow[$i % 2]);
				if ($answer->is_true())
				{
					$this->tpl->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
				$coords = "";
				switch ($answer->get_area())
				{
					case "poly":
					case "rect":
						$coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $answer->get_coords());
						break;
					case "circle":
						$coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $answer->get_coords());
						break;
				}
				$this->tpl->setVariable("COORDINATES", $coords);
				$this->tpl->setVariable("AREA", $answer->get_area());
				$this->tpl->setVariable("TEXT_SHAPE", strtoupper($answer->get_area()));
				$this->tpl->parseCurrentBlock();
			}
			// call to other question data i.e. material, estimated working time block
			$this->outOtherQuestionData();
			// image block
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
				if (count($this->object->answers))
				{
					$preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->get_image_filename());
					foreach ($this->object->answers as $index => $answer)
					{
						$preview->addArea($answer->get_area(), $answer->get_coords(), $answer->get_answertext(), "", "", true);
					}
					$preview->createPreview();
					$imagepath = "displaytempimage.php?gfx=" . $preview->getPreviewFilename();
				}
				else
				{
					$imagepath = $this->object->getImagePathWeb() . $img;
				}
				$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath\" alt=\"$img\" border=\"\" />");
			}
			else
			{
				$this->tpl->setVariable("VALUE_IMAGE_UPLOAD", $this->lng->txt("upload"));
			}

			// imagemap block
			$imgmap = $this->object->get_imagemap_filename();
			$this->tpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap"));
			$this->tpl->setVariable("VALUE_IMAGEMAP_UPLOAD", $this->lng->txt("add_imagemap"));
			$this->tpl->setCurrentBlock("questioneditor");
			$this->tpl->setVariable("VALUE_IMAGEMAP_TITLE", $this->object->getTitle());
			$this->tpl->setVariable("VALUE_IMAGEMAP_COMMENT", $this->object->getComment());
			$this->tpl->setVariable("VALUE_IMAGEMAP_AUTHOR", $this->object->getAuthor());
			$this->tpl->setVariable("VALUE_QUESTION", $this->object->get_question());
			$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
			$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
			$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

			$this->tpl->setVariable("ADD_AREA", $this->lng->txt("add_area"));
			$this->tpl->setVariable("TEXT_RECT", $this->lng->txt("rectangle"));
			$this->tpl->setVariable("TEXT_CIRCLE", $this->lng->txt("circle"));
			$this->tpl->setVariable("TEXT_POLY", $this->lng->txt("polygon"));
			$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
			$this->tpl->setVariable("APPLY",$this->lng->txt("apply"));
			$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$this->ctrl->setParameter($this, "sel_question_types", "qt_imagemap");
			$this->tpl->setVariable("ACTION_IMAGEMAP_QUESTION",
				$this->ctrl->getFormaction($this));
			$this->tpl->setVariable("IMAGEMAP_ID", $this->object->getId());
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->parseCurrentBlock();
	}

/**
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* @access private
*/
  function outOtherQuestionData() {
		$colspan = " colspan=\"3\"";

		if (!empty($this->object->materials))
		{
			$this->tpl->setCurrentBlock("select_block");
			foreach ($this->object->materials as $key => $value)
			{
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("materiallist_block");
			$i = 1;
			foreach ($this->object->materials as $key => $value) 
			{
				$this->tpl->setVariable("MATERIAL_COUNTER", $i);
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->setVariable("MATERIAL_FILE_VALUE", $value);
				$this->tpl->parseCurrentBlock();
				$i++;
			}
			$this->tpl->setVariable("UPLOADED_MATERIAL", $this->lng->txt("uploaded_material"));
			$this->tpl->setVariable("VALUE_MATERIAL_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("COLSPAN_MATERIAL", $colspan);
			$this->tpl->parse("mainselect_block");
		}

		$this->tpl->setCurrentBlock("other_question_data");
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		$this->tpl->setVariable("TEXT_MATERIAL_FILE", $this->lng->txt("material_file"));
		$this->tpl->setVariable("VALUE_MATERIAL_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("COLSPAN_MATERIAL", $colspan);
		$this->tpl->parseCurrentBlock();
	}

	function uploadingImage()
	{
		$this->writePostData();
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

		if ($_POST["cmd"]["back"])
		{
			return $result;
		}

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
			if ($_POST["cmd"]["saveshape"])
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
				$this->object->add_answer($_POST["shapetitle"], 0, false, count($this->object->answers), $coords, $_POST["newarea"]);
			}
		}
		else
		{
			if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"])) $result = 1;

			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
			$this->object->set_question(ilUtil::stripSlashes($_POST["question"]));
			$this->object->setShuffle($_POST["shuffle"]);

			// adding estimated working time and materials uris
			$this->writeOtherPostData($result);

			if (($_POST["id"] > 0) or ($result != 1))
			{
				// Question is already saved, so imagemaps and images can be uploaded
				//setting image file
				if (empty($_FILES['imageName']['tmp_name'])) {
					$this->object->set_image_filename(ilUtil::stripSlashes($_POST["uploaded_image"]));
				}
				else
				{
					if ($this->object->getId() <= 0)
					{
						$this->object->saveToDb();
						$saved = true;
						sendInfo($this->lng->txt("question_saved_for_upload"));
					}
					$this->object->set_image_filename($_FILES['imageName']['name'], $_FILES['imageName']['tmp_name']);
				}

				//setting imagemap
				if (empty($_FILES['imagemapName']['tmp_name']))
				{
					$this->object->set_imagemap_filename(ilUtil::stripSlashes($_POST['uploaded_imagemap']));
					// Add all answers from the form into the object
					$this->object->flush_answers();
					foreach ($_POST as $key => $value)
					{
						if (preg_match("/answer_(\d+)/", $key, $matches))
						{
							if ($_POST["radio"] == $matches[1])
							{
								$is_true = TRUE;
							}
							else
							{
								$is_true = FALSE;
							}
							$this->object->add_answer(
								ilUtil::stripSlashes($_POST["$key"]),
								ilUtil::stripSlashes($_POST["points_$matches[1]"]),
								ilUtil::stripSlashes($is_true, $matches[1]),
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
						$saved = true;
						sendInfo($this->lng->txt("question_saved_for_upload"));
					}
					$this->object->set_imagemap_filename($_FILES['imagemapName']['name'], $_FILES['imagemapName']['tmp_name']);
				}
			}
			else
			{
				if (($this->ctrl->getCmd() == "uploadingImage") and (!empty($_FILES['imageName']['tmp_name'])))
				{
					sendInfo($this->lng->txt("fill_out_all_required_fields_upload_image"));
				}
					else if (($_POST["cmd"]["uploadingImagemap"]) and (!empty($_FILES['imagemapName']['tmp_name'])))
				{
					sendInfo($this->lng->txt("fill_out_all_required_fields_upload_imagemap"));
				}
			}
			if ($_POST["cmd"]["deletearea"])
			{
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
			}
		}
		if ($_POST["cmd"]["addarea"])
		{
			$this->object->saveToDb();
		}
		return $result;
  }

/**
* Creates the question output form for the learner
*
* Creates the question output form for the learner
*
* @access public
*/
	function outWorkingForm($test_id = "", $is_postponed = false, &$formaction)
	{
		global $ilUser;

    $this->tpl->addBlockFile("IMAGEMAP_QUESTION", "imagemapblock", "tpl.il_as_execute_imagemap_question.html", true);
		$solutions = array();
		$postponed = "";
		if ($test_id) 
		{
			$solutions =& $this->object->getSolutionValues($test_id);
		}
		if ($is_postponed) 
		{
			$postponed = " (" . $this->lng->txt("postponed") . ")";
		}
		if (!empty($this->object->materials)) 
		{
			$i=1;
			$this->tpl->setCurrentBlock("material_preview");
			foreach ($this->object->materials as $key => $value) 
			{
				$this->tpl->setVariable("COUNTER", $i++);
				$this->tpl->setVariable("VALUE_MATERIAL_DOWNLOAD", $key);
				$this->tpl->setVariable("URL_MATERIAL_DOWNLOAD", $this->object->getMaterialsPathWeb().$value);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("material_download");
			$this->tpl->setVariable("TEXT_MATERIAL_DOWNLOAD", $this->lng->txt("material_download"));
			$this->tpl->parseCurrentBlock();
		}

    $this->tpl->setCurrentBlock("imagemapblock");
    $this->tpl->setVariable("IMAGEMAP_QUESTION_HEADLINE", $this->object->getTitle());
    $this->tpl->setVariable("IMAGEMAP_QUESTION", $this->object->get_question());
		$imagepath = "";
		$preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->get_image_filename());
		foreach ($this->object->answers as $index => $answer)
		{
			if (!$test_id)
			{
				$href = "";
			}
			else
			{
				$href = $formaction . "&selimage=" . $answer->get_order();
			}
			$visible = false;
			if ((array_key_exists(0, $solutions)) and (isset($solutions[0]->value1))) 
			{
				if ($solutions[0]->value1 == $index)
				{
					$formaction .= "&selimage=" . $answer->get_order();
					$visible = true;
				}
			}
			$preview->addArea($answer->get_area(), $answer->get_coords(), $answer->get_answertext(), $href, "", $visible);
		}
		$preview->createPreview();
		$imagepath = "displaytempimage.php?gfx=" . $preview->getPreviewFilename();
		$this->tpl->setVariable("IMAGEMAP", $preview->getImagemap($this->object->getTitle()));
    $this->tpl->setVariable("IMAGE", $imagepath);
    $this->tpl->setVariable("IMAGEMAP_NAME", $this->object->getTitle() . $postponed);
    $this->tpl->parseCurrentBlock();
	}

/**
* Creates a preview of the question
*
* Creates a preview of the question
*
* @access private
*/
	function outPreviewForm()
	{
    $this->tpl->addBlockFile("IMAGEMAP_QUESTION", "imagemapblock", "tpl.il_as_execute_imagemap_question.html", true);
		$empty = $_SERVER['PHP_SELF'] . "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"] . "&preview=" . $_GET["preview"];
		$this->outWorkingForm("", "", $empty);
	}

}
?>
