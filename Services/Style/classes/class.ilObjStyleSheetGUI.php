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
		$ilCtrl->saveParameter($this, array("tag", "style_type", "temp_type"));
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
	* Include CSS in output
	*/
	function includeCSS()
	{
		// set style sheet
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			$this->object->getContentStylePath($this->object->getId()));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* edit style sheet
	*/
	function editObject()
	{
		global $rbacsystem, $lng, $ilTabs, $ilCtrl;
//ilObjStyleSheet::_addMissingStyleClassesToAllStyles();
		$this->setSubTabs();
		
		$this->includeCSS();

		$ctpl = new ilTemplate("tpl.sty_classes.html", true, true, "Services/Style");

		// output characteristics
		$chars = $this->object->getCharacteristics();
		
		$style_type = ($this->super_type != "")
			? $this->super_type
			: "text_block";
		$ilCtrl->setParameter($this, "style_type", $style_type);

		$ilTabs->setSubTabActive("sty_".$style_type."_char");

		include_once("./Services/Style/classes/class.ilStyleTableGUI.php");
		$table_gui = new ilStyleTableGUI($this, "edit", $chars, $style_type,
			$this->object);
		
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

		// add button button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// export button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportStyle"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("export"));
		$this->tpl->parseCurrentBlock();

		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$this->tpl->setContent($this->form->getHTML());
		
/*
		$this->getTemplateFile("edit", "sty");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit_stylesheet"));

		// title and description
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable(strtoupper("TITLE"), $this->object->getTitle());
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable(strtoupper("DESCRIPTION"), $this->object->getDescription());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_SAVE", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
*/
	}
	
	/**
	* Get current values for properties from 
	*
	*/
	public function getPropertiesValues()
	{
		$values = array();
	
		$values["style_title"] = $this->object->getTitle();
		$values["style_description"] = $this->object->getDescription();
		$values["disable_auto_margins"] = (int) $this->object->lookupStyleSetting("disable_auto_margins");
	
		$this->form->setValuesByArray($values);
	}
	
	/**
	* FORM: Init properties form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm($a_mode = "edit")
	{
		global $lng;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "style_title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "style_description");
		//$ta->setCols();
		//$ta->setRows();
		$this->form->addItem($ta);
		
		// disable automatic margins for left/right alignment
		$cb = new ilCheckboxInputGUI($this->lng->txt("sty_disable_auto_margins"), "disable_auto_margins");
		$cb->setInfo($this->lng->txt("sty_disable_auto_margins_info"));
		$this->form->addItem($cb);
		
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
		}
	                
		$this->form->setTitle($lng->txt("edit_stylesheet"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Update properties
	*/
	function updateObject()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->initPropertiesForm("edit");
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("style_title"));
			$this->object->setDescription($this->form->getInput("style_description"));
			$this->object->writeStyleSetting("disable_auto_margins",
				$this->form->getInput("disable_auto_margins"));
			$this->object->update();
			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "properties");
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
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
					if ($color != "" && trim(substr($color,0,1) != "!"))
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
					$tblr_p = array (0 => "getAllValue", 1 => "getTopValue", 2 => "getRightValue", 
						3 => "getBottomValue", 4 => "getLeftValue");
					foreach ($tblr_p as $k => $func)
					{
						$val = trim($in->$func());
						$val = (($in->getAcceptNamedColors() && substr($val, 0, 1) == "!")
							|| $val == "")
							? $val
							: "#".$val;
						$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][$k], $val, $_GET["style_type"]);
					}
					/*$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][0],
						trim($in->getAllValue() != "") ? "#".$in->getAllValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][1],
						trim($in->getTopValue() != "") ? "#".$in->getTopValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][2],
						trim($in->getRightValue() != "") ? "#".$in->getRightValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][3],
						trim($in->getBottomValue() != "") ? "#".$in->getBottomValue() : "", $_GET["style_type"]);
					$this->writeStylePar($cur_tag, $cur_class, $v["subpar"][4],
						trim($in->getLeftValue() != "") ? "#".$in->getLeftValue() : "", $_GET["style_type"]);
					*/
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
						$col_input->setAcceptNamedColors(true);
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
						$col_input->setAcceptNamedColors(true);
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
		global $lng, $ilCtrl, $ilTabs;
		
		if ($ilCtrl->getCmd() == "editTagStyle")
		{
			// back to upper context
			$tabs_gui->setBackTarget($lng->txt("back"),
				$ilCtrl->getLinkTarget($this, "edit"));
				
			$t = explode(".", $_GET["tag"]);
			$t2 = explode(":", $t[1]);
			$pc = $this->object->_getPseudoClasses($t[0]);
			if (is_array($pc) && count($pc) > 0)
			{
				// style classes
				$ilCtrl->setParameter($this, "tag", $t[0].".".$t2[0]);
				$tabs_gui->addTarget("sty_tag_normal",
					$this->ctrl->getLinkTarget($this, "editTagStyle"), array("editTagStyle", ""),
					get_class($this));
				if ($t2[1] == "")
				{
					$ilTabs->setTabActive("sty_tag_normal");
				}
				
				foreach ($pc as $p)
				{
					// style classes
					$ilCtrl->setParameter($this, "tag", $t[0].".".$t2[0].":".$p);
					$tabs_gui->addTarget("sty_tag_".$p,
						$this->ctrl->getLinkTarget($this, "editTagStyle"), array("editTagStyle", ""),
						get_class($this));
					if ($t2[1] == $p)
					{
						$ilTabs->setTabActive("sty_tag_".$p);
					}
				}
				$ilCtrl->setParameter($this, "tag", $_GET["tag"]);
			}
		}
		else
		{
			// back to upper context
			$tabs_gui->setBackTarget($lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "returnToUpperContext"));
	
			// style classes
			$tabs_gui->addTarget("sty_style_chars",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", ""),
				get_class($this));
	
			// colors
			$tabs_gui->addTarget("sty_colors",
				$this->ctrl->getLinkTarget($this, "listColors"), "listColors",
				get_class($this));

			// images
			$tabs_gui->addTarget("sty_images",
				$this->ctrl->getLinkTarget($this, "listImages"), "listImages",
				get_class($this));

			// table templates
			$tabs_gui->addTarget("sty_templates",
				$this->ctrl->getLinkTarget($this, "listTemplates"), "listTemplates",
				get_class($this));
				
			// settings
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "properties"), "properties",
				get_class($this));

			// accordiontest
