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
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
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
	* edit properties form
	*/
	function edit()
	{
		global $ilCtrl, $lng, $tpl;
		
		$this->displayValidationError();
		$this->setTabs();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt("cont_table_properties"));

		// width
		$width = new ilTextInputGUI($this->lng->txt("cont_table_width"), "width");
		$width->setValue($this->content_obj->getWidth());
		$width->setSize(6);
		$width->setMaxLength(6);
		$form->addItem($width);
		
		// border
		$border = new ilTextInputGUI($this->lng->txt("cont_table_border"), "border");
		$border->setValue($this->content_obj->getBorder());
		$border->setSize(6);
		$border->setMaxLength(6);
		$form->addItem($border);

		// padding
		$padding = new ilTextInputGUI($this->lng->txt("cont_table_cellpadding"), "padding");
		$padding->setValue($this->content_obj->getCellPadding());
		$padding->setSize(6);
		$padding->setMaxLength(6);
		$form->addItem($padding);

		// spacing
		$spacing = new ilTextInputGUI($this->lng->txt("cont_table_cellspacing"), "spacing");
		$spacing->setValue($this->content_obj->getCellSpacing());
		$spacing->setSize(6);
		$spacing->setMaxLength(6);
		$form->addItem($spacing);

		// alignment
		$align_opts = array("Left" => $lng->txt("cont_left"),
			"Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
		$align->setOptions($align_opts);
		$align->setValue($this->content_obj->getHorizontalAlign());
		$form->addItem($align);

		// caption
		$caption = new ilTextInputGUI($this->lng->txt("cont_caption"), "caption");
		$caption->setValue($this->content_obj->getCaption());
		$caption->setSize(60);
		$form->addItem($caption);
		
		// caption align
		$ca_opts = array("top" => $lng->txt("cont_top"),
			"bottom" => $lng->txt("cont_bottom"));
		$ca = new ilSelectInputGUI($this->lng->txt("cont_align"),
			"cap_align");
		$ca->setOptions($ca_opts);
		$ca->setValue($this->content_obj->getCaptionAlign());
		$caption->addSubItem($ca);

		// language
		$s_lang = $this->content_obj->getLanguage();
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		//$select_language = ilUtil::formSelect ($s_lang, "tab_language", $lang, false, true);
		$language = new ilSelectInputGUI($this->lng->txt("language"), "tab_language");
		$language->setOptions($lang);
		$language->setValue($s_lang);
		$form->addItem($language);
				
		$form->addCommandButton("saveProperties", $lng->txt("save"));

		$html = $form->getHTML();
		$html.= "<br />".$this->renderTable("");
		$tpl->setContent($html);
	}

	/**
	* Render the table
	*/
	function renderTable($a_mode = "table_edit")
	{
		global $ilUser;
		
		$tab_node = $this->content_obj->getNode();
		$content = $this->dom->dump_node($tab_node);
		$trans = $this->pg_obj->getLanguageVariablesXML();
		$mobs = $this->pg_obj->getMultimediaXML();
		$content = "<dummy>".$content.$mobs.$trans."</dummy>";

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
		$options = array("" => $this->lng->txt("none"), "ilc_Cell1" => "Cell1", "ilc_Cell2" => "Cell2",
			"ilc_Cell3" => "Cell3", "ilc_Cell4" => "Cell4");
		foreach($options as $k => $option)
		{
			$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="'.$k.'">'.
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
	* set width of selected table data cells
	*/
	function setWidth()
	{
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $target)
			{
				$cid = explode(":", $target);
				$this->content_obj->setTDWidth($cid[0], $_POST["td_width"], $cid[1]);
			}
		}

		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		$this->pg_obj->addHierIDs();
		$this->edit();
	}

	/**
	* set class of selected table data cells
	*/
	function setClass()
	{
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $target)
			{
				$cid = explode(":", $target);
				$this->content_obj->setTDClass($cid[0], $_POST["td_class"], $cid[1]);
			}
		}
		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		$this->pg_obj->addHierIDs();
		$this->edit();
	}

	
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
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt("cont_insert_table"));
		
		$nr = array();
		for($i=1; $i<=20; $i++)
		{
			$nr[$i] = $i;
		}
		
		// cols
		$cols = new ilSelectInputGUI($this->lng->txt("cont_nr_cols"), "nr_cols");
		$cols->setOptions($nr);
		$cols->setValue(2);
		$form->addItem($cols);

		// rows
		$rows = new ilSelectInputGUI($this->lng->txt("cont_nr_rows"), "nr_rows");
		$rows->setOptions($nr);
		$rows->setValue(2);
		$form->addItem($rows);

		// width
		$width = new ilTextInputGUI($this->lng->txt("cont_table_width"), "width");
		$width->setValue("");
		$width->setSize(6);
		$width->setMaxLength(6);
		$form->addItem($width);
		
		// border
		$border = new ilTextInputGUI($this->lng->txt("cont_table_border"), "border");
		$border->setValue("1px");
		$border->setSize(6);
		$border->setMaxLength(6);
		$form->addItem($border);

		// padding
		$padding = new ilTextInputGUI($this->lng->txt("cont_table_cellpadding"), "padding");
		$padding->setValue("2px");
		$padding->setSize(6);
		$padding->setMaxLength(6);
		$form->addItem($padding);

		// spacing
		$spacing = new ilTextInputGUI($this->lng->txt("cont_table_cellspacing"), "spacing");
		$spacing->setValue("0px");
		$spacing->setSize(6);
		$spacing->setMaxLength(6);
		$form->addItem($spacing);

		// first row style
		require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$fr_style = new ilRadioMatrixInputGUI($this->lng->txt("cont_first_row_style"), "first_row_style");
		$options = array("" => $this->lng->txt("none"), "ilc_Cell1" => "Cell1", "ilc_Cell2" => "Cell2",
			"ilc_Cell3" => "Cell3", "ilc_Cell4" => "Cell4");
		foreach($options as $k => $option)
		{
			$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="'.$k.'">'.
				$option.'</td></tr></table>';
		}
			
		$fr_style->setValue("");
		$fr_style->setOptions($options);
		$form->addItem($fr_style);

		// alignment
		$align_opts = array("Left" => $lng->txt("cont_left"),
			"Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
		$align->setOptions($align_opts);
		$align->setValue("Center");
		$form->addItem($align);
		
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
		$form->addItem($import);

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
		//$select_language = ilUtil::formSelect ($s_lang, "tab_language", $lang, false, true);
		$language = new ilSelectInputGUI($this->lng->txt("language"), "tab_language");
		$language->setOptions($lang);
		$language->setValue($s_lang);
		$form->addItem($language);
				
		$form->addCommandButton("create_tab", $lng->txt("save"));
		$form->addCommandButton("cancelCreate", $lng->txt("cancel"));

		$html = $form->getHTML();
		$tpl->setContent($html);
return;

		// new table form (input of rows and columns)
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table_new.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_table"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		for($i=1; $i<=10; $i++)
		{
			$nr[$i] = $i;
		}

		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}

		// select fields for number of columns
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_language = ilUtil::formSelect ($s_lang, "tab_language", $lang, false, true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("TXT_COLS", $this->lng->txt("cont_nr_cols"));
		$select_cols = ilUtil::formSelect ("2","nr_cols",$nr,false,true);
		$this->tpl->setVariable("SELECT_COLS", $select_cols);
		$this->tpl->setVariable("TXT_ROWS", $this->lng->txt("cont_nr_rows"));
		$select_rows = ilUtil::formSelect ("2","nr_rows",$nr,false,true);
		$this->tpl->setVariable("SELECT_ROWS", $select_rows);
		
		//import html table
		$this->tpl->setVariable("TXT_HTML_IMPORT", $this->lng->txt("cont_table_html_import"));
		$this->tpl->setVariable("TXT_SPREADSHEET", $this->lng->txt("cont_table_spreadsheet_import"));		
		$this->tpl->setVariable("TXT_BTN_HTML_IMPORT", $this->lng->txt("import"));		
		$this->tpl->setVariable("TXT_HTML_IMPORT_INFO", $this->lng->txt("cont_table_html_import_info"));
		$this->tpl->setVariable("TXT_SPREADSHEET_IMPORT_INFO", $this->lng->txt("cont_table_spreadsheet_import_info"));		
		$this->tpl->setVariable("CMD_HTML_IMPORT", "create_tab");
		$this->tpl->setVariable("SELECT_ROWS", $select_rows);
					
//		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_tab");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelCreate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}
	

	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		global	$lng;
		$this->content_obj = new ilPCTable($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setLanguage(ilUtil::stripSlashes($_POST["tab_language"]));
		$import_table = trim($_POST["import_table"]);
		
		// import xhtml or spreadsheet table
		if (!empty ($import_table))
		{
			switch($_POST["import_type"])
			{
				// xhtml import
				case "html":
					if (!$this->content_obj->importHtml ($_POST["tab_language"], $import_table))
					{
						$this->insert();
						return;	
					}
					break;
					
				// spreadsheet
				case "spreadsheet":
					$this->content_obj->importSpreadsheet($_POST["tab_language"], $import_table);
					break;
			}
		}
		else
		{		
			$this->content_obj->addRows(ilUtil::stripSlashes($_POST["nr_rows"]),
				ilUtil::stripSlashes($_POST["nr_cols"]));
		}
		$this->content_obj->setWidth(ilUtil::stripSlashes($_POST["width"]));
		$this->content_obj->setBorder(ilUtil::stripSlashes($_POST["border"]));
		$this->content_obj->setCellPadding(ilUtil::stripSlashes($_POST["padding"]));
		$this->content_obj->setCellSpacing(ilUtil::stripSlashes($_POST["spacing"]));
		$this->content_obj->setHorizontalAlign(ilUtil::stripSlashes($_POST["align"]));
		
		$frtype = ilUtil::stripSlashes($_POST["first_row_style"]);
		if ($frtype != "")
		{
			$this->content_obj->setFirstRowStyle($frtype);
		}
		
		$this->updated = $this->pg_obj->update();
		
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}
}
?>
