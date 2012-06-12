<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjectSelector.php 4880 2004-09-06 10:55:33Z smeyer $
*
* @package core
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilObjectSelector extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	public $root_id;
	public $output;
	public $ctrl;

	public $selectable_type;
	public $ref_id;

	/**
	 * @param $a_target
	 */
	function ilObjectSelector($a_target)
	{
		global $tree, $ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";

		$this->setSessionExpandVariable("paya_link_expand");

		// add here all container objects
		#$this->addFilter("root");
		#$this->addFilter("cat");

		#$this->setFilterMode(IL_FM_NEGATIVE);
		$this->setFiltered(false);
	}

	function buildLinkTarget($a_node_id, $a_type)
	{
		if($a_type == $this->selectable_type)
		{
			$this->ctrl->setParameterByClass('ilpaymentobjectgui','source_id',$a_node_id);
			return $this->ctrl->getLinkTargetByClass('ilpaymentobjectgui','linkChilds');
		}
		else
		{
			$this->ctrl->setParameterByClass('ilrepositorygui',"ref_id",$this->ref_id);
			return $this->ctrl->getLinkTargetByClass('ilrepositorygui','linkSelector');
		}
	}

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	function isClickable($a_type, $a_ref_id)
	{
		return $a_type == $this->selectable_type and $a_ref_id != $this->ref_id;
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
	* @param	integer a_obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id, $a_option)
	{
		global $lng;

		$tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilObjectSelector
?>