/*
			$tabs_gui->addTarget("accordiontest",
				$this->ctrl->getLinkTarget($this, "accordiontest"), "accordiontest",
				get_class($this));*/
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
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function setTemplatesSubTabs()
	{
		global $lng, $ilTabs, $ilCtrl;
		
		$types = ilObjStyleSheet::_getTemplateClassTypes();
		
		foreach ($types as $t => $c)
		{
			$ilCtrl->setParameter($this, "temp_type", $t);
			$ilTabs->addSubTabTarget("sty_".$t."_templates",
				$this->ctrl->getLinkTarget($this, "listTemplates"), array("listTemplates", ""),
				get_class($this));
		}

		$ilCtrl->setParameter($this, "temp_type", $_GET["temp_type"]);
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
	* Add an image
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
		global $lng;
		
		$c = explode(":", $a_class);
		$a_class = $c[0];
		
		$ex_tpl = new ilTemplate("tpl.style_example.html", true, true, "Services/Style");
		
		$ex_tpl->setCurrentBlock("Example_".$a_type);
		$ex_tpl->setVariable("EX_CLASS", "ilc_".$a_type."_".$a_class);
		$ex_tpl->setVariable("EX_TEXT", "ABC abc 123");
		if ($a_type == "media_cont")
		{
			$ex_tpl->setVariable("IMG_MEDIA_DISABLED", ilUtil::getImagePath("media_disabled.gif"));
		}
		if (in_array($a_type, array("table", "table_caption")))
		{
			$ex_tpl->setVariable("TXT_CAPTION", $lng->txt("sty_caption"));
		}
		$ex_tpl->parseCurrentBlock();

		return $ex_tpl->get();
	}

	/**
	* Save hide status for characteristics
	*/
	function saveHideStatusObject()
	{
		global $ilCtrl, $lng;
		
		//var_dump($_POST);
		
		foreach ($_POST["all_chars"] as $char)
		{
			$ca = explode(".", $char);
			$this->object->saveHideStatus($ca[0], $ca[2],
				(is_array($_POST["hide"]) && in_array($char, $_POST["hide"])));
		}
		
		ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "edit");
	}
	
	
	//
	// Color management
	//
	
	/**
	* List colors of style
	*/
	function listColorsObject()
	{
		global $tpl;
		
		include_once("./Services/Style/classes/class.ilStyleColorTableGUI.php");
		$table_gui = new ilStyleColorTableGUI($this, "listColors",
			$this->object);
		$tpl->setContent($table_gui->getHTML());
		
	}

	/**
	* Add a color
	*/
	function addColorObject()
	{
		global $tpl;
		
		$this->initColorForm();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Edit color
	*/
	function editColorObject()
	{
		global $tpl, $ilCtrl;
		
		$ilCtrl->setParameter($this, "c_name", $_GET["c_name"]);
		$this->initColorForm("edit");
		$this->getColorFormValues();
		$tpl->setContent($this->form_gui->getHTML());
	}

	
	/**
	* Init color form
	*/
	function initColorForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		$this->form_gui->setTitle($lng->txt("sty_add_color"));
		
		// name
		$name_input = new ilRegExpInputGUI($lng->txt("sty_color_name"), "color_name");
		$name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
		$name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include")." A-Z, a-z, 1-9");
		$name_input->setRequired(true);
		$name_input->setSize(15);
		$name_input->setMaxLength(15);
		$this->form_gui->addItem($name_input);

		// code
		$color_input = new ilColorPickerInputGUI($lng->txt("sty_color_code"), "color_code");
		$color_input->setRequired(true);
		$color_input->setDefaultColor("");
		$this->form_gui->addItem($color_input);
		
		if ($a_mode == "create")
		{
			$this->form_gui->addCommandButton("saveColor", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelColorSaving", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("updateColor", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelColorSaving", $lng->txt("cancel"));
		}
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Set values for color editing
	*/
	function getColorFormValues()
	{
		if ($_GET["c_name"] != "")
		{
			$values["color_name"] = $_GET["c_name"];
			$values["color_code"] = $this->object->getColorCodeForName($_GET["c_name"]);
			$this->form_gui->setValuesByArray($values);
		}
	}
	
	/**
	* Cancel color saving
	*/
	function cancelColorSavingObject()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listColors");
	}
	
	/**
	* Save color
	*/
	function saveColorObject()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->initColorForm();
		
		if ($this->form_gui->checkInput())
		{
			if ($this->object->colorExists($_POST["color_name"]))
			{
				$col_input = $this->form_gui->getItemByPostVar("color_name");
				$col_input->setAlert($lng->txt("sty_color_already_exists"));
			}
			else
			{
				$this->object->addColor($_POST["color_name"],
					$_POST["color_code"]);
				$ilCtrl->redirect($this, "listColors");
			}
		}
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Update color
	*/
	function updateColorObject()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->initColorForm("edit");
		
		if ($this->form_gui->checkInput())
		{
			if ($this->object->colorExists($_POST["color_name"]) &&
				$_POST["color_name"] != $_GET["c_name"])
			{
				$col_input = $this->form_gui->getItemByPostVar("color_name");
				$col_input->setAlert($lng->txt("sty_color_already_exists"));
			}
			else
			{
				$this->object->updateColor($_GET["c_name"], $_POST["color_name"],
					$_POST["color_code"]);
				$ilCtrl->redirect($this, "listColors");
			}
		}
		$ilCtrl->setParameter($this, "c_name", $_GET["c_name"]);
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Delete color confirmation
	*/
	function deleteColorConfirmationObject()
	{
		global $ilCtrl, $tpl, $lng;
		
		if (!is_array($_POST["color"]) || count($_POST["color"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listColors");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("sty_confirm_color_deletion"));
			$cgui->setCancel($lng->txt("cancel"), "cancelColorDeletion");
			$cgui->setConfirm($lng->txt("delete"), "deleteColor");
			
			foreach ($_POST["color"] as $c)
			{
				$cgui->addItem("color[]", ilUtil::prepareFormOutput($c), $c);
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	* Cancel color deletion
	*/
	function cancelColorDeletionObject()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listColors");
	}

	/**
	* Delete colors
	*/
	function deleteColorObject()
	{
		global $ilCtrl;
		
		if (is_array($_POST["color"]))
		{
			foreach ($_POST["color"] as $c)
			{
				$this->object->removeColor($c);
			}
		}
			
		$ilCtrl->redirect($this, "listColors");
	}

	//
	// Templates management
	//
	
	/**
	* List templates
	*/
	function listTemplatesObject()
	{
		global $tpl, $ilTabs, $ilCtrl;
		
		$ctype = $_GET["temp_type"];
		if ($ctype == "")
		{
			$ctype = "table";
			$ilCtrl->setParameter($this, "temp_type", $ctype);
			$_GET["temp_type"] = $ctype;
		}
		
		$this->setTemplatesSubTabs();
		$ilTabs->setSubTabActive("sty_".$ctype."_templates");
		
		$this->includeCSS();
		include_once("./Services/Style/classes/class.ilTableTemplatesTableGUI.php");
		$table_gui = new ilTableTemplatesTableGUI($ctype, $this, "listTemplates",
			$this->object);
		$tpl->setContent($table_gui->getHTML());
		
	}
	
	/**
	* Add template
	*/
	function addTemplateObject()
	{
		global $tpl;
		
		$this->initTemplateForm();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Edit table template
	*/
	function editTemplateObject()
	{
		global $tpl, $ilCtrl;

		$ilCtrl->setParameter($this, "t_id", $_GET["t_id"]);
		$this->initTemplateForm("edit");
		$this->getTemplateFormValues();
		
		$this->displayTemplateEditForm();
	}

	/**
	* Get table template preview
	*/
	function getTemplatePreview($a_type, $a_t_id, $a_small_mode = false)
	{
		return $this->_getTemplatePreview(
			$this->object, $a_type, $a_t_id, $a_small_mode);
	}

	/**
	* Get table template preview
	*/
	function _getTemplatePreview($a_style, $a_type, $a_t_id, $a_small_mode = false)
	{
		global $lng, $tpl;

		$kr = $kc = 7;
		if ($a_small_mode)
		{
			$kr = 6;
			$kc = 5;
		}
		
		$ts = $a_style->getTemplate($a_t_id);
		$t = $ts["classes"];

		// preview
		if ($a_type == "table")
		{
			$p_content = '<PageContent><Table DataTable="y"';
			if ($t["row_head"] != "")
			{
				$p_content.= ' HeaderRows="1"';
			}
			if ($t["row_foot"] != "")
			{
				$p_content.= ' FooterRows="1"';
			}
			if ($t["col_head"] != "")
			{
				$p_content.= ' HeaderCols="1"';
			}
			if ($t["col_foot"] != "")
			{
				$p_content.= ' FooterCols="1"';
			}
			$p_content.= ' Template="'.$a_style->lookupTemplateName($a_t_id).'">';
			if (!$a_small_mode)
			{
				$p_content.= '<Caption>'.$lng->txt("sty_caption").'</Caption>';
			}
			for($i = 1; $i<=$kr; $i++)
			{
				$p_content.= '<TableRow>';
				for($j = 1; $j<=$kc; $j++)
				{
					if ($a_small_mode)
					{
						$cell = '&lt;div style="height:2px;"&gt;&lt;/div&gt;';
					}
					else
					{
						$cell = 'xxx';
					}
					$p_content.= '<TableData><PageContent><Paragraph Characteristic="TableContent">'.$cell.'</Paragraph></PageContent></TableData>';
				}
				$p_content.= '</TableRow>';
			}
			$p_content.= '</Table></PageContent>';
		}
		
		if ($a_type == "vaccordion" || $a_type == "haccordion")
		{
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			ilAccordionGUI::addCss();
			
			if ($a_small_mode)
			{
				$c = '&amp;nbsp;';
				$h = '&amp;nbsp;';
			}
			else
			{
				$c = 'xxx';
				$h = 'head';
			}
			if ($a_type == "vaccordion")
			{
				$p_content = '<PageContent><Tabs HorizontalAlign="Left" Type="VerticalAccordion" ';
				if ($a_small_mode)
				{
					$p_content.= ' ContentWidth="70"';
				}
			}
			else
			{
				$p_content = '<PageContent><Tabs Type="HorizontalAccordion"';
				if ($a_small_mode)
				{
					$p_content.= ' ContentHeight="40"';
					$p_content.= ' ContentWidth="70"';
					$c = '&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;';
				}
				else
				{
					$p_content.= ' ContentHeight="40"';
				}
			}
			$p_content.= ' Template="'.$a_style->lookupTemplateName($a_t_id).'">';
			$p_content.= '<Tab><PageContent><Paragraph>'.$c.'</Paragraph></PageContent>';
			$p_content.= '<TabCaption>'.$h.'</TabCaption>';
			$p_content.= '</Tab>';
			$p_content.= '</Tabs></PageContent>';
		}
//echo htmlentities($p_content);
		$txml = $a_style->getTemplateXML();
//echo htmlentities($txml);
		$p_content.= $txml;
		include_once("./Services/COPage/classes/class.ilPCTableGUI.php");
		$r_content = ilPCTableGUI::_renderTable($p_content, "");

		return $r_content;
	}

	/**
	* Init table template form
	*/
	function initTemplateForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		if ($a_mode == "create")
		{
			$this->form_gui->setTitle($lng->txt("sty_add_template"));
		}
		else
		{
			$this->form_gui->setTitle($lng->txt("sty_edit_template"));
		}
		
		// name
		$name_input = new ilRegExpInputGUI($lng->txt("sty_template_name"), "name");
		$name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
		$name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include")." A-Z, a-z, 1-9");
		$name_input->setRequired(true);
		$name_input->setSize(30);
		$name_input->setMaxLength(30);
		$this->form_gui->addItem($name_input);

		// template style classes
		$scs = ilObjStyleSheet::_getTemplateClassTypes($_GET["temp_type"]);
		foreach ($scs as $sc => $st)
		{
			$sc_input = new ilSelectInputGUI($lng->txt("sty_".$sc."_class"), $sc."_class");
			$chars = $this->object->getCharacteristics($st);
			$options = array("" => "");
			foreach($chars as $char)
			{
				$options[$char] = $char;
			}
			$sc_input->setOptions($options);
			$this->form_gui->addItem($sc_input);
		}
		
		if ($a_mode == "create")
		{
			$this->form_gui->addCommandButton("saveTemplate", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelTemplateSaving", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("refreshTemplate", $lng->txt("save_refresh"));
			$this->form_gui->addCommandButton("updateTemplate", $lng->txt("save_return"));
			$this->form_gui->addCommandButton("cancelTemplateSaving", $lng->txt("cancel"));
		}
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Cancel color saving
	*/
	function cancelTemplateSavingObject()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listTemplates");
	}

	/**
	* Save table template
	*/
	function saveTemplateObject()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->initTemplateForm();
		
		if ($this->form_gui->checkInput())
		{
			if ($this->object->templateExists($_POST["name"]))
			{
				$name_input = $this->form_gui->getItemByPostVar("name");
				$name_input->setAlert($lng->txt("sty_table_template_already_exists"));
			}
			else
			{
				$classes = array();
				foreach (ilObjStyleSheet::_getTemplateClassTypes($_GET["temp_type"]) as $tct => $ct)
				{
					$classes[$tct] = $_POST[$tct."_class"];
				}
				$t_id = $this->object->addTemplate($_GET["temp_type"], $_POST["name"], $classes);
				$this->object->writeTemplatePreview($t_id,
					$this->getTemplatePreview($_GET["temp_type"], $t_id, true));
				$ilCtrl->redirect($this, "listTemplates");
			}
		}
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Update table template
	*/
	function updateTemplateObject($a_refresh = false)
	{
		global $tpl, $ilCtrl, $lng;
		
		$ilCtrl->setParameter($this, "t_id", $_GET["t_id"]);
		$this->initTemplateForm("edit");
		
		if ($this->form_gui->checkInput())
		{
			if ($this->object->templateExists($_POST["name"]) &&
				$_POST["name"] != ilObjStyleSheet::lookupTemplateName($_GET["t_id"]))
			{
				$name_input = $this->form_gui->getItemByPostVar("name");
				$name_input->setAlert($lng->txt("sty_template_already_exists"));
			}
			else
			{
				$classes = array();
				foreach (ilObjStyleSheet::_getTemplateClassTypes($_GET["temp_type"]) as $tct => $ct)
				{
					$classes[$tct] = $_POST[$tct."_class"];
				}

				$this->object->updateTemplate($_GET["t_id"],
					$_POST["name"], $classes);
				$this->object->writeTemplatePreview($_GET["t_id"],
					$this->getTemplatePreview($_GET["temp_type"], $_GET["t_id"], true));
				if(!$a_refresh)
				{
					$ilCtrl->redirect($this, "listTemplates");
				}
			}
		}
		
		$this->form_gui->setValuesByPost();
		$this->displayTemplateEditForm();
	}
	
	/**
	* Display table tempalte edit form
	*/
	function displayTemplateEditForm()
	{
		global $tpl;
		
		$a_tpl = new ilTemplate("tpl.template_edit.html", true, true,
			"Services/Style");
		$this->includeCSS();
		$a_tpl->setVariable("FORM", $this->form_gui->getHTML());
		$a_tpl->setVariable("PREVIEW", $this->getTemplatePreview($_GET["temp_type"], $_GET["t_id"]));
		$tpl->setContent($a_tpl->get());
	}

	/**
	* Refresh table template
	*/
	function refreshTemplateObject()
	{
		$this->updateTemplateObject(true);
	}

	/**
	* Set values for table template editing
	*/
	function getTemplateFormValues()
	{
		if ($_GET["t_id"] > 0)
		{
			$t = $this->object->getTemplate($_GET["t_id"]);

			$values["name"] = $t["name"];
			$scs = ilObjStyleSheet::_getTemplateClassTypes($_GET["temp_type"]);
			foreach ($scs as $k => $type)
			{
				$values[$k."_class"] = $t["classes"][$k];
			}
			$this->form_gui->setValuesByArray($values);
		}
	}

	/**
	* Delete table template confirmation
	*/
	function deleteTemplateConfirmationObject()
	{
		global $ilCtrl, $tpl, $lng;
		
		if (!is_array($_POST["tid"]) || count($_POST["tid"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listTemplates");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("sty_confirm_template_deletion"));
			$cgui->setCancel($lng->txt("cancel"), "cancelTemplateDeletion");
			$cgui->setConfirm($lng->txt("sty_del_template"), "deleteTemplate");
			
			foreach ($_POST["tid"] as $tid)
			{
				$classes = $this->object->getTemplateClasses($tid);
				$cl_str = "";
				$listed = array();
				foreach ($classes as $cl)
				{
					if ($cl != "" && !$listed[$cl])
					{
						$cl_str.= '<div>- '.
							$cl."</div>";
						$listed[$cl]  = true;
					}
				}
				if ($cl_str != "")
				{
					$cl_str = '<div style="padding-left:30px;" class="small">'.
						"<div><i>".$lng->txt("sty_style_class")."</i></div>".$cl_str."</div>";
				}
				$cgui->addItem("tid[]", $tid, $this->object->lookupTemplateName($tid).$cl_str);
			}
			
			$cgui->addButton($lng->txt("sty_del_template_keep_classes"), "deleteTemplateKeepClasses");
			
			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	* Cancel table template deletion
	*/
	function cancelTemplateDeletionObject()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listTemplates");
	}

	/**
	* Delete table template
	*/
	function deleteTemplateKeepClassesObject()
	{
		global $ilCtrl;
		
		if (is_array($_POST["tid"]))
		{
			foreach ($_POST["tid"] as $tid)
			{
				$this->object->removeTemplate($tid);
			}
		}
			
		$ilCtrl->redirect($this, "listTemplates");
	}
	
	/**
	* Delete table template
	*/
	function deleteTemplateObject()
	{
		global $ilCtrl;
		
		if (is_array($_POST["tid"]))
		{
			foreach ($_POST["tid"] as $tid)
			{
				$cls = $this->object->getTemplateClasses($tid);
				foreach ($cls as $k => $cls)
				{
					$ty = $this->object->determineTemplateStyleClassType($_GET["temp_type"], $k);
					$ta = ilObjStyleSheet::_determineTag($ty);
					$this->object->deleteCharacteristic($ty, $ta, $cls);
				}
				$this->object->removeTemplate($tid);
			}
		}
			
		$ilCtrl->redirect($this, "listTemplates");
	}

	/**
	* Generate table template
	*/
	function generateTemplateObject()
	{
		global $tpl;
		
		$this->initTemplateGenerationForm();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Init table template generation form
	*/
	function initTemplateGenerationForm()
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		$this->form_gui->setTitle($lng->txt("sty_generate_template"));
		
		// name
		$name_input = new ilRegExpInputGUI($lng->txt("sty_template_name"), "name");
		$name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
		$name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include")." A-Z, a-z, 1-9");
		$name_input->setRequired(true);
		$name_input->setSize(30);
		$name_input->setMaxLength(30);
		$this->form_gui->addItem($name_input);

		// basic layout
		$bl_input = new ilSelectInputGUI($lng->txt("sty_template_layout"), "layout");
		$options = array(
			"coloredZebra" => $lng->txt("sty_table_template_colored_zebra"),
			"bwZebra" => $lng->txt("sty_table_template_bw_zebra"),
			"noZebra" => $lng->txt("sty_table_template_no_zebra")
			);
		$bl_input->setOptions($options);
		$this->form_gui->addItem($bl_input);
		
		// top bottom padding
		include_once("./Services/Style/classes/class.ilNumericStyleValueInputGUI.php");
		$num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_top_bottom_padding"), "tb_padding");
		$num_input->setAllowPercentage(false);
		$num_input->setValue("3px");
		$this->form_gui->addItem($num_input);

		// left right padding
		$num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_left_right_padding"), "lr_padding");
		$num_input->setAllowPercentage(false);
		$num_input->setValue("10px");
		$this->form_gui->addItem($num_input);

		// base color
		$bc_input = new ilSelectInputGUI($lng->txt("sty_base_color"), "base_color");
		$cs = $this->object->getColors();
		$options = array();
		foreach ($cs as $c)
		{
			$options[$c["name"]] = $c["name"];
		}
		$bc_input->setOptions($options);
		$this->form_gui->addItem($bc_input);
		
		// Lightness Settings
		$lss = array("border" => 90, "header_text" => 70, "header_bg" => 0,
			"cell1_text" => -60, "cell1_bg" => 90, "cell2_text" => -60, "cell2_bg" => 75);
		foreach ($lss as $ls => $v)
		{
			$l_input = new ilNumberInputGUI($lng->txt("sty_lightness_".$ls), "lightness_".$ls);
			$l_input->setMaxValue(100);
			$l_input->setMinValue(-100);
			$l_input->setValue($v);
			$l_input->setSize(4);
			$l_input->setMaxLength(4);
			$this->form_gui->addItem($l_input);
		}
		
		$this->form_gui->addCommandButton("templateGeneration", $lng->txt("generate"));
		$this->form_gui->addCommandButton("cancelTemplateSaving", $lng->txt("cancel"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Table template generation
	*/
	function templateGenerationObject()
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->initTemplateGenerationForm();
		
		if ($this->form_gui->checkInput())
		{
			if ($this->object->templateExists($_POST["name"]))
			{
				$name_input = $this->form_gui->getItemByPostVar("name");
				$name_input->setAlert($lng->txt("sty_table_template_already_exists"));
			}
			else
			{
				// -> move to application class!
				
				// cell classes
				$cells = array("H" => "header", "C1" => "cell1", "C2" => "cell2");
				$tb_p = $this->form_gui->getItemByPostVar("tb_padding");
				$tb_padding = $tb_p->getValue();
				$lr_p = $this->form_gui->getItemByPostVar("lr_padding");
				$lr_padding = $lr_p->getValue();
				$cell_color = $_POST["base_color"];

				// use mid gray as cell color for bw zebra
				if ($_POST["layout"] == "bwZebra")
				{
					$cell_color = "MidGray";
					if (!$this->object->colorExists($cell_color))
					{
						$this->object->addColor($cell_color, "7F7F7F");
					}
					$this->object->updateColor($cell_color, $cell_color, "7F7F7F");
				}

				foreach ($cells as $k => $cell)
				{
					$cell_class[$k] = $_POST["name"].$k;
					if (!$this->object->characteristicExists($cell_class[$k], "table_cell"))
					{
						$this->object->addCharacteristic("table_cell", $cell_class[$k], true);
					}
					if ($_POST["layout"] == "bwZebra" && $k == "H")
					{
						$this->object->replaceStylePar("td", $cell_class[$k], "color",
							"!".$_POST["base_color"]."(".$_POST["lightness_".$cell."_text"].")", "table_cell");
						$this->object->replaceStylePar("td", $cell_class[$k], "background-color",
							"!".$_POST["base_color"]."(".$_POST["lightness_".$cell."_bg"].")", "table_cell");
					}
					else
					{
						$this->object->replaceStylePar("td", $cell_class[$k], "color",
							"!".$cell_color."(".$_POST["lightness_".$cell."_text"].")", "table_cell");
						$this->object->replaceStylePar("td", $cell_class[$k], "background-color",
							"!".$cell_color."(".$_POST["lightness_".$cell."_bg"].")", "table_cell");
					}
					$this->object->replaceStylePar("td", $cell_class[$k], "padding-top",
						$tb_padding, "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "padding-bottom",
						$tb_padding, "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "padding-left",
						$lr_padding, "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "padding-right",
						$lr_padding, "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "border-width",
						"1px", "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "border-style",
						"solid", "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "border-color",
						"!".$cell_color."(".$_POST["lightness_border"].")", "table_cell");
					$this->object->replaceStylePar("td", $cell_class[$k], "font-weight",
						"normal", "table_cell");
				}
				
				// table class
				$classes["table"] = $_POST["name"]."T";
				if (!$this->object->characteristicExists($classes["table"], "table"))
				{
						$this->object->addCharacteristic("table", $classes["table"], true);
				}
				$this->object->replaceStylePar("table", $classes["table"], "caption-side",
					"bottom", "table");
				$this->object->replaceStylePar("table", $classes["table"], "border-collapse",
					"collapse", "table");
				$this->object->replaceStylePar("table", $classes["table"], "margin-top",
					"5px", "table");
				$this->object->replaceStylePar("table", $classes["table"], "margin-bottom",
					"5px", "table");
				if ($_POST["layout"] == "bwZebra")
				{
					$this->object->replaceStylePar("table", $classes["table"], "border-bottom-color",
						"!".$_POST["base_color"], "table");
					$this->object->replaceStylePar("table", $classes["table"], "border-bottom-style",
						"solid", "table");
					$this->object->replaceStylePar("table", $classes["table"], "border-bottom-width",
						"3px", "table");
					$sb = array("left", "right", "top");
					foreach ($sb as $b)
					{
						$this->object->replaceStylePar("table", $classes["table"], "border-".$b."-width",
							"0px", "table");
					}
				}
				
				switch ($_POST["layout"])
				{
					case "coloredZebra":
						$classes["row_head"] = $cell_class["H"];
						$classes["odd_row"] = $cell_class["C1"];
						$classes["even_row"] = $cell_class["C2"];
						break;
						
					case "bwZebra":
						$classes["row_head"] = $cell_class["H"];
						$classes["odd_row"] = $cell_class["C1"];
						$classes["even_row"] = $cell_class["C2"];
						break;
						
					case "noZebra":
						$classes["row_head"] = $cell_class["H"];
						$classes["odd_row"] = $cell_class["C1"];
						$classes["even_row"] = $cell_class["C1"];
						$classes["col_head"] = $cell_class["C2"];
						break;
				}
				

				$t_id = $this->object->addTemplate($_GET["temp_type"],
					$_POST["name"], $classes);
				$this->object->writeTemplatePreview($t_id,
					$this->getTemplatePreview($_GET["temp_type"], $_GET["temp_type"], $t_id, true));
				$ilCtrl->redirect($this, "listTemplates");
			}
		}
		$this->form_gui->setValuesByPost();
		$tpl->setContent($this->form_gui->getHTML());
	}

	function accordiontestObject()
	{
		global $tpl;
		
		include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
		
		$acc = new ilAccordionGUI();
		$acc->addItem("Header 1", str_repeat("bla bla bla bla bla bla", 30));
		$acc->addItem("Header 2", str_repeat("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx xx x xx x xx", 30));
		$acc->setOrientation(ilAccordionGUI::HORIZONTAL);

		$ac2 = new ilAccordionGUI();
		$ac2->addItem("Header 1", str_repeat("bla bla bla bla bla bla", 30));
		$ac2->addItem("Header 2", $acc->getHTML());
		$ac2->setOrientation(ilAccordionGUI::VERTICAL);
		
		$tpl->setContent($ac2->getHTML());
	}
	
	/**
	* return to upper context
	*/
	function returnToUpperContextObject()
	{
		global $ilCtrl;

		if ($_GET["baseClass"] == "ilAdministrationGUI")
		{
			$ilCtrl->redirectByClass("ilobjstylesettingsgui", "editContentStyles");
		}
		$ilCtrl->returnToParent($this);
	}

}
?>
