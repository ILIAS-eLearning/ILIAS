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


/**
* class ilConditionHandlerInterface
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* This class is aggregated in folders, groups which have a parent course object
* Since it is something like an interface, all varirables, methods have there own name space (names start with cci) to avoid collisions
* 
* @extends Object
* @package ilias-core
*/

class ilConditionHandlerInterface
{
	var $lng;
	var $tpl;
	var $tree;

	var $ch_obj;
	var $target_obj;
	var $client_obj;
	var $target_id;
	var $target_type;
	var $target_title;
	var $target_ref_id;

	function ilConditionHandlerInterface(&$gui_obj,$a_ref_id = null)
	{
		global $lng,$tpl,$tree;

		include_once "./classes/class.ilConditionHandler.php";

		$this->ch_obj =& new ilConditionHandler();

		$this->gui_obj =& $gui_obj;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		
		if($a_ref_id)
		{
			$this->target_obj =& ilObjectFactory::getInstanceByRefId($a_ref_id);
		}
		else
		{
			$this->target_obj =& $this->gui_obj->object;
		}

		// this only works for ilObject derived objects (other objects
		// should call set() methods manually	
		if (is_object($this->target_obj))
		{
			$this->setTargetId($this->target_obj->getId());
			$this->setTargetRefId($this->target_obj->getRefId());
			$this->setTargetType($this->target_obj->getType());
			$this->setTargetTitle($this->target_obj->getTitle());
		}
		
	}
	
	/**
	* set target id
	*/
	function setTargetId($a_target_id)
	{
		$this->target_id = $a_target_id;
	}
	
	/**
	* get target id
	*/
	function getTargetId()
	{
		return $this->target_id;
	}

	/**
	* set target ref id
	*/
	function setTargetRefId($a_target_ref_id)
	{
		$this->target_ref_id = $a_target_ref_id;
	}
	
	/**
	* get target ref id
	*/
	function getTargetRefId()
	{
		return $this->target_ref_id;
	}

	/**
	* set target type
	*/
	function setTargetType($a_target_type)
	{
		$this->target_type = $a_target_type;
	}
	
	/**
	* get target type
	*/
	function getTargetType()
	{
		return $this->target_type;
	}

	/**
	* set target title
	*/
	function setTargetTitle($a_target_title)
	{
		$this->target_title = $a_target_title;
	}
	
	/**
	* get target title
	*/
	function getTargetTitle()
	{
		return $this->target_title;
	}

	function chi_init(&$chi_target_obj,$a_ref_id = null)
	{
		echo 'deprecated';
		
		include_once "./classes/class.ilConditionHandler.php";

		$this->ch_obj =& new ilConditionHandler();

		if($a_ref_id)
		{
			$this->target_obj =& ilObjectFactory::getInstanceByRefId($a_ref_id);
		}
		else
		{
			$this->target_obj =& $this->object;
		}

		return true;
	}

	/**
	* get conditioni list
	*/
	function &chi_list()
	{
		global $rbacsystem;

		$operators = array(''			=> $this->lng->txt('condition_select_one'),
						   'passed'		=> $this->lng->txt('condition_passed'));

		$tpl =& new ilTemplate("tpl.table.html", true, true);


		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->gui_obj->ctrl->getFormAction($this->gui_obj));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		if(count(ilConditionHandler::_getConditionsOfTarget($this->getTargetId(), $this->getTargetType())))
		{

			$tpl->setCurrentBlock("tbl_action_btn");
			$tpl->setVariable("BTN_NAME","chi_delete");
			$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("plain_button");
			$tpl->setVariable("PBTN_NAME","chi_update");
			$tpl->setVariable("PBTN_VALUE",$this->lng->txt("condition_update"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","chi_selector");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("add_condition"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.condition_handler_row.html");

		$counter = 0;
		foreach(ilConditionHandler::_getConditionsOfTarget($this->getTargetId(), $this->getTargetType()) as $condition)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($condition['trigger_ref_id']);

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("CHECKBOX",ilUtil::formCheckbox(0,"conditions[]",$condition['id']));
			$tpl->setVariable("OBJ_TITLE",$tmp_obj->getTitle());
			$tpl->setVariable("OBJ_DESCRIPTION",$tmp_obj->getDescription());
			$tpl->setVariable("ROWCOL", ilUtil::switchColor($counter++,"tblrow2","tblrow1"));

			$tpl->setVariable("OBJ_CONDITION",ilUtil::formSelect($condition['operator'],
																 "operator[".$condition['id']."]",
																 $operators,
																 false,
																 true));

			$tpl->setVariable("OBJ_VALUE_NAME","value[".$condition['id']."]");
			$tpl->setVariable("OBJ_VALUE_VALUE",$condition['value']); 

			$tpl->parseCurrentBlock();
		}


		// create table
		include_once './classes/class.ilTableGUI.php';

		$tbl =& new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->getTargetTitle()." ".$this->lng->txt("preconditions"),"icon_".$this->getTargetType().".gif",
					   $this->lng->txt("preconditions"));

