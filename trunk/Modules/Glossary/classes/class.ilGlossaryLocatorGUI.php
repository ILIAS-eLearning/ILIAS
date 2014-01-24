<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary Locator GUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
 */
class ilGlossaryLocatorGUI
{
	var $mode;
	var $temp_var;
	var $tree;
	var $obj;
	var $lng;
	var $tpl;


	function ilGlossaryLocatorGUI()
	{
		global $lng, $tpl, $tree;

		$this->mode = "edit";
		$this->temp_var = "LOCATOR";
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
	}

	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}

	function setTerm(&$a_term)
	{
		$this->term =& $a_term;
	}

	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
	}

	function setDefinition(&$a_def)
	{
		$this->definition =& $a_def;
	}

	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* display locator
	*/
	function display()
	{
		global $lng, $ilCtrl, $ilLocator, $tpl;
		
		// repository links
		$ilLocator->addRepositoryItems();
		
		// glossary link
		$title = $this->glossary->getTitle();
		if ($this->mode == "edit")
		{
			$link = $ilCtrl->getLinkTargetByClass("ilobjglossarygui", "listTerms");
		}
		else
		{
			$ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", "");
			$link = $ilCtrl->getLinkTargetByClass("ilglossarypresentationgui");
			if (is_object($this->term))
			{
				$ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", $this->term->getId());
			}
		}
		$ilLocator->addItem($title, $link, "");
		
		if (is_object($this->term) && $this->mode != "edit")
		{
			$ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", $this->term->getId());
			$ilLocator->addItem($this->term->getTerm(),
				$ilCtrl->getLinkTargetByClass("ilglossarypresentationgui", "listDefinitions"));
			$ilCtrl->setParameterByClass("ilglossarypresentationgui", "term_id", $_GET["term_id"]);
		}

		if (is_object($this->definition))
		{
			$title = $this->term->getTerm()." (".$this->lng->txt("cont_definition")." ".$this->definition->getNr().")";
			if ($this->mode == "edit")
			{
				$link = $ilCtrl->getLinkTargetByClass("ilglossarydefpagegui", "edit");
			}
			else
			{
				$ilCtrl->setParameterByClass("ilglossarypresentationgui", "def", $_GET["def"]);
				$link = $ilCtrl->getLinkTargetByClass("ilglossarypresentationgui", "view");
			}
			$ilLocator->addItem($title, $link);
		}
		
		$tpl->setLocator();
	}

}
?>
