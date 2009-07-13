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
* Class ilCourseItemAdministrationGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @ilCtrl_Calls ilCourseItemAdministrationGUI: ilConditionHandlerInterface
*
*
* @extends ilObjectGUI
*/

class ilCourseItemAdministrationGUI
{
	var $container_obj;
	var $tpl;
	var $ctrl;
	var $lng;

	/**
	* Constructor
	* @access public
	*/
	function ilCourseItemAdministrationGUI(&$container_obj,$a_item_id)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilErr,$ilTabs;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');
		$this->err =& $ilErr;
		$this->tabs_gui =& $ilTabs;

		$this->container_obj =& $container_obj;

		$this->item_id = $a_item_id;
		$this->ctrl->saveParameter($this,'item_id');

		$this->__initItem();
	}

	function &executeCommand()
	{
		global $ilTabs;

		$this->__setTabs();

		$cmd = $this->ctrl->getCmd();

		// Check if item id is given and valid
		if(!$this->__checkItemId())
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_item_id_given"),true);
			$this->ctrl->returnToParent($this);
		}

		switch($this->ctrl->getNextClass($this))
		{

			case 'ilconditionhandlerinterface':
				
				// preconditions for single course items
				include_once './classes/class.ilConditionHandlerInterface.php';
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
				$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
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
	}

	function getItemId()
	{
		return $this->item_id;
	}

	function cancel()
	{
		$this->ctrl->returnToParent($this);
	}

	/**
	 * edit timings
	 *
	 * @access public
	 * @return
	 */
	public function edit()
	{
		global $ilErr,$ilAccess,$ilObjDataCache,$tpl;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
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
		$item_data = $this->items_obj->getItem($this->getItemId());
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		$title = ilObject::_lookupTitle($item_data['obj_id']);
		$this->form->setTitle($title.': '.$this->lng->txt('crs_edit_timings'));
		$this->form->setTitleIcon(ilUtil::getImagePath("icon_".$item_data['type']."_s.gif"));
		
		$timings = new ilRadioGroupInputGUI($this->lng->txt('timings'),'timing_type');
		
		// opt deactivated
		$dea = new ilRadioOption($this->lng->txt('crs_timings_disabled'),IL_CRS_TIMINGS_DEACTIVATED);
		$dea->setInfo($this->lng->txt('crs_timings_disabled_info'));
		$timings->addOption($dea);
		
		// Visiblity
		$vis = new ilRadioOption($this->lng->txt('crs_timings_availability_enabled'),IL_CRS_TIMINGS_ACTIVATION);
		#$vis->setInfo($this->lng->txt('crs_timings_visibility'));
		
			$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_start'),'timing_start');
			$start->setShowTime(true);
			#$start->setMinuteStepSize(5);
			$vis->addSubItem($start);
		
			$end = new ilDateTimeInputGUI($this->lng->txt('crs_timings_end'),'timing_end');
			$end->setShowTime(true);
			#$end->setMinuteStepSize(5);
			$vis->addSubItem($end);
			
			$isv = new ilCheckboxInputGUI($this->lng->txt('crs_timings_visibility_short'),'visible');
			$isv->setInfo($this->lng->txt('crs_timings_visibility'));
			$isv->setValue(1);
			$vis->addSubItem($isv);

		$timings->addOption($vis);
		
		// Timings
		$tim = new ilRadioOption($this->lng->txt('crs_timings_presetting'),IL_CRS_TIMINGS_PRESETTING);
		$tim->setInfo($this->lng->txt('crs_item_presetting_info'));
		
			$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_sug_begin'),'sug_start');
			$tim->addSubItem($start);
			
			$end = new ilDateTimeInputGUI($this->lng->txt('crs_timings_sug_end'),'sug_end');
			$tim->addSubItem($end);
			
			$cha = new ilCheckboxInputGUI($this->lng->txt('crs_timings_changeable'),'changeable');
			$tim->addSubItem($cha);
			
				$start = new ilDateTimeInputGUI($this->lng->txt('crs_timings_early_begin'),'early_start');
				$cha->addSubItem($start);
				
				$late = new ilDateTimeInputGUI($this->lng->txt('crs_timings_short_limit_start_end'),'late_end');
				$cha->addSubItem($late);
				
			
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
		global $ilUser;
		
		$item_data = $this->items_obj->getItem($this->getItemId());
		
		$data['timing_type'] = $item_data['timing_type'];
		$data['visible'] = $item_data['visible'];
		$data['changeable'] = $item_data['changeable'];
		
		$start = new ilDateTime($item_data['timing_start'],IL_CAL_UNIX);
		$data['timing_start']['date'] = $start->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		$data['timing_start']['time'] = $start->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());
		
		$end = new ilDateTime($item_data['timing_end'],IL_CAL_UNIX);
		$data['timing_end']['date'] = $end->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		$data['timing_end']['time'] = $end->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());
		
		$start = new ilDate(date('Y-m-d',$item_data['suggestion_start']),IL_CAL_DATE);
		$data['sug_start']['date'] = $start->get(IL_CAL_FKT_DATE,'Y-m-d','UTC');
		
		$end = new ilDate(date('Y-m-d',$item_data['suggestion_end']),IL_CAL_DATE);
		$data['sug_end']['date'] = $end->get(IL_CAL_FKT_DATE,'Y-m-d','UTC');
		
		$start = new ilDate(date('Y-m-d',$item_data['earliest_start']),IL_CAL_DATE);
		$data['early_start']['date'] = $start->get(IL_CAL_FKT_DATE,'Y-m-d','UTC');
		
		$end = new ilDate(date('Y-m-d',$item_data['latest_end']),IL_CAL_DATE);
		$data['late_end']['date'] = $end->get(IL_CAL_FKT_DATE,'Y-m-d','UTC');
		
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
		global $ilErr,$ilAccess,$ilObjDataCache,$tpl,$ilUser;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		$this->initFormEdit();
		if($this->form->checkInput())
		{
			$this->items_obj->setTimingType($this->form->getInput('timing_type'));
			
			$date = $this->form->getInput('timing_start');
			$date = new ilDateTime($date['date'].' '.$date['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$this->items_obj->setTimingStart($date->get(IL_CAL_UNIX));
			
			$date = $this->form->getInput('timing_end');
			$date = new ilDateTime($date['date'].' '.$date['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$this->items_obj->setTimingEnd($date->get(IL_CAL_UNIX));
	
			$date = $this->form->getInput('sug_start');
			$date = new ilDate($date['date'],IL_CAL_DATE);
			$this->items_obj->setSuggestionStart($date->get(IL_CAL_UNIX));
	
			$date = $this->form->getInput('sug_end');
			$date = new ilDate($date['date'],IL_CAL_DATE);
			$this->items_obj->setSuggestionEnd($date->get(IL_CAL_UNIX));
	
			$date = $this->form->getInput('early_start');
			$date = new ilDate($date['date'],IL_CAL_DATE);
			$this->items_obj->setEarliestStart($date->get(IL_CAL_UNIX));
	
			$date = $this->form->getInput('late_end');
			$date = new ilDate($date['date'],IL_CAL_DATE);
			$this->items_obj->setLatestEnd($date->get(IL_CAL_UNIX));
	
			$this->items_obj->toggleVisible((bool) $this->form->getInput('visible'));
			$this->items_obj->toggleChangeable((bool) $this->form->getInput('changeable'));
			
			if(!$this->items_obj->validateActivation())
			{
				ilUtil::sendFailure($ilErr->getMessage());
		        $this->form->setValuesByPost();
				$tpl->setContent($this->form->getHTML());
				return false;
			}
			else
			{
				$this->items_obj->update($this->getItemId());
				ilUtil::sendSuccess($this->lng->txt('settings_saved'));
				$this->edit();
			}
		}
		else
		{
	        $this->form->setValuesByPost();
			$tpl->setContent($this->form->getHTML());
		}
	
	}



	function moveUp()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->moveUp((int) $this->getItemId());
		ilUtil::sendSuccess($this->lng->txt("crs_moved_item"),true);

		$this->ctrl->returnToParent($this);
		return true;
	}

	function moveDown()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->moveDown((int) $this->getItemId());
		ilUtil::sendSuccess($this->lng->txt("crs_moved_item"),true);

		$this->ctrl->returnToParent($this);
		return true;
	}


	function __checkItemId()
	{
		global $tree;

		if(!$this->getItemId())
		{
			return false;
		}
		// Item has to be within course
		if(!$tree->checkForParentType($this->getItemId(),'crs'))
		{
			return false;
		}
		return true;
	}

	function __initItem()
	{
		global $ilObjDataCache,$tree;

		include_once "./Modules/Course/classes/class.ilCourseItems.php";
		
		if(!is_object($this->items_obj))
		{
			$parent_ref = $tree->getParentId($this->item_id);
			$cont_obj = ilObjectFactory::getInstanceByRefId($parent_ref,false);
			
			if($cont_obj->getType() == 'crs')
			{
				$this->items_obj =& new ilCourseItems($cont_obj,$cont_obj->getRefId());
			}
			else
			{
				// lookup crs_obj
				$crs_ref_id = $tree->checkForParentType($cont_obj->getRefId(),'crs');
				$crs_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

				$this->items_obj =& new ilCourseItems($crs_obj,$cont_obj->getRefId());
			}
			/*
			if($ilObjDataCache->lookupType($this->container_obj->getId()) == 'crs')
			{
				$this->items_obj =& new ilCourseItems($this->container_obj,$this->container_obj->getRefId());
			}
			else
			{
				// lookup crs_obj
				$crs_ref_id = $tree->checkForParentType($this->container_obj->getRefId(),'crs');
				$crs_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

				$this->items_obj =& new ilCourseItems($crs_obj,$this->container_obj->getRefId());
			}
			*/
		}
		return true;
	}

	function __setTabs()
	{
		global $rbacsystem,$ilUser;
		
		$this->tabs_gui->clearTargets();

		$this->tabs_gui->setBackTarget($this->lng->txt('back_to_'.$this->container_obj->getType().'_content'),
									   'repository.php?ref_id='.$this->container_obj->getRefId());
		$this->tabs_gui->addTarget("timings",
								   $this->ctrl->getLinkTarget($this,'edit'),
								   "edit", get_class($this));
		$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
		$this->tabs_gui->addTarget("preconditions",
								   $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
								   "", "ilConditionHandlerInterface");
		return true;
	}

} // END class.ilCourseItemAdminsitration
?>