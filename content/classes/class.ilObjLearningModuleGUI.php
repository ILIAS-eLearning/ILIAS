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
require_once("content/classes/class.ilObjContentObjectGUI.php");

/**
* Class ilLearningModuleGUI
*
* GUI class for ilLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilObjLearningModuleGUI: ilLMPageObjectGUI, ilStructureObjectGUI, ilObjStyleSheetGUI
*
* @package content
*/
class ilObjLearningModuleGUI extends ilObjContentObjectGUI
{
	var $object;
	/**
	* Constructor
	* @access	public
	*/
	function ilObjLearningModuleGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "lm";

		parent::ilObjContentObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		# BETTER DO IT HERE THAN IN PARENT CLASS ( PROBLEMS FOR import, create)
		$this->assignObject();

		// SAME REASON
		if($a_id != 0)
		{
			$this->lm_tree =& $this->object->getLMTree();
		}
		/*
		global $ilias, $tpl, $lng, $objDefinition;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->lm_tree =& $a_tree;
		*/

		//$this->read(); todo
	}

	function assignObject()
	{
		include_once("content/classes/class.ilObjLearningModule.php");

		$this->link_params = "ref_id=".$this->ref_id;
		$this->object =& new ilObjLearningModule($this->id, true);
	}

	/*
	function setLearningModuleObject(&$a_lm_obj)
	{
		$this->lm_obj =& $a_lm_obj;
		//$this->obj =& $this->lm_obj;
	}*/

	// MOVED ALL *style METHODS TO base class

    function setilLMMenu()
	{
		if (!$this->object->isActiveLMMenu())
		{
			return "";
		}

		include_once("./classes/class.ilTemplate.php");

		$tpl_menu =& new ilTemplate("tpl.lm_menu.html", true, true, true);
		$tpl_menu->setCurrentBlock("lm_menu_btn");

		if ($this->object->isActiveTOC())
		{
			$tpl_menu->setVariable("BTN_LINK", "./lm_presentation.php?cmd=showTableOfContents&ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
			$tpl_menu->setVariable("BTN_TXT", $this->lng->txt("cont_contents"));
			$tpl_menu->setVariable("BTN_TARGET", "_top");
			$tpl_menu->parseCurrentBlock();
		}

		return $tpl_menu->get();


		//return "";
	}

	function view()
	{
		$this->properties();
	}

}

?>
