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
*/

class ilConditionHandlerInterface
{
	var $ctrl = null;

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

	var $automatic_validation = true;

	function ilConditionHandlerInterface(&$gui_obj,$a_ref_id = null)
	{
		global $lng,$tpl,$tree,$ilCtrl;

		include_once "./classes/class.ilConditionHandler.php";

		$this->ch_obj =& new ilConditionHandler();

		$this->ctrl =& $ilCtrl;
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

	function setBackButtons($a_btn_arr)
	{
		$_SESSION['precon_btn'] = $a_btn_arr;
	}
	function getBackButtons()
	{
		return $_SESSION['precon_btn'] ? $_SESSION['precon_btn'] : array();
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		switch ($next_class)
		{
			default:
				if (empty($cmd))
				{
					$cmd = "view";
				}
				$this->$cmd();
				break;
		}
	}

	function setAutomaticValidation($a_status)
	{
		$this->automatic_validation = $a_status;
	}
	function getAutomaticValidation()
	{
		return $this->automatic_validation;
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


	function listConditions()
	{
		global $ilObjDataCache;

		$this->lng->loadLanguageModule('crs');

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.condition_handler_edit.html');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_'.$this->getTargetType().'.gif'));
		$this->tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('obj_'.$this->getTargetType()));
		$this->tpl->setVariable("TABLE_TITLE",$this->getTargetTitle().' ('.$this->lng->txt('preconditions').')');

