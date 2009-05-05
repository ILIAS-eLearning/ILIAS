<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPCTable.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTableGUI
*
* User Interface for Table Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTableGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCTableGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
		$this->setCharacteristics(array("StandardTable" => $this->lng->txt("cont_StandardTable")));

	}

	/**
	* Set basic table cell styles
	*/
	function setBasicTableCellStyles()
	{
		$this->setCharacteristics(array("Cell1" => "Cell1", "Cell2" => "Cell2",
			"Cell3" => "Cell3", "Cell4" => "Cell4"));
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->getCharacteristicsOfCurrentStyle("table");	// scorm-2004
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}


	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $lng;

		$ilTabs->setBackTarget($lng->txt("pg"),
			$this->ctrl->getParentReturn($this));
		
		$ilTabs->addTarget("cont_table_properties",
			$ilCtrl->getLinkTarget($this, "edit"), "edit",
			get_class($this));

		$ilTabs->addTarget("cont_table_cell_properties",
			$ilCtrl->getLinkTarget($this, "editCells"), "editCells",
			get_class($this));

	}
	
	/**
	* Get table templates
	*/
	function getTemplateOptions()
	{
		if ($this->getStyleId() > 0 &&
			ilObject::_lookupType($this->getStyleId()) == "sty")
		{
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			$style = new ilObjStyleSheet($this->getStyleId());
			$this->style = $style;
			$ts = $style->getTableTemplates();
			$options = array();
			foreach ($ts as $t)
			{
				$options["t:".$t["id"].":".$t["name"]] = $t["name"];
			}
			return $options;
		}
		return false;
	}

	/**
	* edit properties form
	*/
	function edit()
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->displayValidationError();
		$this->setTabs();
		
		$this->initPropertiesForm();
		$this->getPropertiesFormValues();
		$html = $this->form->getHTML();
		$html.= "<br />".$this->renderTable("");
		$tpl->setContent($html);
	}
	
	/**
	* Init properties form
	*/
	function initPropertiesForm($a_mode = "edit")
	{
		global $ilCtrl, $lng, $tpl, $ilUser;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_mode == "create")
		{
			$this->form->setTitle($this->lng->txt("cont_insert_table"));
		}
		else
		{
			$this->form->setTitle($this->lng->txt("cont_table_properties"));
		}

		if ($a_mode == "create")
		{
			$nr = array();
			for($i=1; $i<=20; $i++)
			{
				$nr[$i] = $i;
			}
			
			// cols
			$cols = new ilSelectInputGUI($this->lng->txt("cont_nr_cols"), "nr_cols");
			$cols->setOptions($nr);
			$cols->setValue(2);
			$this->form->addItem($cols);
	
			// rows
			$rows = new ilSelectInputGUI($this->lng->txt("cont_nr_rows"), "nr_rows");
			$rows->setOptions($nr);
			$rows->setValue(2);
			$this->form->addItem($rows);
		}

		// width
		$width = new ilTextInputGUI($this->lng->txt("cont_table_width"), "width");
		$width->setSize(6);
		$width->setMaxLength(6);
		$this->form->addItem($width);
		
		// border
		$border = new ilTextInputGUI($this->lng->txt("cont_table_border"), "border");
		$border->setValue("1px");
		$border->setSize(6);
		$border->setMaxLength(6);
		$this->form->addItem($border);

		// padding
		$padding = new ilTextInputGUI($this->lng->txt("cont_table_cellpadding"), "padding");
		$padding->setValue("2px");
		$padding->setSize(6);
		$padding->setMaxLength(6);
		$this->form->addItem($padding);

		// spacing
		$spacing = new ilTextInputGUI($this->lng->txt("cont_table_cellspacing"), "spacing");
		$spacing->setValue("0px");
		$spacing->setSize(6);
		$spacing->setMaxLength(6);
		$this->form->addItem($spacing);

		// table templates and table classes
		require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$char_prop = new ilRadioMatrixInputGUI($this->lng->txt("cont_characteristic"),
			"characteristic");
		$chars = $this->getCharacteristics();
		$templates = $this->getTemplateOptions();
		$chars = array_merge($templates, $chars);
		if (is_object($this->content_obj))
		{
			if ($chars[$a_seleted_value] == "" && ($this->content_obj->getClass() != ""))
			{
				$chars = array_merge(
					array($this->content_obj->getClass() => $this->content_obj->getClass()),
					$chars);
			}
		}
		foreach($chars as $k => $char)
		{
			if (strpos($k, ":") > 0)
			{
				$t = explode(":", $k);
				$chars[$k] = $this->style->lookupTableTemplatePreview($t[1])."<div>$char</div>";
			}
			else
			{
				$chars[$k] = '<table class="ilc_table_'.$k.'"><tr><td>'.
					$char.'</td></tr></table>';
			}
		}
		$char_prop->setOptions($chars);
		$char_prop->setValue("StandardTable");
		$this->form->addItem($char_prop);
		
		$nr = array();
		for($i=0; $i<=3; $i++)
		{
			$nr[$i] = $i;
		}
			
		// row header
		$rh = new ilSelectInputGUI($this->lng->txt("cont_nr_row_header"), "row_header");
		$rh->setOptions($nr);
		$rh->setValue(1);
		$this->form->addItem($rh);

		// row footer
		$rf = new ilSelectInputGUI($this->lng->txt("cont_nr_row_footer"), "row_footer");
		$rf->setOptions($nr);
		$rf->setValue(0);
		$this->form->addItem($rf);

		// col header
		$ch = new ilSelectInputGUI($this->lng->txt("cont_nr_col_header"), "col_header");
		$ch->setOptions($nr);
		$ch->setValue(0);
		$this->form->addItem($ch);

		// col footer
		$cf = new ilSelectInputGUI($this->lng->txt("cont_nr_col_footer"), "col_footer");
		$cf->setOptions($nr);
		$cf->setValue(0);
		$this->form->addItem($cf);

		if ($a_mode == "create")
		{
			// first row style
			require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
			$fr_style = new ilRadioMatrixInputGUI($this->lng->txt("cont_first_row_style"), "first_row_style");
			$this->setBasicTableCellStyles();
			$this->getCharacteristicsOfCurrentStyle("table_cell");	// scorm-2004
			$chars = $this->getCharacteristics();	// scorm-2004
			$options = array_merge(array("" => $this->lng->txt("none")), $chars);	// scorm-2004
			foreach($options as $k => $option)
			{
				$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_'.$k.'">'.
					$option.'</td></tr></table>';
			}
				
			$fr_style->setValue("");
			$fr_style->setOptions($options);
			$this->form->addItem($fr_style);
		}

		// alignment
		$align_opts = array("Left" => $lng->txt("cont_left"),
			"Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
		$align->setOptions($align_opts);
		$align->setValue("Center");
		$this->form->addItem($align);

		// caption
		$caption = new ilTextInputGUI($this->lng->txt("cont_caption"), "caption");
		$caption->setSize(60);
		$this->form->addItem($caption);
		
		// caption align
		$ca_opts = array("top" => $lng->txt("cont_top"),
			"bottom" => $lng->txt("cont_bottom"));
		$ca = new ilSelectInputGUI($this->lng->txt("cont_align"),
			"cap_align");
		$ca->setOptions($ca_opts);
		$caption->addSubItem($ca);

		// import
		if ($a_mode == "create")
		{
			// import table
			$import = new ilRadioGroupInputGUI($this->lng->txt("cont_paste_table"), "import_type");
			$op = new ilRadioOption($this->lng->txt("cont_html_table"), "html");
			$import->addOption($op);
			$op2 = new ilRadioOption($this->lng->txt("cont_spreadsheet_table"), "spreadsheet");
			
				$import_data = new ilTextAreaInputGUI("", "import_table");
				$import_data->setRows(8);
				$import_data->setCols(50);
				$op2->addSubItem($import_data);
			
			$import->addOption($op2);
			$import->setValue("html");
			$this->form->addItem($import);
		}
		
		// language
		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$language = new ilSelectInputGUI($this->lng->txt("language"), "language");
		$language->setOptions($lang);
		$language->setValue($s_lang);
		$this->form->addItem($language);

		if ($a_mode == "create")
		{
			$this->form->addCommandButton("create_tab", $lng->txt("save"));
			$this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("saveProperties", $lng->txt("save"));
		}
	}

	/**
	* Get properties form
	*/
	function getPropertiesFormValues()
	{
		$values = array();
		$values["width"] = $this->content_obj->getWidth();
		$values["border"] = $this->content_obj->getBorder();
		$values["padding"] = $this->content_obj->getCellPadding();
		$values["spacing"] = $this->content_obj->getCellSpacing();
		$values["row_header"] = $this->content_obj->getHeaderRows();
		$values["row_footer"] = $this->content_obj->getFooterRows();
		$values["col_header"] = $this->content_obj->getHeaderCols();
		$values["col_footer"] = $this->content_obj->getFooterCols();
		if ($this->content_obj->getTemplate() != "")
		{
			$values["characteristic"] = "t:".
				ilObjStyleSheet::_lookupTableTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()).":".
				$this->content_obj->getTemplate();
		}
		else
		{
			$values["characteristic"] = $this->content_obj->getClass();
		}
		$values["align"] = $this->content_obj->getHorizontalAlign();
		$values["caption"] = $this->content_obj->getCaption();
		$values["cap_align"] = $this->content_obj->getCaptionAlign();
		$values["language"] = $this->content_obj->getLanguage();
		
		$this->form->setValuesByArray($values);
		
		$ca = $this->form->getItemByPostVar("cap_align");
		$ca->setValue($this->content_obj->getCaptionAlign());
	}

	/**
	* Render the table
	*/
	function renderTable($a_mode = "table_edit")
	{
		$tab_node = $this->content_obj->getNode();
		$content = $this->dom->dump_node($tab_node);
		$trans = $this->pg_obj->getLanguageVariablesXML();
		$mobs = $this->pg_obj->getMultimediaXML();
		if ($this->getStyleId() > 0)
		{
			if (ilObject::_lookupType($this->getStyleId()) == "sty")
			{
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$style = new ilObjStyleSheet($this->getStyleId());
				$template_xml = $style->getTableTemplateXML();
			}
		}

		$content = $content.$mobs.$trans.$template_xml;
		
		return ilPCTableGUI::_renderTable($content, $a_mode);
	}
		
	/**
	* Static render table function
	*/
	static function _renderTable($content, $a_mode = "table_edit")
	{
		global $ilUser;
		
		$content = "<dummy>".$content."</dummy>";

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$med_disabled_path = ilUtil::getImagePath("media_disabled.gif");
		$wb_path = ilUtil::getWebspaceDir("output");
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$params = array ('mode' => $a_mode,
			'med_disabled_path' => $med_disabled_path,
			'media_mode' => $ilUser->getPref("ilPageEditor_MediaMode"),
			'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;","&",$output);
		
		return '<div style="float:left;">'.$output.'</div>';
	}
	
	/**
	* edit properties form
	*/
	function editCells()
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->displayValidationError();
		$this->setTabs();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt("cont_table_cell_properties"));

		// first row style
		require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$style = new ilRadioMatrixInputGUI($this->lng->txt("cont_style"), "style");
		$this->setBasicTableCellStyles();
		$this->getCharacteristicsOfCurrentStyle("table_cell");	// scorm-2004
		$chars = $this->getCharacteristics();	// scorm-2004
		$options = array_merge(array("" => $this->lng->txt("none")), $chars);	// scorm-2004
		foreach($options as $k => $option)
		{
			$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_'.$k.'">'.
				$option.'</td></tr></table>';
		}
			
		$style->setValue("");
		$style->setInfo($lng->txt("cont_set_tab_style_info"));
		$style->setOptions($options);
		$form->addItem($style);
		$form->setKeepOpen(true);

		$form->addCommandButton("setStylesAndWidths", $lng->txt("cont_set_styles_and_widths"));

		$html = $form->getHTML();
		$html.= "<br />".$this->renderTable()."</form>";
		$tpl->setContent($html);
		
	}

	/**
	* Set cell styles and widths
	*/
	function setStylesAndWidths()
	{
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $k => $value)
			{
				if ($value > 0)
				{
					$cid = explode(":", $k);
					$this->content_obj->setTDClass($cid[0], $_POST["style"], $cid[1]);
				}
			}
		}
		if (is_array($_POST["width"]))
		{
			foreach ($_POST["width"] as $k => $width)
			{
				$cid = explode(":", $k);
				$this->content_obj->setTDWidth($cid[0], $width, $cid[1]);
			}
		}
		$this->updated = $this->pg_obj->update();
		$this->ctrl->redirect($this, "editCells");
	}
	
	/**
	* Set properties from input form
	*/
	function setProperties()
	{
		// mask html
		$caption = ilUtil::stripSlashes($_POST["caption"]);
		$caption = str_replace("&","&amp;", $caption);
		$caption = str_replace("<","&lt;", $caption);
		$caption = str_replace(">","&gt;", $caption);

		$this->content_obj->setLanguage(ilUtil::stripSlashes($_POST["language"]));
		$this->content_obj->setWidth(ilUtil::stripSlashes($_POST["width"]));
		$this->content_obj->setBorder(ilUtil::stripSlashes($_POST["border"]));
		$this->content_obj->setCellSpacing(ilUtil::stripSlashes($_POST["spacing"]));
		$this->content_obj->setCellPadding(ilUtil::stripSlashes($_POST["padding"]));
		$this->content_obj->setHorizontalAlign(ilUtil::stripSlashes($_POST["align"]));
		$this->content_obj->setHeaderRows(ilUtil::stripSlashes($_POST["row_header"]));
		$this->content_obj->setHeaderCols(ilUtil::stripSlashes($_POST["col_header"]));
		$this->content_obj->setFooterRows(ilUtil::stripSlashes($_POST["row_footer"]));
		$this->content_obj->setFooterCols(ilUtil::stripSlashes($_POST["col_footer"]));
		if (strpos($_POST["characteristic"], ":") > 0)
		{
			$t = explode(":", $_POST["characteristic"]);
			$this->content_obj->setTemplate(ilUtil::stripSlashes($t[2]));
			$this->content_obj->setClass("");
		}
		else
		{
			$this->content_obj->setClass(ilUtil::stripSlashes($_POST["characteristic"]));
			$this->content_obj->setTemplate("");
		}
		$this->content_obj->setCaption($caption,
			ilUtil::stripSlashes($_POST["cap_align"]));
	}
	
	/**
	* save table properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->redirect($this, "edit");
			//$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}

	/**
	* align table to right
	*/
	function rightAlign()
	{
		$this->content_obj->setHorizontalAlign("Right");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align table to left
	*/
	function leftAlign()
	{
		$this->content_obj->setHorizontalAlign("Left");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align table to left
	*/
	function centerAlign()
	{
		$this->content_obj->setHorizontalAlign("Center");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align table to left float
	*/
	function leftFloatAlign()
	{
		$this->content_obj->setHorizontalAlign("LeftFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* align table to left
	*/
	function rightFloatAlign()
	{
		$this->content_obj->setHorizontalAlign("RightFloat");
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* insert new table form
	*/
	function insert()
	{
		global $ilUser, $ilCtrl, $tpl, $lng;
		
		$this->displayValidationError();

		$this->initPropertiesForm("create");
		$html = $this->form->getHTML();
		$tpl->setContent($html);
	}

	/**
	* Get new table object
	*/
	function getNewTableObject()
	{
		return new ilPCTable($this->dom);
	}

	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		global	$lng;
		
		$this->content_obj = $this->getNewTableObject();
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$import_table = trim($_POST["import_table"]);
		
		// import xhtml or spreadsheet table
		if (!empty ($import_table))
		{
			switch($_POST["import_type"])
			{
				// xhtml import
				case "html":
					if (!$this->content_obj->importHtml ($_POST["language"], $import_table))
					{
						$this->insert();
						return;	
					}
					break;
					
				// spreadsheet
				case "spreadsheet":
					$this->content_obj->importSpreadsheet($_POST["language"], $import_table);
					break;
			}
		}
		else
		{		
			$this->content_obj->addRows(ilUtil::stripSlashes($_POST["nr_rows"]),
				ilUtil::stripSlashes($_POST["nr_cols"]));
		}
		
		$this->setProperties();
		
		$frtype = ilUtil::stripSlashes($_POST["first_row_style"]);
		if ($frtype != "")
		{
			$this->content_obj->setFirstRowStyle($frtype);
		}
		
		$this->updated = $this->pg_obj->update();
		
		if ($this->updated === true)
		{
			$this->afterCreation();
		}
		else
		{
			$this->insert();
		}
	}
	
	/**
	* After creation processing
	*/
	function afterCreation()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

}
?>
