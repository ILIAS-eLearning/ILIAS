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

/*
* Explorer View for Learning Module Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

require_once("content/classes/class.ilLMExplorer.php");

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
		$this->setExpandTarget("lm_edit.php?cmd=explorer&ref_id=".$this->lm_obj->getRefId());
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng, $ilias;

		$tpl = new ilTemplate("tpl.tree.html", true, true);

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

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
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
					"view", array($this->gui_class));
				break;

			case "st":
				$this->ctrl->setParameterByClass("ilStructureObjectGUI", "obj_id", $a_node_id);
				return $this->ctrl->getLinkTargetByClass("ilStructureObjectGUI",
					"view", array($this->gui_class));
				break;
		}
	}


} // END class.ilLMEditorExplorer
?>
