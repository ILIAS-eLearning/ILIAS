<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Repository Explorer
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/

require_once("classes/class.ilExplorer.php");


class ilRoleDesktopItemSelector extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $role_desk_obj = null;


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
	function ilRoleDesktopItemSelector($a_target,$role_desk_item_obj)
	{
		global $tree,$ilCtrl;

		$this->ctrl = $ilCtrl;

		$this->role_desk_obj =& $role_desk_item_obj;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";

		$this->setSessionExpandVariable("role_desk_item_link_expand");

		$this->addFilter("adm");
		$this->addFilter("rolf");
		#$this->addFilter("chat");
		#$this->addFilter('fold');

		$this->setFilterMode(IL_FM_NEGATIVE);
		$this->setFiltered(true);

	}

	function buildLinkTarget($a_node_id, $a_type)
	{
		$this->ctrl->setParameterByClass('ilobjrolegui','item_id',$a_node_id);
		return $this->ctrl->getLinkTargetByClass('ilobjrolegui','assignDesktopItem');

	}

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	function isClickable($a_type, $a_ref_id)
	{
		global $rbacsystem;

		return $rbacsystem->checkAccess('write',$a_ref_id) and !$this->role_desk_obj->isAssigned($a_ref_id);
	}

	function showChilds($a_ref_id)
	{
		global $rbacsystem;

		if($a_ref_id)
		{
			return $rbacsystem->checkAccess('read',$a_ref_id);
		}
		return true;
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

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilObjectSelector
?>
