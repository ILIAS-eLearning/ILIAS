<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjStyleSheetGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjStyleSheetGUI:
*
* @extends ilObjectGUI
*/

require_once "./classes/class.ilObjectGUI.php";
require_once "./Services/Style/classes/class.ilObjStyleSheet.php";

class ilObjStyleSheetGUI extends ilObjectGUI
{
	var $cmd_update;
	var $cmd_new_par;
	var $cmd_refresh;
	var $cmd_delete;

	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSheetGUI($a_data,$a_id,$a_call_by_reference, $a_prep = true)
	{
		global $ilCtrl, $lng, $tpl;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("style");
		$ilCtrl->saveParameter($this, array("tag", "style_type"));
		if ($_GET["style_type"] != "")
		{
			$this->super_type = ilObjStyleSheet::_getStyleSuperTypeForType($_GET["style_type"]);
		}
		
		$this->type = "sty";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("edit");

		$this->prepareOutput();
		switch($next_class)
		{
			default:
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}
	
	function viewObject()
	{
		$this->editObject();
	}

	/**
	* create
	*/
	function createObject()
	{
		global $rbacsystem, $lng, $tpl;

		//$this->setTabs();
		

		$this->lng =& $lng;
		//$this->ctrl->setParameter($this,'new_type',$this->type);
		$this->getTemplateFile("create", "sty");

		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("sty_create_new_stylesheet"));

		$this->tpl->setVariable("TXT_STYLE_BY_IMPORT", $this->lng->txt("sty_import_stylesheet"));
		$this->tpl->setVariable("TXT_STYLE_BY_COPY", $this->lng->txt("sty_copy_other_stylesheet"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("import_file"));
		$this->tpl->setVariable("TXT_SOURCE", $this->lng->txt("sty_source"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		
		$this->ctrl->setParameter($this, "new_type", "sty");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("TXT_COPY", $this->lng->txt("copy"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		// get all learning module styles
		$clonable_styles = ilObjStyleSheet::_getClonableContentStyles();
		$select = ilUtil::formSelect("", "source_style", $clonable_styles, false, true);
		$this->tpl->setVariable("SOURCE_SELECT", $select);
	}
	
	/**
	* edit style sheet
	*/
	function editObject()
	{
		global $rbacsystem, $lng, $ilTabs, $ilCtrl;
//ilObjStyleSheet::_addMissingStyleClassesToAllStyles();
		$this->setSubTabs();
		
		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();

		$ctpl = new ilTemplate("tpl.sty_classes.html", true, true, "Services/Style");

		// output characteristics
		$chars = $this->object->getCharacteristics();
		
		$style_type = ($this->super_type != "")
			? $this->super_type
			: "text_block";
		$ilCtrl->setParameter($this, "style_type", $style_type);

		$ilTabs->setSubTabActive("sty_".$style_type."_char");

		include_once("./Services/Style/classes/class.ilStyleTableGUI.php");
		$table_gui = new ilStyleTableGUI($this, "edit", $chars, $style_type);
		
		$ctpl->setCurrentBlock("style_table");
		$ctpl->setVariable("STYLE_TABLE", $table_gui->getHTML());
		$ctpl->parseCurrentBlock();

		$this->tpl->setContent($ctpl->get());
	}

	/**
	* Properties
	*/
	function propertiesObject()
	{
		global $rbacsystem, $lng;

		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();

		$this->getTemplateFile("edit", "sty");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit_stylesheet"));
		
		// add button button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// export button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportStyle"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();

		// title and description
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable(strtoupper("TITLE"), $this->object->getTitle());
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable(strtoupper("DESCRIPTION"), $this->object->getDescription());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_return"));
		$this->tpl->setVariable("BTN_SAVE", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}
	
	/**
	* save and refresh tag editing
	*/
	function refreshTagStyleObject()
	{
		global $ilCtrl;
		
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];

		$this->initTagStyleForm("edit", $cur_tag);

		if ($this->form_gui->checkInput())
		{
			$this->saveTagStyle();
			$ilCtrl->redirect($this, "editTagStyle");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->outputTagStyleEditScreen();
		}
	}

	/**
	* save and refresh tag editing
	*/
	function updateTagStyleObject()
	{
		global $ilCtrl;
		
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];

		$this->initTagStyleForm("edit", $cur_tag);
		if ($this->form_gui->checkInput())
		{
			$this->saveTagStyle();
			$ilCtrl->redirect($this, "edit");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->outputTagStyleEditScreen();
		}
	}

	/**
	* Save tag style.
	*/
	function saveTagStyle()
	{
		$cur = explode(".", $_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		$avail_pars = ilObjStyleSheet::_getStyleParameters($cur_tag);
		foreach ($avail_pars as $par => $v)
		{
			$var = str_replace("-", "_", $par);
			$basepar_arr = explode(".", $par);
			$basepar = $basepar_arr[0];
//var_dump($basepar_arr);
			if ($basepar_arr[1] != "" && $basepar_arr[1] != $cur_tag)
			{
				continue;
			}

			switch ($v["input"])
			{
				case "fontsize":
				case "numeric_no_perc":
				case "numeric":
				case "background_image":
					$in = $this->form_gui->getItemByPostVar($basepar);
//echo "<br>-".$cur_tag."-".$cur_class."-".$basepar."-".$_GET["style_type"]."-";
					$this->writeStylePar($cur_tag, $cur_class, $basepar, $in->getValue(), $_GET["style_type"]);
					break;

				case "color":
					$color = trim($_POST[$basepar]);
					if ($color != "")
					{
						$color = "#".$color;
					}
					$this->writeStylePar($cur_tag, $cur_class, $basepar, $color, $_GET["style_type"]);
					break;
					
				case "trbl_numeric":
				case "border_width":
				case "border_style":
					$in = $this->form_gui->getItemByPostVar($basepar);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][0], $in->getAllValue(), $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][1], $in->getTopValue(), $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][2], $in->getRightValue(), $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][3], $in->getBottomValue(), $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][4], $in->getLeftValue(), $_GET["style_type"]);
					break;

				case "trbl_color":
					$in = $this->form_gui->getItemByPostVar($basepar);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][0],
						trim($in->getAllValue() != "") ? "#".$in->getAllValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][1],
						trim($in->getTopValue() != "") ? "#".$in->getTopValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][2],
						trim($in->getRightValue() != "") ? "#".$in->getRightValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][3],
						trim($in->getBottomValue() != "") ? "#".$in->getBottomValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][4],
						trim($in->getLeftValue() != "") ? "#".$in->getLeftValue() : "", $_GET["style_type"]);
					break;

				case "background_position":
					$in = $this->form_gui->getItemByPostVar($basepar);
					$this->writeStylePar($cur_tag, $cur_class, $basepar, $in->getValue(), $_GET["style_type"]);
					break;
					
				default:
					$this->writeStylePar($cur_tag, $cur_class, $basepar, $_POST[$basepar], $_GET["style_type"]);
					break;
			}
		}

		$this->object->update();
	}
	
	function writeStylePar($cur_tag, $cur_class, $par, $value, $a_type)
	{
		if ($a_type == "")
		{
			return;
		}
		
		if ($value != "")
		{
			$this->object->replaceStylePar($cur_tag, $cur_class, $par, $value, $a_type);
		}
		else
		{
			$this->object->deleteStylePar($cur_tag, $cur_class, $par, $a_type);
		}
	}
	
	/**
	* Edit tag style.
	*
	*/
	function editTagStyleObject()
	{
		global $tpl;

		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		
		$this->initTagStyleForm("edit", $cur_tag);
		$this->getValues();
		$this->outputTagStyleEditScreen();
	}
	
	/**
	* Output tag style edit screen.
	*/
	function outputTagStyleEditScreen()
	{
		global $tpl, $ilCtrl, $lng;
		
		// set style sheet
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));

		$ts_tpl = new ilTemplate("tpl.style_tag_edit.html", true, true, "Services/Style");
		
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];

		$ts_tpl->setVariable("EXAMPLE",
			ilObjStyleSheetGUI::getStyleExampleHTML($_GET["style_type"], $cur_class));

		$ts_tpl->setVariable("FORM",
			$this->form_gui->getHtml());
		
		$tpl->setTitle($cur_class." (".$lng->txt("sty_type_".$_GET["style_type"]).")");
		
		$tpl->setContent($ts_tpl->get());
	}

	
	/**
	* Init tag style editing form
	*
	* @param        int        $a_mode        Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
	*/
	public function initTagStyleForm($a_mode, $a_cur_tag)
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		$avail_pars = $this->object->getAvailableParameters();
		$groups = $this->object->getStyleParameterGroups();
		
		// output select lists
		foreach ($groups as $k => $group)
		{
			// filter groups of properties that should only be
			// displayed with matching tag
			$filtered_groups = ilObjStyleSheet::_getFilteredGroups();
			if (is_array($filtered_groups[$k]) && !in_array($a_cur_tag, $filtered_groups[$k]))
			{
				continue;
			}

			$sh = new ilFormSectionHeaderGUI();
			$sh->setTitle($lng->txt("sty_".$k));
			$this->form_gui->addItem($sh);
			
			foreach ($group as $par)
			{
				$basepar = explode(".", $par);
				$basepar = $basepar[0];
				
				$var = str_replace("-", "_", $basepar);
				$up_par = strtoupper($var);

				switch (ilObjStyleSheet::_getStyleParameterInputType($par))
				{
					case "select":
						$sel_input = new ilSelectInputGUI($lng->txt("sty_".$var), $basepar);
						$options = array("" => "");
						foreach ($avail_pars[$par] as $p)
						{
							$options[$p] = $p;
						}
						$sel_input->setOptions($options);
						$this->form_gui->addItem($sel_input);
						break;
					
					case "text":
						$text_input = new ilTextInputGUI($lng->txt("sty_".$var), $basepar);
						$text_input->setMaxLength(200);
						$text_input->setSize(20);
						$this->form_gui->addItem($text_input);
						break;

					case "fontsize":
						include_once("./Services/Style/classes/class.ilFontSizeInputGUI.php");
						$fs_input = new ilFontSizeInputGUI($lng->txt("sty_".$var), $basepar);
						$this->form_gui->addItem($fs_input);
						break;
						
					case "numeric_no_perc":
					case "numeric":
						include_once("./Services/Style/classes/class.ilNumericStyleValueInputGUI.php");
						$num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_".$var), $basepar);
						if (ilObjStyleSheet::_getStyleParameterInputType($par) == "numeric_no_perc")
						{
							$num_input->setAllowPercentage(false);
						}
						$this->form_gui->addItem($num_input);
						break;
						
					case "percentage":
						$per_input = new ilNumberInputGUI($lng->txt("sty_".$var), $basepar);
						$per_input->setMinValue(0);
						$per_input->setMaxValue(100);
						$per_input->setMaxLength(3);
						$per_input->setSize(3);
						$this->form_gui->addItem($per_input);
						break;

					case "color":
						//include_once("./Services/Style/classes/class.ilNumericStyleValueInputGUI.php");
						$col_input = new ilColorPickerInputGUI($lng->txt("sty_".$var), $basepar);
						$col_input->setDefaultColor("");
						$this->form_gui->addItem($col_input);
						break;

					case "trbl_numeric":
						include_once("./Services/Style/classes/class.ilTRBLNumericStyleValueInputGUI.php");
						$num_input = new ilTRBLNumericStyleValueInputGUI($lng->txt("sty_".$var), $basepar);
						if (ilObjStyleSheet::_getStyleParameterInputType($par) == "trbl_numeric_no_perc")
						{
							$num_input->setAllowPercentage(false);
						}
						$this->form_gui->addItem($num_input);
						break;

					case "border_width":
						include_once("./Services/Style/classes/class.ilTRBLBorderWidthInputGUI.php");
						$bw_input = new ilTRBLBorderWidthInputGUI($lng->txt("sty_".$var), $basepar);
						$this->form_gui->addItem($bw_input);
						break;

					case "border_style":
						include_once("./Services/Style/classes/class.ilTRBLBorderStyleInputGUI.php");
						$bw_input = new ilTRBLBorderStyleInputGUI($lng->txt("sty_".$var), $basepar);
						$this->form_gui->addItem($bw_input);
						break;

					case "trbl_color":
						include_once("./Services/Style/classes/class.ilTRBLColorPickerInputGUI.php");
						$col_input = new ilTRBLColorPickerInputGUI($lng->txt("sty_".$var), $basepar);
						$this->form_gui->addItem($col_input);
						break;

					case "background_image":
						include_once("./Services/Style/classes/class.ilBackgroundImageInputGUI.php");
						$im_input = new ilBackgroundImageInputGUI($lng->txt("sty_".$var), $basepar);
						$imgs = array();
						foreach ($this->object->getImages() as $entry)
						{
							$imgs[] = $entry["entry"];
						}
						$im_input->setImages($imgs);
						$this->form_gui->addItem($im_input);
						break;

					case "background_position":
						include_once("./Services/Style/classes/class.ilBackgroundPositionInputGUI.php");
						$im_input = new ilBackgroundPositionInputGUI($lng->txt("sty_".$var), $basepar);
						$this->form_gui->addItem($im_input);
						break;
				}
			}
		}
		
		// save and cancel commands
		$this->form_gui->addCommandButton("updateTagStyle", $lng->txt("save_return"));
		$this->form_gui->addCommandButton("refreshTagStyle", $lng->txt("save_refresh"));
		
		$this->form_gui->setTitle($lng->txt("edit"));
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this));
	}
	
	/**
	* FORM: Get current values from persistent object.
	*
	*/
	public function getValues()
	{
		$style = $this->object->getStyle();
		$cur = explode(".",$_GET["tag"]);
		$cur_tag = $cur[0];
		$cur_class = $cur[1];
		$cur_parameters = $this->extractParametersOfTag($cur_tag, $cur_class, $style, $_GET["style_type"]);
		$parameters = ilObjStyleSheet::_getStyleParameters();
		foreach($parameters as $p => $v)
		{
			$filtered_groups = ilObjStyleSheet::_getFilteredGroups();
			if (is_array($filtered_groups[$v["group"]]) && !in_array($cur_tag, $filtered_groups[$v["group"]]))
			{
				continue;
			}
			$p = explode(".", $p);
			$p = $p[0];
			$input = $this->form_gui->getItemByPostVar($p);
			switch ($v["input"])
			{
				case "":
					break;
					
				case "trbl_numeric":
				case "border_width":
				case "border_style":
				case "trbl_color":
					$input->setAllValue($cur_parameters[$v["subpar"][0]]);
					$input->setTopValue($cur_parameters[$v["subpar"][1]]);
					$input->setRightValue($cur_parameters[$v["subpar"][2]]);
					$input->setBottomValue($cur_parameters[$v["subpar"][3]]);
					$input->setLeftValue($cur_parameters[$v["subpar"][4]]);
					break;
					
				default:
					$input->setValue($cur_parameters[$p]);
					break;
			}
		}
	}
	
	/**
	* export style
	*/
	function exportStyleObject()
	{
		$file = $this->object->export();
		
		ilUtil::deliverFile($file, "sty_".$this->object->getId().".zip");
	}

	function extractParametersOfTag($a_tag, $a_class, $a_style, $a_type)
	{
		$parameters = array();
		foreach($a_style as $tag)
		{
			foreach($tag as $par)
			{
				if ($par["tag"] == $a_tag && $par["class"] == $a_class
					&& $par["type"] == $a_type)
				{
					$parameters[$par["parameter"]] = $par["value"]; 
				}
			}
		}
		return $parameters;
	}
	
	/**
	* add style parameter
	*/
	function newStyleParameterObject()
	{
		$this->object->addParameter($_POST["tag"], $_POST["parameter"]);
		$this->editObject();
	}

	/**
	* refresh style sheet
	*/
	function refreshObject()
	{
		//$class_name = "ilObjStyleSheet";
		//require_once("classes/class.ilObjStyleSheet.php");
		$this->object->setTitle($_POST["style_title"]);
		$this->object->setDescription($_POST["style_description"]);

		foreach($_POST["styval"] as $id => $value)
		{
			$this->object->updateStyleParameter($id, $value);
		}
		$this->object->update();
		$this->editObject();
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
 	*/
	function deleteObject($a_error = false)
	{
		//$this->setTabs();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html");

		if(!$a_error)
		{
			ilUtil::sendInfo($this->lng->txt("info_delete_sure"));
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT", $this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();
		
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		$this->tpl->setCurrentBlock("table_row");
		$this->tpl->setVariable("IMG_OBJ",ilUtil::getImagePath("icon_styf.gif"));
		$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
		$this->tpl->setVariable("TEXT_CONTENT",ilObject::_lookupTitle($this->object->getId()));
		$this->tpl->parseCurrentBlock();
		
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		$buttons = array("confirmedDelete"  => $this->lng->txt("confirm"),
			"cancelDelete"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	
	/**
	* cancel oobject deletion
	*/
	function cancelDeleteObject()
	{
		$this->ctrl->returnToParent($this);
	}

	/**
	* delete selected style objects
	*/
	function confirmedDeleteObject()
	{
		global $ilias;
		
		$this->object->delete();
		
		$this->ctrl->returnToParent($this);
	}

	/**
	* delete style parameters
	*/
	function deleteStyleParameterObject()
	{
		if (is_array($_POST["sty_select"]))
		{
			foreach($_POST["sty_select"] as $id => $dummy)
			{
				$this->object->deleteParameter($id);
			}
		}
		$this->object->read();
		$this->object->writeCSSFile();
		$this->editObject();
	}

	/**
	* save style sheet
	*/
	function saveObject()
	{
//echo "HH"; exit;
		$class_name = "ilObjStyleSheet";
		require_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$newObj = new ilObjStyleSheet();
		$newObj->setTitle("-");
		$newObj->create();
		$newObj->setTitle(ilUtil::stripSlashes($_POST["style_title"]));
		$newObj->setDescription(ilUtil::stripSlashes($_POST["style_description"]));
		$newObj->update();

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($newObj->getId());
				$fold->update();
				ilObjStyleSheet::_writeStandard($newObj->getId(), "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $newObj->getId();
	}

	/**
	* update style sheet
	*/
	function updateObject()
	{
		global $lng;
		
		$this->object->setTitle(ilUtil::stripSlashes($_POST["style_title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["style_description"]));

		$this->object->update();
		ilUtil::sendInfo($lng->txt("msg_saved_modifications"));
		$this->ctrl->redirect($this, "properties");
	}

	/**
	* save style sheet
	*/
	function copyStyleObject()
	{
		global $ilias;
		
		if ($_POST["source_style"] > 0)
		$style_obj =& $ilias->obj_factory->getInstanceByObjId($_POST["source_style"]);
		$new_id = $style_obj->ilClone();

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($new_id);
				$fold->update();
				ilObjStyleSheet::_writeStandard($new_id, "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $new_id;
	}

	/**
	* import style sheet
	*/
	function importStyleObject()
	{
		// check file
		$source = $_FILES["stylefile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		
		// check correct file type
		$info = pathinfo($_FILES["stylefile"]["name"]);
		if (strtolower($info["extension"]) != "zip" && strtolower($info["extension"]) != "xml")
		{
			$this->ilias->raiseError("File must be a zip or xml file!",$this->ilias->error_obj->MESSAGE);
		}

		$class_name = "ilObjStyleSheet";
		require_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$newObj = new ilObjStyleSheet();
		//$newObj->setTitle();
		//$newObj->setDescription($_POST["style_description"]);
		$newObj->import($_FILES["stylefile"]);
		//$newObj->createFromXMLFile($_FILES["stylefile"]["tmp_name"]);

		// assign style to style sheet folder,
		// if parent is style sheet folder
		if ($_GET["ref_id"] > 0)
		{

			$fold =& ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
			if ($fold->getType() == "stys")
			{
				$fold->addStyle($newObj->getId());
				$fold->update();
				ilObjStyleSheet::_writeStandard($newObj->getId(), "1");
				$this->ctrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
			}
		}

		return $newObj->getId();
	}

	/**
	* update style sheet
	*/
	function cancelObject()
	{
		global $lng;

		ilUtil::sendInfo($lng->txt("msg_cancel"), true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $lng;

		$this->getTabs($this->tabs_gui);

		if (strtolower(get_class($this->object)) == "ilobjstylesheet")
		{
			$this->tpl->setVariable("HEADER", $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("HEADER", $lng->txt("create_stylesheet"));
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $lng, $ilCtrl;
		
		if ($ilCtrl->getCmd() == "editTagStyle")
		{
			// back to upper context
			$tabs_gui->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "edit"));
		}
		else
		{
			// back to upper context
			$tabs_gui->setBackTarget($lng->txt("back"),
				$this->ctrl->getParentReturn($this));
	
			// style classes
			$tabs_gui->addTarget("sty_style_chars",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", ""),
				get_class($this));
	
			// images
			$tabs_gui->addTarget("sty_images",
				$this->ctrl->getLinkTarget($this, "listImages"), "listImages",
				get_class($this));
				
			// settings
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "properties"), "properties",
				get_class($this));
		}

	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function setSubTabs()
	{
		global $lng, $ilTabs, $ilCtrl;
		
		$types = ilObjStyleSheet::_getStyleSuperTypes();
		
		foreach ($types as $super_type => $types)
		{
			// text block characteristics
			$ilCtrl->setParameter($this, "style_type", $super_type);
			$ilTabs->addSubTabTarget("sty_".$super_type."_char",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", ""),
				get_class($this));
		}

		$ilCtrl->setParameter($this, "style_type", $_GET["style_type"]);
	}

	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings")	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
				
			$ilLocator->addItem(ilObject::_lookupTitle(
				ilObject::_lookupObjId($_GET["ref_id"])),
				$this->ctrl->getLinkTargetByClass("ilobjstylesettingsgui", "view"));

			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "edit"));
			}
		}
		else							// repository administration
		{
			//?
		}

	}
	
	function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
				$tpl->setUpperIcon(
					$this->ctrl->getLinkTargetByClass("ilobjstylesettingsgui",
						"editContentStyles"));
		}
		else
		{
			// ?
		}
	}

	/**
	* List images of style
	*/
	function listImagesObject()
	{
		global $tpl;
		
		include_once("./Services/Style/classes/class.ilStyleImageTableGUI.php");
		$table_gui = new ilStyleImageTableGUI($this, "listImages",
			$this->object);
		$tpl->setContent($table_gui->getHTML());
		
	}
	
	/**
	*
	*/
	function addImageObject()
	{
		global $tpl;
		
		$this->initImageForm();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Cancel Upload
	*/
	function cancelUploadObject()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listImages");
	}
	
	/**
	* Upload image
	*/
	function uploadImageObject()
	{
		global $tpl, $ilCtrl;
		
		$this->initImageForm();
		
		if ($this->form_gui->checkInput())
		{
			$this->object->uploadImage($_FILES["image_file"]);
			$ilCtrl->redirect($this, "listImages");
		}
		else
		{
			//$this->form_gui->setImageFormValuesByPost();
			$tpl->setContent($this->form_gui->getHTML());
		}

	}
	
	/**
	* Init image form
	*/
	function initImageForm()
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		$this->form_gui->setTitle($lng->txt("sty_add_image"));
		
		$file_input = new ilImageFileInputGUI($lng->txt("sty_image_file"), "image_file");
		$file_input->setRequired(true);
		$this->form_gui->addItem($file_input);
		
		$this->form_gui->addCommandButton("uploadImage", $lng->txt("upload"));
		$this->form_gui->addCommandButton("cancelUpload", $lng->txt("cancel"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Delete images
	*/
	function deleteImageObject()
	{
		global $ilCtrl;
		
		$images = $this->object->getImages();
		
		foreach ($images as $image)
		{
			if (is_array($_POST["file"]) && in_array($image["entry"], $_POST["file"]))
			{
				$this->object->deleteImage($image["entry"]);
			}
		}
		$ilCtrl->redirect($this, "listImages");
	}
	
	/**
	* Characteristic deletion confirmation screen
	*/
	function deleteCharacteristicConfirmationObject()
	{
		global $ilCtrl, $tpl, $lng;
		
//var_dump($_POST);

		if (!is_array($_POST["char"]) || count($_POST["char"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "edit");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("sty_confirm_char_deletion"));
			$cgui->setCancel($lng->txt("cancel"), "cancelCharacteristicDeletion");
			$cgui->setConfirm($lng->txt("delete"), "deleteCharacteristic");
			
			foreach ($_POST["char"] as $char)
			{
				$char_comp = explode(".", $char);
				$cgui->addItem("char[]", $char, $char_comp[2]);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	* Cancel characteristic deletion
	*/
	function cancelCharacteristicDeletionObject()
	{
		global $ilCtrl, $lng;
		
		ilUtil::sendInfo($lng->txt("action_aborted"), true);
		$ilCtrl->redirect($this, "edit");
	}
	
	/**
	* Delete one or multiple style characteristic
	*/
	function deleteCharacteristicObject()
	{
		global $ilCtrl;
		
		if (is_array($_POST["char"]))
		{
			foreach($_POST["char"] as $char)
			{
				$char_comp = explode(".", $char);
				$type = $char_comp[0];
				$tag = $char_comp[1];
				$class = $char_comp[2];
				
				$this->object->deleteCharacteristic($type, $tag, $class);
			}
		}

		$ilCtrl->redirect($this, "edit");
	}
	
	/**
	* Add characteristic
	*/
	function addCharacteristicFormObject()
	{
		global $tpl;
		
		$this->initCharacteristicForm("create");
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Save Characteristic
	*/
	function saveCharacteristicObject()
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->initCharacteristicForm("create");

		if ($this->form_gui->checkInput())
		{
			if ($this->object->characteristicExists($_POST["new_characteristic"], $_GET["style_type"]))
			{
				$char_input = $this->form_gui->getItemByPostVar("new_characteristic");
				$char_input->setAlert($lng->txt("sty_characteristic_already_exists"));
			}
			else
			{
				$this->object->addCharacteristic($_POST["type"], $_POST["new_characteristic"]);
				ilUtil::sendInfo($lng->txt("sty_added_characteristic"), true);
				$ilCtrl->redirect($this, "edit");
			}
		}
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Init tag style editing form
	*
	* @param        int        $a_mode        Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
	*/
	public function initCharacteristicForm($a_mode)
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		// title
		$txt_input = new ilRegExpInputGUI($lng->txt("title"), "new_characteristic");
		$txt_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
		$txt_input->setNoMatchMessage($lng->txt("sty_msg_characteristic_must_only_include")." A-Z, a-z, 1-9");
		$txt_input->setRequired(true);
		$this->form_gui->addItem($txt_input);
		
		// type
		$all_super_types = ilObjStyleSheet::_getStyleSuperTypes();
		$types = $all_super_types[$this->super_type];
		$exp_types = array();
		foreach($types as $t)
		{
			if (ilObjStyleSheet::_isExpandable($t))
			{
				$exp_types[$t] = $lng->txt("sty_type_".$t);
			}
		}
		if (count($exp_types) > 1)
		{
			$type_input = new ilSelectInputGUI($lng->txt("sty_type"), "type");
			$type_input->setOptions($exp_types);
			$type_input->setValue(key($exp_types));
			$this->form_gui->addItem($type_input);
		}
		else if (count($exp_types) == 1)
		{
			$hid_input = new ilHiddenInputGUI("type");
			$hid_input->setValue(key($exp_types));
			$this->form_gui->addItem($hid_input);
		}
		
		$this->form_gui->setTitle($lng->txt("sty_add_characteristic"));
		$this->form_gui->addCommandButton("saveCharacteristic", $lng->txt("save"));
		$this->form_gui->addCommandButton("edit", $lng->txt("cancel"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get style example HTML
	*/
	static function getStyleExampleHTML($a_type, $a_class)
	{
		$ex_tpl = new ilTemplate("tpl.style_example.html", true, true, "Services/Style");
		
		$ex_tpl->setCurrentBlock("Example_".$a_type);
		$ex_tpl->setVariable("EX_CLASS", "ilc_".$a_type."_".$a_class);
		$ex_tpl->setVariable("EX_TEXT", "ABC abc 123");
		if ($a_type == "media_cont")
		{
			$ex_tpl->setVariable("IMG_MEDIA_DISABLED", ilUtil::getImagePath("media_disabled.gif"));
		}
		$ex_tpl->parseCurrentBlock();

		return $ex_tpl->get();
	}

} // END class.ObjStyleSheetGUI
?>
