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

require_once("./Modules/LearningModule/classes/class.ilLMExplorer.php");

/*
* Explorer View for Learning Module Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMEditorExplorer extends ilLMExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMEditorExplorer($a_target, &$a_lm_obj, $a_gui_class)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->gui_class = $a_gui_class;

		parent::ilLMExplorer($a_target, $a_lm_obj);
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias;
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_lm_s.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("obj_".$this->lm_obj->getType()));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", ilUtil::shortenText($this->lm_obj->getTitle(), $this->textwidth, true));

		if ($this->lm_obj->getType() == "lm")
		{
			$this->ctrl->setParameterByClass("ilObjLearningModuleGUI",
				"obj_id", "");
			$link = $this->ctrl->getLinkTargetByClass("ilObjLearningModuleGUI",
				"properties");
		}
		else
		{
			$this->ctrl->setParameterByClass("ilObjDlBookGUI",
				"obj_id", "");
			$link = $this->ctrl->getLinkTargetByClass("ilObjDlBookGUI",
				"properties");
		}
		$tpl->setVariable("LINK_TARGET", $link);

		$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
		$tpl->parseCurrentBlock();

		//$tpl->setCurrentBlock("row");
		//$tpl->parseCurrentBlock();

		$tpl->touchBlock("element");
	}

	/**
	* standard implementation for title, maybe overwritten by derived classes
	*/
	function buildTitle($a_title, $a_id, $a_type)
	{
//echo "<br>-$a_title-$a_type-$a_id-";
		if ($a_type == "st")
		{
			return ilStructureObject::_getPresentationTitle($a_id,
				$this->lm_obj->isActiveNumbering());
		}

		return $a_title;
		/*
		if ($this->lm_obj->getTOCMode() == "chapters" || $a_type != "pg")
		{
			return $a_title;
		}
		else
		{
			if ($a_type == "pg")
			{
				return ilLMPageObject::_getPresentationTitle($a_id,
					$this->lm_obj->getPageHeader(), $this->lm_obj->isActiveNumbering());
			}
		}*/
	}


	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		switch($a_type)
		{
			case "pg":
				$this->ctrl->setParameterByClass("ilLMPageObjectGUI", "obj_id", $a_node_id);
				return $this->ctrl->getLinkTargetByClass("ilLMPageObjectGUI",
					"edit", array($this->gui_class));
				break;

			case "st":
				$this->ctrl->setParameterByClass("ilStructureObjectGUI", "obj_id", $a_node_id);
				return $this->ctrl->getLinkTargetByClass("ilStructureObjectGUI",
					"view", array($this->gui_class));
				break;
		}
	}


	/**
	* get style class for node
	*/
	function getNodeStyleClass($a_id, $a_type)
	{
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		
		$active = ilLMObject::_lookupActive($a_id);
		if (!$active)
		{
			return "il_PageDisabled";
		}
		else
		{
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			$contains_dis = ilPageObject::_lookupContainsDeactivatedElements($a_id,
				$this->lm_obj->getType());
			if ($contains_dis)
			{
				return "il_PageDeactivatedElements";
			}
		}
		return "";
	}

} // END class.ilLMEditorExplorer
?>
