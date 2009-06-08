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

class ilConditionSelector extends ilExplorer
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
	function ilConditionSelector($a_target)
	{
		global $tree,$ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";

		$this->setSessionExpandVariable("condition_selector_expand");

		// add here all container objects
		$this->addFilter("root");
		$this->addFilter("cat");
		$this->addFilter("grp");
		$this->addFilter("fold");
		$this->addFilter("crs");
		$this->addFilter("exc");
		$this->addFilter("tst");

		$this->setFilterMode(IL_FM_POSITIVE);
		$this->setFiltered(true);
	}

	function setControlClass(&$class)
	{
		$this->control_class =& $class;
	}
	function &getControlClass()
	{
		return $this->control_class;
	}

	function setSelectableTypes($a_type)
	{
		$this->selectable_types  = $a_type;
	}
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	

	function buildLinkTarget($a_node_id, $a_type)
	{
		if(in_array($a_type,$this->selectable_types))
		{
			#$this->ctrl->setParameterByClass('ilrepositorygui','source_id',$a_node_id);
			$this->ctrl->setParameter($this->getControlClass(),'source_id',$a_node_id);
			
			return $this->ctrl->getLinkTarget($this->getControlClass(),'add');
		}
		else
		{
			$this->ctrl->setParameterByClass('ilrepositorygui',"ref_id",$this->ref_id);
			return $this->ctrl->getLinkTargetByClass('ilrepositorygui','copySelector');
		}

	}

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	function isClickable($a_type, $a_ref_id)
	{
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

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilRepositoryExplorer
?>
