<?php 
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Term list table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTermListTableGUI extends ilTable2GUI
{
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_tax_node)
	{
		global $ilCtrl, $lng;
		
		$this->glossary = $a_parent_obj->object;
		$this->setId("glotl".$this->glossary->getId());
		$this->tax_node = $a_tax_node;

		$this->selectable_cols = array();

		include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
		$adv_ad = new ilGlossaryAdvMetaDataAdapter($this->glossary->getId());
		$this->adv_fields = $adv_ad->getAllFields();
		foreach ($this->adv_fields as $f)
		{
			$this->selectable_cols["md_".$f["id"]] = array(
				"txt" => $f["title"],
				"default" => false
				);
		}

		// selectable columns
		$this->selectable_cols["language"] = array(
			"txt" => $lng->txt("language"),
			"default" => true);
		$this->selectable_cols["usage"] = array(
			"txt" => $lng->txt("cont_usage"),
			"default" => true);

		// selectable columns of advanced metadata
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("cont_terms"));
		
		$this->addColumn("", "", "1", true);
		//$this->addColumn($this->lng->txt("cont_term"));

		include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
		$adv_ap = new ilGlossaryAdvMetaDataAdapter($this->glossary->getId());
		$this->adv_cols_order = $adv_ap->getColumnOrder();
		$this->selected_cols = $this->getSelectedColumns();
		foreach ($this->adv_cols_order as $c)
		{
			if ($c["id"] == 0)
			{
				$this->addColumn($lng->txt("cont_term"), "term");
			}
			else
			{
				if (in_array("md_".$c["id"], $this->selected_cols))
				{
					$this->addColumn($c["text"], "md_".$c["id"]);
				}
			}
		}

		foreach (array ("language", "usage") as $c)
		{
			if (in_array($c, $this->selected_cols))
			{
				$this->addColumn($this->selectable_cols[$c]["txt"]);
			}
		}

		$this->setDefaultOrderDirection("asc");
		$this->setDefaultOrderField("term");
		$this->addColumn($this->lng->txt("cont_definitions"));
		
		if (in_array($this->glossary->getVirtualMode(),
			array("level", "subtree")))
		{
			$this->addColumn($this->lng->txt("obj_glo"));
		}

		$this->addColumn("", "", "1");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.term_tbl_row.html", "Modules/Glossary");
		$this->setEnableTitle(true);

		$this->addMultiCommand("confirmTermDeletion", $lng->txt("delete"));
		//$this->addMultiCommand("addDefinition", $lng->txt("cont_add_definition"));
		
		$this->setShowRowsSelector(true);

		$this->initFilter();
		$this->setData($this->glossary->getTermList($this->filter["term"], "",
			$this->filter["definition"], $this->tax_node, true, true));
	}
	
	/**
	 * Get selectable columns
	 *
	 * @param
	 * @return
	 */
	function getSelectableColumns()
	{
		return $this->selectable_cols;
	}

	/**
	 * Should this field be sorted numeric?
	 *
	 * @return	boolean		numeric ordering; default is false
	 */
	function numericOrdering($a_field)
	{
		if (substr($a_field, 0, 3) == "md_")
		{
			$md_id = (int) substr($a_field, 3);
			if ($this->adv_fields[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Init filter
	 */
	function initFilter()
	{
		global $lng, $ilDB;
		
		// term
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("cont_term"), "term");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setSubmitFormOnEnter(true);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["term"] = $ti->getValue();
		
		// definition
		if ($ilDB->getDBType() != "oracle")
		{
			include_once("./Services/Form/classes/class.ilTextInputGUI.php");
			$ti = new ilTextInputGUI($lng->txt("cont_definition"), "defintion");
			$ti->setMaxLength(64);
			$ti->setSize(20);
			$ti->setSubmitFormOnEnter(true);
			$this->addFilterItem($ti);
			$ti->readFromSession();
			$this->filter["definition"] = $ti->getValue();
		}
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($term)
	{
		global $lng, $ilCtrl;

		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

		$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
		$ilCtrl->setParameterByClass("ilobjglossarygui", "term_id", $term["id"]);
		$ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $term["id"]);
		$ilCtrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term["id"]);

		// actions drop down
		if ($this->glossary->getId() == $term["glo_id"])
		{
			$list = new ilAdvancedSelectionListGUI();
			$list->addItem($lng->txt("cont_edit_term"), "", $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "editTerm"));
			if (count($defs) > 1)
			{
				$list->addItem($lng->txt("cont_edit_definitions"), "", $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listDefinitions"));
			}
			else if (count($defs) == 1)
			{
				$ilCtrl->setParameterByClass("ilglossarydefpagegui", "def", $defs[0]["id"]);
				$list->addItem($lng->txt("cont_edit_definition"), "", $ilCtrl->getLinkTargetByClass(array("ilglossarytermgui",
					"iltermdefinitioneditorgui",
					"ilglossarydefpagegui"), "edit"));
			}
			$list->addItem($lng->txt("cont_add_definition"), "", $ilCtrl->getLinkTargetByClass("ilobjglossarygui", "addDefinition"));
			$ilCtrl->setParameterByClass("ilglossarydefpagegui", "def", "");

			$list->setId("act_term_".$term["id"]);
			$list->setListTitle($lng->txt("actions"));
			$this->tpl->setVariable("ACTIONS", $list->getHTML());
		}


		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];

			/*if ($this->glossary->getId() == $term["glo_id"])
			{
				// up
				if ($j > 0)
				{
					$this->tpl->setCurrentBlock("move_up");
					$this->tpl->setVariable("TXT_UP", $lng->txt("up"));
					$ilCtrl->setParameter($this->parent_obj, "term_id", $term["id"]);
					$ilCtrl->setParameter($this->parent_obj, "def", $def["id"]);
					$this->tpl->setVariable("LINK_UP",
						$ilCtrl->getLinkTarget($this->parent_obj, "moveDefinitionUp"));
					$this->tpl->parseCurrentBlock();
				}
	
				// down
				if ($j+1 < count($defs))
				{
					$this->tpl->setCurrentBlock("move_down");
					$this->tpl->setVariable("TXT_DOWN", $lng->txt("down"));
					$ilCtrl->setParameter($this->parent_obj, "term_id", $term["id"]);
					$ilCtrl->setParameter($this->parent_obj, "def", $def["id"]);
					$this->tpl->setVariable("LINK_DOWN",
						$ilCtrl->getLinkTarget($this->parent_obj, "moveDefinitionDown"));
					$this->tpl->parseCurrentBlock();
				}
	
				// delete
				$this->tpl->setCurrentBlock("delete");
				$ilCtrl->setParameter($this->parent_obj, "term_id", $term["id"]);
				$ilCtrl->setParameter($this->parent_obj, "def", $def["id"]);
				$this->tpl->setVariable("LINK_DELETE",
					$ilCtrl->getLinkTarget($this->parent_obj, "confirmDefinitionDeletion"));
				$this->tpl->setVariable("TXT_DELETE", $lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
	
				// edit
				$this->tpl->setCurrentBlock("edit");
				$ilCtrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term["id"]);
				$ilCtrl->setParameterByClass("ilglossarydefpagegui", "def", $def["id"]);
				$this->tpl->setVariable("LINK_EDIT",
					$ilCtrl->getLinkTargetByClass(array("ilglossarytermgui",
					"iltermdefinitioneditorgui",
					"ilglossarydefpagegui"), "edit"));
				$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
				$this->tpl->parseCurrentBlock();
			}*/

			// text
			$this->tpl->setCurrentBlock("definition");
			$short_str = $def["short_text"];
			
			// replace tex
			// if a tex end tag is missing a tex end tag
			$ltexs = strrpos($short_str, "[tex]");
			$ltexe = strrpos($short_str, "[/tex]");
			if ($ltexs > $ltexe)
			{
				$page = new ilGlossaryDefPage($def["id"]);
				$page->buildDom();
				$short_str = $page->getFirstParagraphText();
				$short_str = strip_tags($short_str, "<br>");
				$ltexe = strpos($short_str, "[/tex]", $ltexs);
				$short_str = ilUtil::shortenText($short_str, $ltexe+6, true);
			}
			$short_str = ilUtil::insertLatexImages($short_str);
			$short_str = ilPCParagraph::xml2output($short_str);
			$this->tpl->setVariable("DEF_SHORT", $short_str);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("definition_row");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("check_col");
		$this->tpl->setVariable("CHECKBOX_ID", $term["id"]);
		$this->tpl->parseCurrentBlock();

		$ilCtrl->setParameter($this->parent_obj, "term_id", $term["id"]);


		// usage
		if (in_array("usage", $this->getSelectedColumns()))
		{
			$nr_usage = ilGlossaryTerm::getNumberOfUsages($term["id"]);
			if ($nr_usage > 0 && $this->glossary->getId() == $term["glo_id"])
			{
				$this->tpl->setCurrentBlock("link_usage");
				$ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $term["id"]);
				$this->tpl->setVariable("LUSAGE", ilGlossaryTerm::getNumberOfUsages($term["id"]));
				$this->tpl->setVariable("LINK_USAGE",
					$ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages"));
				$ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", "");
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("usage");
				$this->tpl->setVariable("USAGE", ilGlossaryTerm::getNumberOfUsages($term["id"]));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("td_usage");
			$this->tpl->parseCurrentBlock();
		}
		
		// glossary title
		if (in_array($this->glossary->getVirtualMode(),
			array("level", "subtree")))
		{
			$this->tpl->setCurrentBlock("glossary");
			$this->tpl->setVariable("GLO_TITLE", ilObject::_lookupTitle($term["glo_id"]));
			$this->tpl->parseCurrentBlock();
		}

		// output language
		if (in_array("language", $this->getSelectedColumns()))
		{
			$this->tpl->setCurrentBlock("td_lang");
			$this->tpl->setVariable("TEXT_LANGUAGE", $lng->txt("meta_l_".$term["language"]));
			$this->tpl->parseCurrentBlock();
		}


		foreach ($this->adv_cols_order as $c)
		{
			if ($c["id"] == 0)
			{
				$this->tpl->setCurrentBlock("td");
				$this->tpl->setVariable("TD_VAL", $term["term"]);
				$this->tpl->parseCurrentBlock();
			}
			else
			{				
				if (in_array("md_".$c["id"], $this->selected_cols))
				{
					$id = (int) $c["id"];
					
					$val = " ";
					if(isset($term["md_".$id."_presentation"]))
					{
						$pb = $term["md_".$id."_presentation"]->getHTML();
						if($pb)
						{
							$val = $pb;
						}
					}		
										
					$this->tpl->setCurrentBlock("td");										
					$this->tpl->setVariable("TD_VAL", $val);
					$this->tpl->parseCurrentBlock();
				}
			}
		}
	}

}
?>