		// Table header
		$this->tpl->setVariable("HEAD_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("HEAD_CONDITION",$this->lng->txt('condition'));
		
		// Table footer
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('add_condition'));
		$this->tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));


		if(!count($conditions = ilConditionHandler::_getConditionsOfTarget($this->getTargetRefId(),$this->getTargetId(), $this->getTargetType())))
		{
			$this->tpl->setVariable("EMPTY_TXT",$this->lng->txt('no_conditions_found'));
			return true;
		}

		$counter = 0;
		foreach($conditions as $condition)
		{
			$this->tpl->setCurrentBlock("table_content");
			
			$this->tpl->setVariable('TRIGGER_SRC',ilUtil::getImagePath('icon_'.$condition['trigger_type'].'_s.gif'));
			$this->tpl->setVariable('TRIGGER_ALT',$this->lng->txt('obj_'.$condition['trigger_type']));
			$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($counter++,"tblrow1","tblrow2"));
			$this->tpl->setVariable("CHECKBOX",ilUtil::formCheckbox(0,"conditions[]",$condition['id']));
			$this->tpl->setVariable("TITLE",$ilObjDataCache->lookupTitle($condition['trigger_obj_id']));
			if(strlen($desc = $ilObjDataCache->lookupDescription($condition['trigger_obj_id'])))
			{
				$this->tpl->setVariable("DESCRIPTION",$desc);
			}
			$this->tpl->setVariable("OBJ_CONDITION",$this->lng->txt('condition_'.$condition['operator']));

			// Edit link
			$this->tpl->setVariable("EDIT",$this->lng->txt('edit'));

			$this->ctrl->setParameter($this,'condition_id',$condition['id']);
			$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTarget($this,'edit'));
			$this->ctrl->clearParameters($this);
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}

	function edit()
	{
		global $ilObjDataCache;

		if(!$_GET['condition_id'])
		{
			ilUtil::sendInfo("Missing id: condition_id");
			$this->listConditions();
			return false;
		}
		$condition = ilConditionHandler::_getCondition((int) $_GET['condition_id']);

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.condition_handler_edit_condition.html');
		$this->ctrl->setParameter($this,'condition_id',(int) $_GET['condition_id']);
		
		$this->initFormCondition($condition['trigger_ref_id'],(int) $_GET['condition_id'],'edit');
		$this->tpl->setVariable('CONDITION_TABLE',$this->form->getHTML());
	}

	function updateCondition()
	{
		global $ilObjDataCache;

		if(!$_GET['condition_id'])
		{
			ilUtil::sendInfo("Missing id: condition_id");
			$this->listConditions();
			return false;
		}

		// Update condition
		include_once 'classes/class.ilConditionHandler.php';
		$condition_handler = new ilConditionHandler();

		$condition = ilConditionHandler::_getCondition((int) $_GET['condition_id']);
		$condition_handler->setOperator($_POST['operator']);
		$condition_handler->setTargetRefId($this->getTargetRefId());
		$condition_handler->setValue('');
		switch($this->getTargetType())
		{
			case 'st':
				$condition_handler->setReferenceHandlingType($_POST['ref_handling']);
				break;
			
			default:
				$condition_handler->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
				break;	
		}
		$condition_handler->updateCondition($condition['id']);

		// Update relevant sco's
		if($condition['trigger_type'] == 'sahs')
		{
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			$lp_collection = new ilLPCollections($condition['trigger_obj_id']);
			$lp_collection->deleteAll();

			$items = is_array($_POST['item_ids']) ? $_POST['item_ids'] : array();
			foreach($items as $item_id)
			{
				$lp_collection->add($item_id);
			}
		}

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->listConditions();
	}
		

	function delete()
	{
		if(!count($_POST['conditions']))
		{
			ilUtil::sendInfo('no_condition_selected');
			$this->listConditions();
			return true;
		}

		foreach($_POST['conditions'] as $condition_id)
		{
			$this->ch_obj->deleteCondition($condition_id);
		}
		ilUtil::sendInfo($this->lng->txt('condition_deleted'));
		$this->listConditions();

		return true;
	}
	
	function selector()
	{
		include_once ("classes/class.ilConditionSelector.php");

		$this->tpl->addBlockFile('ADM_CONTENT', "adm_content", "tpl.condition_selector.html");

		ilUtil::sendInfo($this->lng->txt("condition_select_object"));

		$exp = new ilConditionSelector($this->ctrl->getLinkTarget($this,'copySelector'));
		$exp->setExpand($_GET["condition_selector_expand"] ? $_GET["condition_selector_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'selector'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->getTargetRefId());

		$exp->addFilter('crs');
		$exp->addFilter('tst');
		$exp->addFilter('sahs');

		$exp->setSelectableTypes($this->ch_obj->getTriggerTypes());
		$exp->setControlClass($this);
		// build html-output
		$exp->setOutput(0);

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
		$this->tpl->parseCurrentBlock();
	}

	function add()
	{
		global $ilObjDataCache;

		if(!$_GET['source_id'])
		{
			ilUtil::sendInfo("Missing id: condition_id");
			$this->selector();
			return false;
		}
		
		$this->initFormCondition((int) $_GET['source_id'],0,'add');
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.condition_handler_add.html');
		$this->tpl->setVariable('CONDITION_TABLE',$this->form->getHTML());		
	}


	/**
	* assign new trigger condition to target
	*/
	function assign()
	{
		if(!isset($_GET['source_id']))
		{
			echo "class.ilConditionHandlerInterface: no source_id given";

			return false;
		}
		if(!strlen($_POST['operator']))
		{
			ilUtil::sendInfo($this->lng->txt('no_operator_selected'));
			$this->add();

			return false;
		}


		$this->ch_obj->setTargetRefId($this->getTargetRefId());
		$this->ch_obj->setTargetObjId($this->getTargetId());
		$this->ch_obj->setTargetType($this->getTargetType());
		
		switch($this->getTargetType())
		{
			case 'st':
				$this->ch_obj->setReferenceHandlingType($_POST['ref_handling']);
				break;
			
			default:
				$this->ch_obj->setReferenceHandlingType(ilConditionHandler::UNIQUE_CONDITIONS);
				break;	
		}
		// this has to be changed, if non referenced trigger are implemted
		if(!$trigger_obj =& ilObjectFactory::getInstanceByRefId((int) $_GET['source_id'],false))
		{
			echo 'ilConditionHandler: Trigger object does not exist';
		}
		$this->ch_obj->setTriggerRefId($trigger_obj->getRefId());
		$this->ch_obj->setTriggerObjId($trigger_obj->getId());
		$this->ch_obj->setTriggerType($trigger_obj->getType());
		$this->ch_obj->setOperator($_POST['operator']);
		$this->ch_obj->setValue('');

		// Save assigned sco's
		if($this->ch_obj->getTriggerType() == 'sahs')
		{
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			$lp_collection = new ilLPCollections($this->ch_obj->getTriggerObjId());
			$lp_collection->deleteAll();

			$items = is_array($_POST['item_ids']) ? $_POST['item_ids'] : array();
			foreach($items as $item_id)
			{
				$lp_collection->add($item_id);
			}
		}

		$this->ch_obj->enableAutomaticValidation($this->getAutomaticValidation());
		if(!$this->ch_obj->storeCondition())
		{
			ilUtil::sendInfo($this->ch_obj->getErrorMessage());
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('added_new_condition'));
		}

		$this->listConditions();

		return true;
	}

	function chi_update()
	{
		#if(in_array('',$_POST['operator']))
		#{
		#	ilUtil::sendInfo($this->lng->txt('select_one_operator'));

		#	return false;
		#}
		foreach($this->__getConditionsOfTarget() as $condition)
		{
			$this->ch_obj->setOperator($_POST['operator'][$condition["id"]]);
			$this->ch_obj->setValue($_POST['value'][$condition["id"]]);
			$this->ch_obj->updateCondition($condition['id']);

		}
		ilUtil::sendInfo($this->lng->txt('conditions_updated'));
		
		$this->ctrl->returnToParent($this);

		return true;
	}
	function __getConditionsOfTarget()
	{
		include_once './classes/class.ilConditionHandler.php';

		foreach(ilConditionHandler::_getConditionsOfTarget($this->getTargetRefId(),$this->getTargetId(), $this->getTargetType()) as $condition)
		{
			if($condition['operator'] == 'not_member')
			{
				continue;
			}
			else
			{
				$cond[] = $condition;
			}
		}
		return $cond ? $cond : array();
	}

	function __showButtons()
	{
		if(!$this->getBackButtons())
		{
			return false;
		}

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		foreach($this->getBackButtons() as $name => $link)
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$link);
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt($name));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Init form for condition table
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function initFormCondition($a_source_id,$a_condition_id = 0,$a_mode = 'add')
	{
	 	$trigger_obj_id = ilObject::_lookupObjId($a_source_id);
	 	$trigger_type = ilObject::_lookupType($trigger_obj_id);
	 	
	 	$condition = ilConditionHandler::_getCondition($a_condition_id);
		
	 	if(is_object($this->form))
	 	{
	 		return true;
	 	}
	 	include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
	 	$this->form = new ilPropertyFormGUI();
	 	$this->ctrl->setParameter($this,'source_id',$a_source_id);
	 	$this->form->setFormAction($this->ctrl->getFormAction($this));
	 	
	 	$sel = new ilSelectInputGUI($this->lng->txt('condition'),'operator');
		include_once "./classes/class.ilConditionHandler.php";
		$ch_obj = new ilConditionHandler();
		$operators[0] = $this->lng->txt('select_one');
		foreach($ch_obj->getOperatorsByTargetType($trigger_type) as $operator)
		{
			$operators[$operator] = $this->lng->txt('condition_'.$operator);
		}
		$sel->setValue(isset($condition['operator']) ? $condition['operator'] : 0);
		$sel->setOptions($operators);
		$sel->setRequired(true);
		$this->form->addItem($sel);
	 	
	 	if(ilConditionHandler::_isReferenceHandlingOptional($this->getTargetType()))
	 	{
	 		$rad_opt = new ilRadioGroupInputGUI($this->lng->txt('cond_ref_handling'),'ref_handling');
	 		$rad_opt->setValue(isset($condition['ref_handling']) ? $condition['ref_handling'] : ilConditionHandler::SHARED_CONDITIONS);
	 		
	 		$opt2 = new ilRadioOption($this->lng->txt('cond_ref_shared'),ilConditionHandler::SHARED_CONDITIONS);
	 		$rad_opt->addOption($opt2);

	 		$opt1 = new ilRadioOption($this->lng->txt('cond_ref_unique'),ilConditionHandler::UNIQUE_CONDITIONS);
	 		$rad_opt->addOption($opt1);
	 		
	 		$this->form->addItem($rad_opt);
	 	}
	 	
		// Additional settings for SCO's
		if($trigger_type == 'sahs')
		{
			$this->lng->loadLanguageModule('trac');
			include_once 'Services/Tracking/classes/class.ilLPCollections.php';
			$lp_collections = new ilLPCollections($trigger_obj_id);
			
			$cus = new ilCustomInputGUI($this->lng->txt('trac_sahs_relevant_items'),'item_ids[]');
			$cus->setRequired(true);

			$tpl = new ilTemplate('tpl.condition_handler_sco_row.html',true,true);
			$counter = 0;

			foreach(ilLPCollections::_getPossibleSAHSItems($trigger_obj_id) as $item_id => $sahs_item)
			{
				$tpl->setCurrentBlock("sco_row");
				$tpl->setVariable('SCO_ID',$item_id);
				$tpl->setVariable('SCO_TITLE',$sahs_item['title']);
				$tpl->setVariable('CHECKED',$lp_collections->isAssigned($item_id) ? 'checked="checked"' : '');
				$tpl->parseCurrentBlock();
				$counter++;
			}
			$tpl->setVariable('INFO_SEL',$this->lng->txt('trac_lp_determination_info_sco'));
			$cus->setHTML($tpl->get());
			$this->form->addItem($cus);
		}
	 	switch($a_mode)
	 	{
	 		case 'edit':
	 			$this->form->setTitleIcon(ilUtil::getImagePath('icon_'.$this->getTargetType().'.gif'));
	 			$this->form->setTitle($this->lng->txt('precondition').' ('.
	 				$this->getTargetTitle().' -> '.
	 				ilObject::_lookupTitle(ilObject::_lookupObjId($a_source_id)).')');
	 			$this->form->addCommandButton('updateCondition',$this->lng->txt('save'));
	 			$this->form->addCommandButton('listConditions',$this->lng->txt('cancel'));
	 			break;
	 			
	 		
	 		case 'add':
	 			$this->form->setTitleIcon(ilUtil::getImagePath('icon_'.$this->getTargetType().'.gif'));
	 			$this->form->setTitle($this->lng->txt('add_condition').' ('.
	 				$this->getTargetTitle().' -> '.
	 				ilObject::_lookupTitle(ilObject::_lookupObjId($a_source_id)).')');
	 			$this->form->addCommandButton('assign',$this->lng->txt('save'));
	 			$this->form->addCommandButton('selector',$this->lng->txt('back'));
	 			break;
	 	}
	 	return true;
	}
}
?>