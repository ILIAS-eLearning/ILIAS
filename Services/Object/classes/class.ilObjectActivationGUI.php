<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjectActivationGUI
*
* @author Stefan Meyer <meyer@leifos.com> 
* @version $Id: class.ilCourseItemAdministrationGUI.php 33406 2012-02-28 16:26:59Z akill $
* 
* @ilCtrl_Calls ilObjectActivationGUI: ilConditionHandlerGUI
*/
class ilObjectActivationGUI
{
	/**
	 * @var ilErrorHandling
	 */
	protected $error;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	protected $parent_ref_id;
	protected $item_id;
	protected $tpl;
	protected $ctrl;
	protected $lng;

	/**
	* Constructor
	* @access public
	*/
	function __construct($a_ref_id,$a_item_id)
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->error = $DIC["ilErr"];
		$this->tabs = $DIC->tabs();
		$this->access = $DIC->access();
		$this->tree = $DIC->repositoryTree();
		$this->user = $DIC->user();
		$this->help = $DIC["ilHelp"];
		$tpl = $DIC["tpl"];
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$ilErr = $DIC["ilErr"];
		$ilTabs = $DIC->tabs();

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');
		$this->err =& $ilErr;
		$this->tabs_gui =& $ilTabs;

		$this->parent_ref_id = $a_ref_id;
		$this->item_id = $a_item_id;
		$this->ctrl->saveParameter($this,'item_id');

	}

	function executeCommand()
	{
		$tpl = $this->tpl;

		$this->__setTabs();

		$cmd = $this->ctrl->getCmd();

		// Check if item id is given and valid
		if(!$this->item_id)
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_item_id_given"),true);
			$this->ctrl->returnToParent($this);
		}
		
		$tpl->getStandardTemplate();
		
		switch($this->ctrl->getNextClass($this))
		{
			case 'ilconditionhandlergui':				
				// preconditions for single course items
				include_once './Services/AccessControl/classes/class.ilConditionHandlerGUI.php';
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
				$new_gui = new ilConditionHandlerGUI($this,(int) $_GET['item_id']);
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('preconditions');
				break;

			default:
				$this->tabs_gui->setTabActive('timings');
				if(!$cmd)
				{
					$cmd = 'edit';
				}
				$this->$cmd();
				$this->tabs_gui->setTabActive('timings');
				break;
		}
		
		$tpl->show();
	}

	function getItemId()
	{
		return $this->item_id;
	}

	function cancel()
	{
		$this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', $this->parent_ref_id);
		$this->ctrl->redirectByClass('ilrepositorygui');
	}

	/**
	 * edit timings
	 *
	 * @access public
	 * @return
	 */
	public function edit()
	{
		$ilErr = $this->error;
		$ilAccess = $this->access;
		$tpl = $this->tpl;

		// #19997 - see ilObjectListGUI::insertTimingsCommand()
		if(!$ilAccess->checkAccess('write','',$this->parent_ref_id) &&
			!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$this->initFormEdit();
		$this->getValues();
				
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * init form edit
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormEdit()
	{
		$tree = $this->tree;
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	
		$title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getItemId()));
		$this->form->setTitle($title.': '.$this->lng->txt('crs_edit_timings'));
		
		$timings = new ilRadioGroupInputGUI($this->lng->txt('timings'),'timing_type');
		
		// opt deactivated
		$dea = new ilRadioOption($this->lng->txt('crs_timings_disabled'),ilObjectActivation::TIMINGS_DEACTIVATED);
		$dea->setInfo($this->lng->txt('crs_timings_disabled_info'));
		$timings->addOption($dea);
		
		// Visiblity
		$vis = new ilRadioOption($this->lng->txt('crs_timings_availability_enabled'),ilObjectActivation::TIMINGS_ACTIVATION);
		#$vis->setInfo($this->lng->txt('crs_timings_visibility'));
		
			$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_start'),'timing_start');
			$start->setRequired(true);
			$start->setShowTime(true);
			$vis->addSubItem($start);
		
			$end = new ilDateTimeInputGUI($this->lng->txt('crs_timings_end'),'timing_end');
			$end->setRequired(true);
			$end->setShowTime(true);
			$vis->addSubItem($end);
			
			$isv = new ilCheckboxInputGUI($this->lng->txt('crs_timings_visibility_short'),'visible');
			$isv->setInfo($this->lng->txt('crs_timings_visibility'));
			$isv->setValue(1);
			$vis->addSubItem($isv);

		$timings->addOption($vis);
		
		// Timings
		$tim = new ilRadioOption($this->lng->txt('crs_timings_presetting'),ilObjectActivation::TIMINGS_PRESETTING);
		$tim->setInfo($this->lng->txt('crs_item_presetting_info'));
		
			$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_sug_begin'),'sug_start');
			$start->setRequired(true);
			$tim->addSubItem($start);
			
			$end = new ilDateTimeInputGUI($this->lng->txt('crs_timings_sug_end'),'sug_end');
			$end->setRequired(true);
			$tim->addSubItem($end);
			
			if ($tree->checkForParentType($this->parent_ref_id,'crs'))
			{
				$cha = new ilCheckboxInputGUI($this->lng->txt('crs_timings_changeable'),'changeable');
				$tim->addSubItem($cha);

				$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_early_begin'),'early_start');
				$start->setRequired(true);
				$tim->addSubItem($start);

				$late = new ilDateTimeInputGUI($this->lng->txt('crs_timings_short_limit_start_end'),'late_end');
				$late->setRequired(true);
				$tim->addSubItem($late);
			}
				
			
		$timings->addOption($tim);
		
		$this->form->addItem($timings);
		
		$this->form->addCommandButton('update',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
	}
	
	/**
	 * get values
	 *
	 * @access protected
	 * @return
	 */
	protected function getValues()
	{
		$ilUser = $this->user;
		
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		$item_data = ilObjectActivation::getItem($this->getItemId());
				
		$data = array();
		
		if(!$item_data)
		{
			$data['timing_type'] = ilObjectActivation::TIMINGS_DEACTIVATED;			
		}
		else
		{		
			$data['timing_type'] = $item_data['timing_type'];
			$data['visible'] = $item_data['visible'];
			$data['changeable'] = $item_data['changeable'];
			
			$map = array(
				'timing_start' => 'timing_start'
				,'timing_end' => 'timing_end'
				,'sug_start' => 'suggestion_start'
				,'sug_end' => 'suggestion_end'
				,'early_start' => 'earliest_start'
				,'late_end' => 'latest_end'
			);
			foreach($map as $data_key => $item_key)
			{
				$data[$data_key] = null;
				
				// #14531
				if($item_data[$item_key])
				{
					$data[$data_key] = new ilDateTime($item_data[$item_key], IL_CAL_UNIX);
				}
			}			
		}
		
		$this->form->setValuesByArray($data);
	}

	/**
	 * update
	 *
	 * @access public
	 * @return
	 */
	public function update()
	{
		$ilErr = $this->error;
		$ilAccess = $this->access;
		$tpl = $this->tpl;
		$ilUser = $this->user;

		// #19997 - see ilObjectListGUI::insertTimingsCommand()
		if(!$ilAccess->checkAccess('write','',$this->parent_ref_id) &&
			!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$this->initFormEdit();
		if($this->form->checkInput())
		{
			include_once "Services/Object/classes/class.ilObjectActivation.php";
			$activation = new ilObjectActivation();
			
			$activation->setTimingType($this->form->getInput('timing_type'));
			
			$date = $this->form->getItemByPostVar('timing_start')->getDate();		
			$activation->setTimingStart($date ? $date->get(IL_CAL_UNIX) : null);
			
			$date = $this->form->getItemByPostVar('timing_end')->getDate();			
			$activation->setTimingEnd($date ? $date->get(IL_CAL_UNIX) : null);
	
			$date = $this->form->getItemByPostVar('sug_start')->getDate();
			$activation->setSuggestionStart($date ? $date->get(IL_CAL_UNIX) : null);
	
			$date = $this->form->getItemByPostVar('sug_end')->getDate();
			$activation->setSuggestionEnd($date ? $date->get(IL_CAL_UNIX) : null);
	
			$date = null;
			$item = $this->form->getItemByPostVar('early_start');				
			if($item)
			{
				$date = $item->getDate();			
			}
			$activation->setEarliestStart($date ? $date->get(IL_CAL_UNIX) : null);
	
			$date = null;
			$item = $this->form->getItemByPostVar('late_end');
			if($item)
			{
				$date = $item->getDate();									
			}
			$activation->setLatestEnd($date ? $date->get(IL_CAL_UNIX) : null);
	
			$activation->toggleVisible((bool) $this->form->getInput('visible'));
			$activation->toggleChangeable((bool) $this->form->getInput('changeable'));
			
			if(!$activation->validateActivation())
			{
				ilUtil::sendFailure($ilErr->getMessage());
		        $this->form->setValuesByPost();
				$tpl->setContent($this->form->getHTML());
				return false;
			}
			else
			{
				$activation->update($this->getItemId());
				ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
				$this->ctrl->redirect($this, "edit");
			}
		}
		else
		{
	        $this->form->setValuesByPost();
			$tpl->setContent($this->form->getHTML());
		}	
	}

	function __setTabs()
	{
		$ilCtrl = $this->ctrl;
		$ilHelp = $this->help;
		
		$this->tabs_gui->clearTargets();

		$ilHelp->setScreenIdComponent("obj");

		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->parent_ref_id);
		$back_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);						
		$this->tabs_gui->setBackTarget($this->lng->txt('btn_back'), $back_link);
		
		$this->tabs_gui->addTarget("timings",
								   $this->ctrl->getLinkTarget($this,'edit'),
								   "edit", get_class($this));
		
		$this->ctrl->setParameterByClass('ilconditionhandlergui','item_id',$this->item_id);
		$this->tabs_gui->addTarget("preconditions",
								   $this->ctrl->getLinkTargetByClass('ilConditionHandlerGUI','listConditions'),
								   "", "ilConditionHandlerGUI");
		return true;
	}
} 

?>