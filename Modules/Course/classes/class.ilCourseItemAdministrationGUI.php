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
			ilUtil::sendInfo($this->lng->txt("crs_no_item_id_given"),true);
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

	function edit()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_edit_item.html",'Modules/Course');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$item_data = $this->items_obj->getItem($this->getItemId());
		$title = $ilObjDataCache->lookupTitle($item_data['obj_id']);

		if(isset($_POST['cmd']))
		{
			$timing_type = $_POST['timing_type'];
			$visible = $_POST['visible'];
			$changeable = $_POST['changeable'];
			$timing_start = $this->__toUnix($_POST['timing_start'],$_POST['timing_start_time']);
			$timing_end = $this->__toUnix($_POST['timing_end'],$_POST['timing_end_time']);
			$suggestion_start = $this->__toUnix($_POST['sug_start']);
			$suggestion_end = $this->__toUnix($_POST['sug_end']);
			$earliest_start = $this->__toUnix($_POST['early_start']);
			$latest_end = $this->__toUnix($_POST['late_end']);
		}
		else
		{
			$timing_type = $item_data['timing_type'];
			$visible = $item_data['visible'];
			$changeable = $item_data['changeable'];
			$timing_start = $item_data['timing_start'];
			$timing_end = $item_data['timing_end'];
			$suggestion_start = $item_data['suggestion_start'];
			$suggestion_end = $item_data['suggestion_end'];
			$earliest_start = $item_data['earliest_start'];
			$latest_end = $item_data['latest_end'];
		}

		// SET TEXT VARIABLES
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_".$ilObjDataCache->lookupType($item_data['obj_id'])));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_".$ilObjDataCache->lookupType($item_data['obj_id']).".gif"));
		$this->tpl->setVariable("TITLE",$ilObjDataCache->lookupTitle($item_data['obj_id']));
		$this->tpl->setVariable("EDIT_TIMINGS",$this->lng->txt('crs_edit_timings'));
		$this->tpl->setVariable("TXT_TIMINGS",$this->lng->txt('crs_timings_disabled'));
		$this->tpl->setVariable("INFO_DEACTIVATED",$this->lng->txt('crs_timings_disabled_info'));
		$this->tpl->setVariable("TXT_BEGIN",$this->lng->txt('crs_timings_start'));
		$this->tpl->setVariable("TXT_END",$this->lng->txt('crs_timings_END'));
		$this->tpl->setVariable("TXT_ACTIVATION_ENABLED",$this->lng->txt('crs_timings_availability_enabled'));
		$this->tpl->setVariable("TXT_PRESETTING",$this->lng->txt('crs_timings_presetting_tbl'));
		$this->tpl->setVariable("TXT_SUG_BEGIN",$this->lng->txt('crs_timings_sug_begin'));
		$this->tpl->setVariable("TXT_SUG_END",$this->lng->txt('crs_suggestion_end'));
		$this->tpl->setVariable("TXT_EARLY_BEGIN",$this->lng->txt('crs_timings_early_begin'));
		$this->tpl->setVariable("TXT_LATE_END",$this->lng->txt('crs_timings_late_end'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('time'));



		// Disabled
		$this->tpl->setVariable("TXT_AVAILABILITY",$this->lng->txt('crs_timings_availability_tbl'));
		$this->tpl->setVariable("TXT_TIMINGS_DEACTIVATED",$this->lng->txt('crs_timings_deactivated'));
		$this->tpl->setVariable("RADIO_DEACTIVATE",ilUtil::formRadioButton($timing_type == IL_CRS_TIMINGS_DEACTIVATED,
																		   'timing_type',IL_CRS_TIMINGS_DEACTIVATED));

		// Activation
		$this->tpl->setVariable("RADIO_ACTIVATION",ilUtil::formRadioButton($timing_type == IL_CRS_TIMINGS_ACTIVATION,
																		   'timing_type',IL_CRS_TIMINGS_ACTIVATION));
		$this->tpl->setVariable("TXT_TIMINGS_ACTIVATION",$this->lng->txt('crs_timings_availability'));
		$this->tpl->setVariable("INFO_AVAILABILITY",$this->lng->txt('crs_item_availability_info'));

		$this->tpl->setVariable("CHECK_VISIBILITY",ilUtil::formCheckbox($visible,
																		'visible',1));
		$this->tpl->setVariable("TXT_VISIBILITY",$this->lng->txt('crs_timings_visibility'));

		// Timings
		$this->tpl->setVariable("RADIO_TIMINGS",ilUtil::formRadioButton($timing_type == IL_CRS_TIMINGS_PRESETTING,
																		'timing_type',IL_CRS_TIMINGS_PRESETTING));
		$this->tpl->setVariable("TXT_TIMINGS_PRESETTING",$this->lng->txt('crs_timings_presetting'));
		$this->tpl->setVariable("INFO_PRESETTING",$this->lng->txt('crs_item_presetting_info'));

		$this->tpl->setVariable("CHECK_CHANGE",ilUtil::formCheckbox($changeable,'changeable',1));
		$this->tpl->setVariable("TXT_CHANGE",$this->lng->txt('crs_timings_changeable'));

		// Start
		$this->tpl->setVariable("TXT_START",$this->lng->txt('crs_timings_start'));

		$date = $this->__prepareDateSelect($timing_start);
		$this->tpl->setVariable("START_DATE_SELECT",
								ilUtil::makeDateSelect('timing_start',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($timing_start);
		$this->tpl->setVariable("START_TIME_SELECT",
								ilUtil::makeTimeSelect('timing_start_time',true,$date['h'],$date['m'],0,false));

		// End
		$date = $this->__prepareDateSelect($timing_end);
		$this->tpl->setVariable("END_DATE_SELECT",
								ilUtil::makeDateSelect('timing_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($timing_end);
		$this->tpl->setVariable("END_TIME_SELECT",
								ilUtil::makeTimeSelect('timing_end_time',true,$date['h'],$date['m'],0,false));

		// End
		$this->tpl->setVariable("TXT_END",$this->lng->txt('crs_timings_end'));

		// Suggestion Start
		$date = $this->__prepareDateSelect($suggestion_start);
		$this->tpl->setVariable("SUG_START_SELECT",
								ilUtil::makeDateSelect('sug_start',$date['y'],$date['m'],$date['d'],date('Y',time())));
		
		$date = $this->__prepareDateSelect($suggestion_end);
		$this->tpl->setVariable("SUG_END_SELECT",
								ilUtil::makeDateSelect('sug_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		// Earliest Latest
		$date = $this->__prepareDateSelect($earliest_start);
		$this->tpl->setVariable("EARLY_SELECT",
								ilUtil::makeDateSelect('early_start',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareDateSelect($latest_end);
		$this->tpl->setVariable("LATE_SELECT",
								ilUtil::makeDateSelect('late_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt("save"));
	}

	function update()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->setTimingType($_POST['timing_type']);
		$this->items_obj->setTimingStart($this->__toUnix($_POST['timing_start'],$_POST['timing_start_time']));
		$this->items_obj->setTimingEnd($this->__toUnix($_POST['timing_end'],$_POST['timing_end_time']));
		$this->items_obj->setSuggestionStart($this->__toUnix($_POST["sug_start"]));
		$this->items_obj->setSuggestionEnd($this->__toUnix($_POST["sug_end"],array('h' => 23,'m' => 55)));
		$this->items_obj->setEarliestStart($this->__toUnix($_POST['early_start']));
		$this->items_obj->setLatestEnd($this->__toUnix($_POST['late_end'],array('h' => 23,'m' => 55)));
		$this->items_obj->toggleVisible($_POST['visible']);
		$this->items_obj->toggleChangeable($_POST['changeable']);

		if(!$this->items_obj->validateActivation())
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->edit();

			return true;
		}
		$this->items_obj->update($this->getItemId());
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->edit();
		#$this->ctrl->returnToParent($this);

		return true;
	}

	function moveUp()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->moveUp((int) $this->getItemId());
		ilUtil::sendInfo($this->lng->txt("crs_moved_item"),true);

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
		ilUtil::sendInfo($this->lng->txt("crs_moved_item"),true);

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

	function __toUnix($date,$time = array())
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}

	function __prepareDateSelect($a_unix_time)
	{
		return array('y' => date('Y',$a_unix_time),
					 'm' => date('m',$a_unix_time),
					 'd' => date('d',$a_unix_time));
	}

	function __prepareTimeSelect($a_unix_time)
	{
		return array('h' => date('G',$a_unix_time),
					 'm' => date('i',$a_unix_time),
					 's' => date('s',$a_unix_time));
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