		$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("condition"),
								   $this->lng->txt("value")));
		$tbl->setHeaderVars(array("","title","condition","value"), 
							array("ref_id" => $this->getTargetRefId(),
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"],
								  "cmd" => "cci_edit"));
		$tbl->setColumnWidth(array("1%","40%","30%","30%"));

		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(10);

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		return $tpl->get();
	}

	function chi_delete()
	{
		if(!count($_POST['conditions']))
		{
			sendInfo('no_condition_selected');

			return true;
		}

		foreach($_POST['conditions'] as $condition_id)
		{
			$this->ch_obj->deleteCondition($condition_id);
		}
		sendInfo($this->lng->txt('condition_deleted'));
		return true;
	}
	
	function chi_selector($a_tpl_block = "content", $a_tpl_var = "OBJECTS")
	{
		include_once ("classes/class.ilConditionSelector.php");

		$this->tpl->setCurrentBlock($a_tpl_block);
		$this->tpl->addBlockFile($a_tpl_var, "objects", "tpl.condition_selector.html");

		sendInfo($this->lng->txt("condition_select_object"));

		$exp = new ilConditionSelector($this->gui_obj->ctrl->getLinkTarget($this->gui_obj,'copySelector'));
		$exp->setExpand($_GET["condition_selector_expand"] ? $_GET["condition_selector_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->gui_obj->ctrl->getLinkTarget($this->gui_obj,'chi_selector'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->getTargetRefId());
		$exp->addFilter('crs');
		$exp->setSelectableTypes($this->ch_obj->getTriggerTypes());
		$exp->setControlClass($this->gui_obj);
		// build html-output
		$exp->setOutput(0);

		$this->tpl->setCurrentBlock("objects");
		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* assign new trigger condition to target
	*/
	function chi_assign($a_automatic_validation = true)
	{
		if(!isset($_GET['source_id']))
		{
			echo "class.ilConditionHandlerInterface: no source_id given";

			return false;
		}

		// automatic determination of obj id and type works only for
		// referenced objects
		if ($this->getTargetRefId() > 0)
		{
			if(!$target_obj =& ilObjectFactory::getInstanceByRefId($this->getTargetRefId(),false))
			{
				echo 'ilConditionHandler: Target object does not exist';
			}
			$this->setTargetObjId($target_obj->getId());
			$this->setTargetType($target_obj->getType());
		}
		$this->ch_obj->setTargetRefId($this->getTargetRefId());
		$this->ch_obj->setTargetObjId($this->getTargetId());
		$this->ch_obj->setTargetType($this->getTargetType());
		
		// this has to be changed, if non referenced trigger are implemted
		if(!$trigger_obj =& ilObjectFactory::getInstanceByRefId((int) $_GET['source_id'],false))
		{
			echo 'ilConditionHandler: Trigger object does not exist';
		}
		$this->ch_obj->setTriggerRefId($trigger_obj->getRefId());
		$this->ch_obj->setTriggerObjId($trigger_obj->getId());
		$this->ch_obj->setTriggerType($trigger_obj->getType());
		$this->ch_obj->setOperator('');
		$this->ch_obj->setValue('');

		$this->ch_obj->enableAutomaticValidation($a_automatic_validation);
		if(!$this->ch_obj->storeCondition())
		{
			sendInfo($this->ch_obj->getErrorMessage());
		}
		else
		{
			sendInfo($this->lng->txt('added_new_condition'));
		}
		return true;
	}

	function chi_update()
	{
		#if(in_array('',$_POST['operator']))
		#{
		#	sendInfo($this->lng->txt('select_one_operator'));

		#	return false;
		#}
		foreach(ilConditionHandler::_getConditionsOfTarget($this->getTargetId(), $this->getTargetType()) as $condition)
		{
			$this->ch_obj->setOperator($_POST['operator'][$condition["id"]]);
			$this->ch_obj->setValue($_POST['value'][$condition["id"]]);
			$this->ch_obj->updateCondition($condition['id']);

		}
		sendInfo($this->lng->txt('conditions_updated'));

		return true;
	}

}
?>