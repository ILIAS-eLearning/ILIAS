<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Glossary Locator GUI
*
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
		global $lng, $ilCtrl;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $this->tree->getPathFull($_GET["ref_id"]);

		if (is_object($this->term) && ($_GET["def"] > 0 || $this->mode == "presentation"))
		{
			$modifier = 0;
		}
		else
		{
			$modifier = 1;
		}

		switch($this->mode)
		{
			case "edit":
				$repository = "./repository.php";
				break;

			case "presentation":
				$script = "ilias.php?baseClass=ilGlossaryPresentationGUI";
				$repository = "./repository.php";
				break;
		}

		//$this->tpl->touchBlock("locator_separator");
		//$this->tpl->touchBlock("locator_item");
		
		foreach ($path as $key => $row)
		{
			//if ($row["child"] == $this->tree->getRootId())
			//{
			//	continue;
			//}

			if (($key < count($path) - $modifier))
			{
				$this->tpl->touchBlock("locator_separator");
			}
			
			
			if ($row["child"] > 0)
			{
				$this->tpl->setCurrentBlock("locator_img");
				$this->tpl->setVariable("IMG_SRC",
					ilUtil::getImagePath("icon_".$row["type"]."_s.gif"));
				$this->tpl->setVariable("IMG_ALT",
					$lng->txt("obj_".$type));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("locator_item");
			$t_frame = "";
			if ($row["child"] == $this->tree->getRootId())
			{
				$title = $this->lng->txt("repository");
				$link = $repository."?cmd=frameset&ref_id=".$row["child"];
				$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
			}
			else if (($_GET["ref_id"] == $row["child"]))
			{
				$title = $this->glossary->getTitle();
				if ($this->mode == "edit")
				{
					$link = $ilCtrl->getLinkTargetByClass("ilobjglossarygui", "listTerms");
				}
				else
				{
					$link = $script."&amp;ref_id=".$_GET["ref_id"];
				}
			}
			else
			{
				$title = $row["title"];
				$link = $repository."?cmd=frameset&ref_id=".$row["child"];
				$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
			}
			$this->tpl->setVariable("ITEM", $title);
			$this->tpl->setVariable("LINK_ITEM", $link);
			if ($t_frame != "")
			{
				$this->tpl->setVariable("LINK_TARGET", " target=\"$t_frame\" ");
			}
			$this->tpl->parseCurrentBlock();
		}

		/*
		if (is_object($this->definition))
		{
			$this->tpl->touchBlock("locator_separator");
		}*/


		if (is_object($this->term) && $this->mode != "edit")
		{
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->term->getTerm());
			//if ($this->mode == "edit")
			//{
			//	$this->tpl->setVariable("LINK_ITEM",
			//		$ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listDefinitions"));
			//}
			//else
			//{
				$this->tpl->setVariable("LINK_ITEM", $script."&amp;ref_id=".$_GET["ref_id"].
					"&cmd=listDefinitions&term_id=".$this->term->getId());
			//}
			$this->tpl->parseCurrentBlock();
		}

		//$this->tpl->touchBlock("locator_separator");

		if (is_object($this->definition))
		{
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->term->getTerm()." (".
				$this->lng->txt("cont_definition")." ".
				$this->definition->getNr().")");
			if ($this->mode == "edit")
			{
				$this->tpl->setVariable("LINK_ITEM",
					$ilCtrl->getLinkTargetByClass("ilpageobjectgui", "edit"));
			}
			else
			{
				$this->tpl->setVariable("LINK_ITEM", $script."&amp;ref_id=".$_GET["ref_id"].
					"&cmd=view&def=".$_GET["def"]);
			}
			$this->tpl->parseCurrentBlock();
		}

		//$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR", $debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

}
?>
