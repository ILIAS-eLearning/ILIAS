<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
* LM Menu Object Selector
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMMenuObjectSelector extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $ctrl;
	var $selectable_type;
	var $ref_id;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMMenuObjectSelector($a_target,&$a_gui_obj)
	{
		global $tree,$ilCtrl;

		$this->ctrl = $ilCtrl;
		
		$this->gui_obj = $a_gui_obj;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";
		$this->setSessionExpandVariable("lm_menu_expand");
		$this->addFilter("rolf");
		$this->addFilter("adm");
	}

	function setSelectableTypes($a_types)
	{
		$this->selectable_types  = $a_types;
	}

	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	

	function buildLinkTarget($a_node_id, $a_type)
	{
		if(in_array($a_type,$this->selectable_types))
		{
			$this->ctrl->setParameter($this->gui_obj,'link_ref_id',$a_node_id);
			if ($_GET["menu_entry"] > 0)
			{
				return $this->ctrl->getLinkTarget($this->gui_obj,'editMenuEntry');
			}
			else
			{
				return $this->ctrl->getLinkTarget($this->gui_obj,'addMenuEntry');
			}
		}
	}

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	function isClickable($a_type, $a_ref_id)
	{//return true;
		return in_array($a_type,$this->selectable_types) and $a_ref_id != $this->ref_id;
	}

	function showChilds($a_ref_id)
	{
		global $rbacsystem;

		if ($a_ref_id == 0)
		{
			return true;
		}

		if ($rbacsystem->checkAccess("read", $a_ref_id))
		{
			return true;
		}
		else
		{
			return false;
		}
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

		$tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

//		$tpl->setCurrentBlock("row");
//		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}
} // END class ilLMMenuObjectSelector
?>
