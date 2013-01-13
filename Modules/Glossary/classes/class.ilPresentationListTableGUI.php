<?php 
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Term list table for presentation mode
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
 */
class ilPresentationListTableGUI extends ilTable2GUI
{	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_glossary, $a_offline,
		$a_tax_node)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->glossary = $a_glossary;
		$this->offline = $a_offline;
		$this->tax_node = $a_tax_node;
		$this->setId("glopr".$this->glossary->getId());
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		//$this->setTitle($lng->txt("cont_terms"));

		if ($this->glossary->getPresentationMode() == "full_def")
		{
			$this->addColumn($lng->txt("cont_terms"));
		}
		else
		{
			$this->addColumn($lng->txt("cont_term"));
			$this->addColumn($lng->txt("cont_definitions"));
			if ($this->glossary->isVirtual())
			{
				$this->addColumn($lng->txt("obj_glo"));
			}
		}
		
		$this->setEnableHeader(true);
		if (!$this->offline)
		{
			$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));
		}
		else
		{
			$this->disable("footer");
		}
		$this->setRowTemplate("tpl.term_tbl_pres_row.html", "Modules/Glossary");
		$this->setEnableTitle(true);
		
		if (!$this->offline)
		{
			$this->initFilter();
			$this->setFilterCommand("applyFilter");
		}
		//$this->setDefaultOrderField("login");
		//$this->setDefaultOrderDirection("asc");

		$this->setData($this->glossary->getTermList($this->filter["term"], $_GET["letter"],
				$this->filter["definition"], $this->tax_node));
		
	}
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser, $ilDB;
		
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

		$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
		$ilCtrl->setParameter($this->parent_obj, "term_id", $term["id"]);

		if ($this->glossary->getPresentationMode() == "full_def")
		{
			$this->tpl->setVariable("FULL_DEF",
				$this->parent_obj->listDefinitions($_GET["ref_id"], $term["id"], true));
		}
		else
		{
			for ($j=0; $j < count($defs); $j++)
			{
				$def = $defs[$j];
				if (count($defs) > 1)
				{
					$this->tpl->setCurrentBlock("definition");
					$this->tpl->setVariable("DEF_TEXT", $lng->txt("cont_definition")." ".($j + 1));
					$this->tpl->parseCurrentBlock();
				}

				// check dirty short texts
				$this->tpl->setCurrentBlock("definition");
				if ($def["short_text_dirty"])
				{
					$def = new ilGlossaryDefinition($def["id"]);
					$def->updateShortText();
					$short_str = $def->getShortText();
				}
				else
				{
					$short_str = $def["short_text"];
				}
				// replace tex
				// if a tex end tag is missing a tex end tag
				$ltexs = strrpos($short_str, "[tex]");
				$ltexe = strrpos($short_str, "[/tex]");
				if ($ltexs > $ltexe)
				{
					$page =& new ilPageObject("gdf", $def["id"]);
					$page->buildDom();
					$short_str = $page->getFirstParagraphText();
					$short_str = strip_tags($short_str, "<br>");
					$ltexe = strpos($short_str, "[/tex]", $ltexs);
					$short_str = ilUtil::shortenText($short_str, $ltexe+6, true);
				}
				if (!$this->offline)
				{
					$short_str = ilUtil::insertLatexImages($short_str);
				}
				else
				{
					$short_str = ilUtil::buildLatexImages($short_str,
						$this->parent_obj->getOfflineDirectory());
				}
				$short_str = ilPCParagraph::xml2output($short_str);

				$this->tpl->setVariable("DEF_SHORT", $short_str);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("definition_row");
				$this->tpl->parseCurrentBlock();
			}
//			$this->tpl->touchBlock("def_td");

			// display additional column 'glossary' for meta glossaries
			if ($this->glossary->isVirtual())
			{
				$this->tpl->setCurrentBlock("glossary_row");
				$glo_title = ilObject::_lookupTitle($term["glo_id"]);
				$this->tpl->setVariable("GLO_TITLE", $glo_title);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("view_term");
			$this->tpl->setVariable("TEXT_TERM", $term["term"]);
			if (!$this->offline)
			{
				if (!empty ($filter))
				{
					$ilCtrl->setParameter($this, "term", $filter);
					$ilCtrl->setParameter($this, "oldoffset", $_GET["oldoffset"]);
				}
				$ilCtrl->setParameter($this, "term_id", $term["id"]);
				$ilCtrl->setParameter($this, "offset", $_GET["offset"]);
				$this->tpl->setVariable("LINK_VIEW_TERM",
					$ilCtrl->getLinkTarget($this->parent_obj, "listDefinitions"));
				$ilCtrl->clearParameters($this);
			}
			else
			{
				$this->tpl->setVariable("LINK_VIEW_TERM", "term_".$term["id"].".html");
			}
			$this->tpl->setVariable("ANCHOR_TERM", "term_".$term["id"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$ilCtrl->clearParameters($this->parent_obj);

	}

}
?>