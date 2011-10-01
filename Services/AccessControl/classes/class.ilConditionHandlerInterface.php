<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* class ilConditionHandlerInterface
*
* @author Stefan Meyer <meyer@leifos.com> 
* @version $Id$
* This class is aggregated in folders, groups which have a parent course object
* Since it is something like an interface, all varirables, methods have there own name space (names start with cci) to avoid collisions
* 
* @ilCtrl_Calls ilConditionHandlerInterface:
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

	/**
	 * Constructor
	 * @global <type> $lng
	 * @global <type> $tpl
	 * @global <type> $tree
	 * @global <type> $ilCtrl
	 * @param <type> $gui_obj
	 * @param <type> $a_ref_id
	 */
	public function ilConditionHandlerInterface($gui_obj,$a_ref_id = null)
	{
		global $lng,$tpl,$tree,$ilCtrl;

		include_once "./Services/AccessControl/classes/class.ilConditionHandler.php";

		$this->ch_obj =& new ilConditionHandler();

		$this->ctrl =& $ilCtrl;
		$this->gui_obj =& $gui_obj;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('rbac');
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

	public function executeCommand()
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
	 * Check if target has refernce id
	 * @return bool
	 */
	public function isTargetReferenced()
	{
		return $this->getTargetRefId() ? true : false;
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
		
		include_once "./Services/AccessControl/classes/class.ilConditionHandler.php";

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
	 * list conditions
	 * @global ilToolbar 
	 */
	protected function listConditions()
	{
		global $ilToolbar;

		$ilToolbar->addButton($this->lng->txt('add_condition'),$this->ctrl->getLinkTarget($this,'selector'));
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.list_conditions.html','Services/AccessControl');

		$optional_conditions = ilConditionHandler::getOptionalConditionsOfTarget(
			$this->getTargetRefId(),
			$this->getTargetId(),
			$this->getTargetType()
		);
		if(count($optional_conditions))
		{
			if(!$_REQUEST["list_mode"])
			{
				$_REQUEST["list_mode"] = "subset";
			}
		}
		else if(!$_REQUEST["list_mode"])
		{
			$_REQUEST["list_mode"] = "all";
		}
		$form = $this->showObligatoryForm($optional_conditions);
		$this->tpl->setVariable('TABLE_SETTINGS',$form->getHTML());

		include_once './Services/AccessControl/classes/class.ilConditionHandlerTableGUI.php';
		$table = new ilConditionHandlerTableGUI($this,'listConditions', ($_REQUEST["list_mode"] != "all"));
		$table->setConditions(
			ilConditionHandler::_getConditionsOfTarget(
				$this->getTargetRefId(),
				$this->getTargetId(),
				$this->getTargetType()
			)
		);

		$this->tpl->setVariable('TABLE_CONDITIONS',$table->getHTML());
		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Save obligatory settings
	 */
	protected function saveObligatorySettings()
	{
		$form = $this->showObligatoryForm();
		if($form->checkInput())
		{
			$old_mode = $form->getInput("old_list_mode");
			switch($form->getInput("list_mode"))
			{
				case "all":
					if($old_mode != "all")
					{
						include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
						$optional_conditions = ilConditionHandler::getOptionalConditionsOfTarget(
							$this->getTargetRefId(),
							$this->getTargetId(),
							$this->getTargetType()
						);
						if(sizeof($optional_conditions) > 1)
						{
							// Set all optional conditions to obligatory
							foreach($optional_conditions as $item)
							{
								ilConditionHandler::updateObligatory($item["condition_id"], true);
							}
						}
					}
					break;
				
				case "subset":
					$num_req = $form->getInput('required');
					if($old_mode != "subset")
					{
						$all_conditions = ilConditionHandler::_getConditionsOfTarget(
							$this->getTargetRefId(),
							$this->getTargetId(),
							$this->getTargetType()
						);
						foreach($all_conditions as $item)
						{
							ilConditionHandler::updateObligatory($item["condition_id"], false);
						}
						$num_req = 1;
					}
					ilConditionHandler::saveNumberOfRequiredTriggers(
									$this->getTargetRefId(),
									$this->getTargetId(),
									$num_req
								);
					break;
			}
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this,'listConditions');
		}

		$form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Save obligatory settings
	 */
	protected function saveObligatoryList()
	{
		$all_conditions = ilConditionHandler::_getConditionsOfTarget(
							$this->getTargetRefId(),
							$this->getTargetId(),
							$this->getTargetType()
						);
		
		if($_POST["obl"] && sizeof($_POST["obl"]) > sizeof($all_conditions)-2)
		{
			ilUtil::sendFailure($this->lng->txt("rbac_precondition_minimum_optional"), true);
			$this->ctrl->redirect($this,'listConditions');
		}
		
		foreach($all_conditions as $item)
		{
			$status = false;
			if($_POST["obl"] && in_array($item["condition_id"], $_POST["obl"]))
			{
				$status = true;
			}
			ilConditionHandler::updateObligatory($item["condition_id"], $status);
		}
		
		// re-calculate 
		ilConditionHandler::calculateRequiredTriggers(
				$this->getTargetRefId(),
				$this->getTargetId(),
				$this->getTargetType(),
				true
			);
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'listConditions');
	}

	/**
	 * Show obligatory form
	 * @return ilPropertyFormGUI
	 */
	protected function showObligatoryForm($opt = array())
	{
		if(!$opt)
		{
			$opt = ilConditionHandler::getOptionalConditionsOfTarget(
				$this->getTargetRefId(),
				$this->getTargetId(),
				$this->getTargetType()
			);
		}

		$all = ilConditionHandler::_getConditionsOfTarget($this->getTargetRefId(),$this->getTargetId());
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this),'listConditions');
		$form->setTitle($this->lng->txt('precondition_obligatory_settings'));
		$form->addCommandButton('saveObligatorySettings', $this->lng->txt('save'));
		
		$mode = new ilRadioGroupInputGUI($this->lng->txt("rbac_precondition_mode"), "list_mode");
		$form->addItem($mode);
		$mode->setValue($_REQUEST["list_mode"]);
		
		$mall = new ilRadioOption($this->lng->txt("rbac_precondition_mode_all"), "all");
		$mall->setInfo($this->lng->txt("rbac_precondition_mode_all_info"));
		$mode->addOption($mall);
		
		$msubset = new ilRadioOption($this->lng->txt("rbac_precondition_mode_subset"), "subset");
		$msubset->setInfo($this->lng->txt("rbac_precondition_mode_subset_info"));
		$mode->addOption($msubset);

		$obl = new ilNumberInputGUI($this->lng->txt('precondition_num_obligatory'), 'required');
		$obl->setInfo($this->lng->txt('precondition_num_optional_info'));
		if(count($opt))
		{
			$obligatory = ilConditionHandler::calculateRequiredTriggers(
				$this->getTargetRefId(),
				$this->getTargetId(),
				$this->getTargetType()
			);
			$min = count($all) - count($opt) + 1;
			$max = count($all) - 1;
		}
		else
		{
			$obligatory = $min = $max = 1;
		}
		$obl->setValue($obligatory);
		$obl->setRequired(true);
		$obl->setSize(1);
		$obl->setMinValue($min);
		$obl->setMaxValue($max);
		$msubset->addSubItem($obl);
		
		$old_mode = new ilHiddenInputGUI("old_list_mode");
		$old_mode->setValue($_REQUEST["list_mode"]);
		$form->addItem($old_mode);

		return $form;
	}


	function edit()
	{
		global $ilObjDataCache;

		if(!$_GET['condition_id'])
		{
			ilUtil::sendFailure("Missing id: condition_id");
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
			ilUtil::sendFailure("Missing id: condition_id");
			$this->listConditions();
			return false;
		}

		// Update condition
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
		$condition_handler = new ilConditionHandler();

		$condition = ilConditionHandler::_getCondition((int) $_GET['condition_id']);
		$condition_handler->setOperator($_POST['operator']);
		$condition_handler->setObligatory((int) $_POST['obligatory']);
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
			
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_refreshStatus($condition['trigger_obj_id']);
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->ctrl->redirect($this,'listConditions');
	}
		

	function delete()
	{
		if(!count($_POST['conditions']))
		{
			ilUtil::sendFailure($this->lng->txt('no_condition_selected'));
			$this->listConditions();
			return true;
		}

		foreach($_POST['conditions'] as $condition_id)
		{
			$this->ch_obj->deleteCondition($condition_id);
		}
		ilUtil::sendSuccess($this->lng->txt('condition_deleted'),true);
		$this->ctrl->redirect($this,'listConditions');

		return true;
	}
	
	function selector()
	{
		global $tree;

		include_once ("./Services/AccessControl/classes/class.ilConditionSelector.php");

		$this->tpl->addBlockFile('ADM_CONTENT', "adm_content", "tpl.condition_selector.html");

		ilUtil::sendInfo($this->lng->txt("condition_select_object"));

		$exp = new ilConditionSelector($this->ctrl->getLinkTarget($this,'copySelector'));
		$exp->setExpand($_GET["condition_selector_expand"] ? $_GET["condition_selector_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'selector'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->getTargetRefId());

		if($this->getTargetRefId())
		{
			$path = $tree->getPathId($this->getTargetRefId());
			array_pop($path);
			$exp->setForceOpenPath($path);
		}

		$exp->addFilter('crs');
		$exp->addFilter('tst');
		$exp->addFilter('sahs');
		$exp->addFilter('svy');

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
			ilUtil::sendFailure("Missing id: condition_id");
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
		if(!$_POST['operator'])
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
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
		$this->ch_obj->setObligatory((int) $_POST['obligatory']);
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
			ilUtil::sendFailure($this->ch_obj->getErrorMessage(),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('added_new_condition'),true);
		}

		$this->ctrl->redirect($this,'listConditions');

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
		ilUtil::sendSuccess($this->lng->txt('conditions_updated'));
		
		$this->ctrl->returnToParent($this);

		return true;
	}
	function __getConditionsOfTarget()
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';

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
		
		$info_source = new ilNonEditableValueGUI($this->lng->txt("rbac_precondition_source"));
		$info_source->setValue(ilObject::_lookupTitle(ilObject::_lookupObjId($a_source_id)));
		$this->form->addItem($info_source);
		
		$info_target = new ilNonEditableValueGUI($this->lng->txt("rbac_precondition_target"));
		$info_target->setValue($this->getTargetTitle());
		$this->form->addItem($info_target);
		
		/* moved to list
		$obl = new ilCheckboxInputGUI($this->lng->txt('precondition_obligatory'), 'obligatory');
		$obl->setInfo($this->lng->txt('precondition_obligatory_info'));
		$obl->setValue(1);
		if($a_condition_id)
		{
			$obl->setChecked($condition['obligatory']);
		}
		else
		{
			$obl->setChecked(true);
		}
		$this->form->addItem($obl);
	    */
		$obl = new ilHiddenInputGUI('obligatory');
		if($a_condition_id)
		{
			$obl->setValue($condition['obligatory']);
		}
		else
		{
			$obl->setValue(1);
		}
		$this->form->addItem($obl);
	 	
	 	$sel = new ilSelectInputGUI($this->lng->txt('condition'),'operator');
		include_once "./Services/AccessControl/classes/class.ilConditionHandler.php";
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
	 			$this->form->setTitle($this->lng->txt('precondition'));
	 			$this->form->addCommandButton('updateCondition',$this->lng->txt('save'));
	 			$this->form->addCommandButton('listConditions',$this->lng->txt('cancel'));
	 			break;
	 			
	 		
	 		case 'add':
	 			$this->form->setTitleIcon(ilUtil::getImagePath('icon_'.$this->getTargetType().'.gif'));
	 			$this->form->setTitle($this->lng->txt('add_condition'));
	 			$this->form->addCommandButton('assign',$this->lng->txt('save'));
	 			$this->form->addCommandButton('selector',$this->lng->txt('back'));
	 			break;
	 	}
	 	return true;
	}
}
?>