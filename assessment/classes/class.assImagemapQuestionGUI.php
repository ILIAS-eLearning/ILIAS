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

require_once "class.assQuestionGUI.php";
require_once "class.assImagemapQuestion.php";

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
  function showEditForm() {
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_imagemap_question.html", true);
		$this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_as_qpl_other_question_data.html", true);
		
		if ($this->object->get_answer_count())
		{
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
			$this->tpl->setVariable("COORDINATES", $answer->get_coords());
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
			$imagepath = $this->object->getImagePathWeb() . $img;
			$this->tpl->setVariable("UPLOADED_IMAGE", "<img src=\"$imagepath.thumb.jpg\" alt=\"$img\" border=\"\" />");
		} 
		else 
		{
			$this->tpl->setVariable("VALUE_IMAGE_UPLOAD", $this->lng->txt("upload"));
		}
		
		// imagemap block
		$imgmap = $this->object->get_imagemap_filename();
		$this->tpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap"));
		if (!empty($imgmap)) 
		{
			$this->tpl->setVariable("IMAGEMAP_FILENAME", $imgmap);
			$this->tpl->setVariable("VALUE_IMAGEMAP_UPLOAD", $this->lng->txt("change"));
			$this->tpl->setCurrentBlock("imagemapupload");
			$this->tpl->setVariable("UPLOADED_IMAGEMAP", $imgmap);
			$this->tpl->parse("imagemapupload");
		} 
		else 
		{
			$this->tpl->setVariable("VALUE_IMAGEMAP_UPLOAD", $this->lng->txt("upload"));
		}
		$this->tpl->setVariable("IMAGEMAP_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_IMAGEMAP_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("VALUE_IMAGEMAP_COMMENT", $this->object->getComment());
		$this->tpl->setVariable("VALUE_IMAGEMAP_AUTHOR", $this->object->getAuthor());
		$this->tpl->setVariable("VALUE_QUESTION", $this->object->get_question());
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("APPLY",$this->lng->txt("apply"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("ACTION_IMAGEMAP_QUESTION", $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=question&sel_question_types=qt_imagemap");
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

/**
* Evaluates a posted edit form and writes the form data in the question object
*
* Evaluates a posted edit form and writes the form data in the question object
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function writePostData() {
		$result = 0;
		$saved = false;

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
			if (($_POST["cmd"]["uploadingImage"]) and (!empty($_FILES['imageName']['tmp_name'])))
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields_upload_image"));
			}
				else if (($_POST["cmd"]["uploadingImagemap"]) and (!empty($_FILES['imagemapName']['tmp_name'])))
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields_upload_imagemap"));
			}
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
    $this->tpl->setVariable("IMAGEMAP", $this->object->get_imagemap_contents($formaction));
		if ((array_key_exists(0, $solutions)) and (isset($solutions[0]->value1))) 
		{
			$formaction .= "&selimage=" . $solutions[0]->value1;
			if (strcmp($this->object->answers[$solutions[0]->value1]->get_area(), "rect") == 0) 
			{
				$imagepath_working = $this->object->getImagePath() . $this->object->get_image_filename();
				$coords = $this->object->answers[$solutions[0]->value1]->get_coords();
				preg_match("/(\d+),(\d+),(\d+),(\d+)/", $coords, $matches);
				$x0 = $matches[1];
				$y0 = $matches[2];
				$x1 = $matches[3];
				$y1 = $matches[4];
				// draw a rect around the selection
				$convert_cmd = ilUtil::getConvertCmd() . " -quality 100 " .
				"-stroke white -fill none -linewidth 5 -draw \"rectangle " .
				$x0 . "," . $y0 .	" " . ($x1) . "," . $y1 . "\" " .
				"-stroke red -fill none -linewidth 3 -draw \"rectangle " .
				$x0 . "," . $y0 .	" " . ($x1) . "," . $y1 . "\" " .
				 " $imagepath_working $imagepath_working.sel" . $ilUser->id . ".jpg";
				system($convert_cmd);
			} 
			else if (strcmp($this->object->answers[$solutions[0]->value1]->get_area(), "circle") == 0) 
			{
				$imagepath_working = $this->object->getImagePath() . $this->object->get_image_filename();
				$coords = $this->object->answers[$solutions[0]->value1]->get_coords();
				preg_match("/(\d+),(\d+),(\d+)/", $coords, $matches);
				$x = $matches[1];
				$y = $matches[2];
				$r = $matches[3];
				// draw a circle around the selection
				$convert_cmd = ilUtil::getConvertCmd() . " -quality 100 " .
				"-stroke white -fill none -linewidth 5 -draw \"circle " .
				$x . "," . $y .	" " . ($x+$r) . "," . $y . "\" " .
				"-stroke red -fill none -linewidth 3 -draw \"circle " .
				$x . "," . $y .	" " . ($x+$r) . "," . $y . "\" " .
				 " $imagepath_working $imagepath_working.sel" . $ilUser->id . ".jpg";
				system($convert_cmd);
			} 
			else if (strcmp($this->object->answers[$solutions[0]->value1]->get_area(), "poly") == 0) 
			{
				$imagepath_working = $this->object->getImagePath() . $this->object->get_image_filename();
				$coords = $this->object->answers[$solutions[0]->value1]->get_coords();
				preg_match("/(\d+),(\d+),(\d+)/", $coords, $matches);
				$x = $matches[1];
				$y = $matches[2];
				$r = $matches[3];
				// draw a polygon around the selection
				$convert_cmd = ilUtil::getConvertCmd() . " -quality 100 ";
				$convert_cmd .= "-stroke white -fill none -linewidth 5 -draw \"polygon ";
				preg_match_all("/(\d+),(\d+)/", $coords, $matches, PREG_PATTERN_ORDER);
				for ($i = 0; $i < count($matches[0]); $i++) 
				{
					$convert_cmd .= $matches[1][$i] . "," . $matches[2][$i] .	" ";
				}
				$convert_cmd .= "\" ";
				$convert_cmd .= "-stroke red -fill none -linewidth 3 -draw \"polygon ";
				preg_match_all("/(\d+),(\d+)/", $coords, $matches, PREG_PATTERN_ORDER);
				for ($i = 0; $i < count($matches[0]); $i++) 
				{
					$convert_cmd .= $matches[1][$i] . "," . $matches[2][$i] .	" ";
				}
				$convert_cmd .= "\" " .
				 " $imagepath_working $imagepath_working.sel" . $ilUser->id . ".jpg";
				system($convert_cmd);
			}
		}
		if (file_exists($this->object->getImagePath() . $this->object->get_image_filename() . ".sel" . $ilUser->id . ".jpg")) 
		{
			$imagepath = "displaytempimage.php?gfx=" . $this->object->getImagePath() . $this->object->get_image_filename() . ".sel" . $ilUser->id . ".jpg";
		} 
		else 
		{
			$imagepath = $this->object->getImagePathWeb() . $this->object->get_image_filename();
		}
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
