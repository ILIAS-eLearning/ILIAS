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
* @package ilias-core
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


		$this->__setSubTabs();

		$cmd = $this->ctrl->getCmd();

		// Check if item id is given and valid
		if(!$this->__checkItemId())
		{
			sendInfo($this->lng->txt("crs_no_item_id_given"),true);
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
				$this->tabs_gui->setSubTabActive('preconditions');
				break;

			default:
				$this->tabs_gui->setSubTabActive('activation');
				if(!$cmd)
				{
					$cmd = 'edit';
				}
				$this->$cmd();
				$this->tabs_gui->setSubTabActive('activation');
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

		if(!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_edit_item.html","course");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$item_data = $this->items_obj->getItem($this->getItemId());
		$title = $ilObjDataCache->lookupTitle($item_data['obj_id']);

		if(isset($_POST['cmd']))
		{
			$timing_type = $_POST['timing_type'];
			$visible = $_POST['visible'];
			$changeable = $_POST['changeable'];
			$timing_start = $this->toUnix($_POST['crs']['timing_start']);
			$timing_end = $this->toUnix($_POST['crs']['timing_end']);
			$suggestion_start = $this->toUnix($_POST['crs']['suggestion_start']);
			$suggestion_end = $this->toUnix($_POST['crs']['suggestion_end']);
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
		}

		// SET TEXT VARIABLES
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_".$ilObjDataCache->lookupType($item_data['obj_id'])));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_".$ilObjDataCache->lookupType($item_data['obj_id']).".gif"));

		$title .= (" (".$this->lng->txt('crs_edit_timings').')');
		$this->tpl->setVariable("TITLE",$title);

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
		$this->tpl->setVariable("SELECT_ACTIVATION_START_DAY",$this->getDateSelect("day","crs[timing_start][day]",
																					 date("d",$timing_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_MONTH",$this->getDateSelect("month","crs[timing_start][month]",
																					   date("m",$timing_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_YEAR",$this->getDateSelect("year","crs[timing_start][year]",
																					  date("Y",$timing_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_HOUR",$this->getDateSelect("hour","crs[timing_start][hour]",
																					  date("G",$timing_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_MINUTE",$this->getDateSelect("minute","crs[timing_start][minute]",
																					  date("i",$timing_start)));
		
		// End
		$this->tpl->setVariable("TXT_END",$this->lng->txt('crs_timings_end'));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_DAY",$this->getDateSelect("day","crs[timing_end][day]",
																				   date("d",$timing_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MONTH",$this->getDateSelect("month","crs[timing_end][month]",
																					 date("m",$timing_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_YEAR",$this->getDateSelect("year","crs[timing_end][year]",
																					date("Y",$timing_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_HOUR",$this->getDateSelect("hour","crs[timing_end][hour]",
																					  date("G",$timing_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MINUTE",$this->getDateSelect("minute","crs[timing_end][minute]",
																					  date("i",$timing_end)));

		// Suggestion Start
		$this->tpl->setVariable("TXT_SUG_START",$this->lng->txt('crs_suggestion_start'));
		$this->tpl->setVariable("SELECT_SUGGESTION_START_DAY",$this->getDateSelect("day","crs[suggestion_start][day]",
																					 date("d",$suggestion_start)));
		$this->tpl->setVariable("SELECT_SUGGESTION_START_MONTH",$this->getDateSelect("month","crs[suggestion_start][month]",
																					   date("m",$suggestion_start)));
		$this->tpl->setVariable("SELECT_SUGGESTION_START_YEAR",$this->getDateSelect("year","crs[suggestion_start][year]",
																					  date("Y",$suggestion_start)));
		$this->tpl->setVariable("SELECT_SUGGESTION_START_HOUR",$this->getDateSelect("hour","crs[suggestion_start][hour]",
																					  date("G",$suggestion_start)));
		$this->tpl->setVariable("SELECT_SUGGESTION_START_MINUTE",$this->getDateSelect("minute","crs[suggestion_start][minute]",
																					  date("i",$suggestion_start)));
		
		// Suggestion End
		$this->tpl->setVariable("TXT_SUG_END",$this->lng->txt('crs_suggestion_end'));
		$this->tpl->setVariable("SELECT_SUGGESTION_END_DAY",$this->getDateSelect("day","crs[suggestion_end][day]",
																				   date("d",$suggestion_end)));
		$this->tpl->setVariable("SELECT_SUGGESTION_END_MONTH",$this->getDateSelect("month","crs[suggestion_end][month]",
																					 date("m",$suggestion_end)));
		$this->tpl->setVariable("SELECT_SUGGESTION_END_YEAR",$this->getDateSelect("year","crs[suggestion_end][year]",
																					date("Y",$suggestion_end)));
		$this->tpl->setVariable("SELECT_SUGGESTION_END_HOUR",$this->getDateSelect("hour","crs[suggestion_end][hour]",
																					  date("G",$suggestion_end)));
		$this->tpl->setVariable("SELECT_SUGGESTION_END_MINUTE",$this->getDateSelect("minute","crs[suggestion_end][minute]",
																					date("i",$suggestion_end)));
		
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt("save"));
	}

	function update()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->setTimingType($_POST['timing_type']);
		$this->items_obj->setTimingStart($this->toUnix($_POST["crs"]["timing_start"]));
		$this->items_obj->setTimingEnd($this->toUnix($_POST["crs"]["timing_end"]));
		$this->items_obj->setSuggestionStart($this->toUnix($_POST["crs"]["suggestion_start"]));
		$this->items_obj->setSuggestionEnd($this->toUnix($_POST["crs"]["suggestion_end"]));
		$this->items_obj->toggleVisible($_POST['visible']);
		$this->items_obj->toggleChangeable($_POST['changeable']);

		if(!$this->items_obj->validateActivation())
		{
			sendInfo($ilErr->getMessage());
			$this->edit();

			return true;
		}
		$this->items_obj->update($this->getItemId());
		sendInfo($this->lng->txt('settings_saved'),true);
		#$this->edit();
		$this->ctrl->returnToParent($this);

		return true;
	}

	function moveUp()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->moveUp((int) $this->getItemId());
		sendInfo($this->lng->txt("crs_moved_item"),true);

		$this->ctrl->returnToParent($this);
		return true;
	}

	function moveDown()
	{
		global $ilErr,$ilAccess,$ilObjDataCache;

		if(!$ilAccess->checkAccess('write','',$this->getItemId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->items_obj->moveDown((int) $this->getItemId());
		sendInfo($this->lng->txt("crs_moved_item"),true);

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

		include_once "./course/classes/class.ilCourseItems.php";
		
		if(!is_object($this->items_obj))
		{
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
		}
		return true;
	}

	function toUnix($a_time_arr)
	{
		return mktime($a_time_arr["hour"],
					  $a_time_arr["minute"],
					  $a_time_arr["second"],
					  $a_time_arr["month"],
					  $a_time_arr["day"],
					  $a_time_arr["year"]);
	}

	function getDateSelect($a_type,$a_varname,$a_selected)
	{
		switch($a_type)
		{
			case "minute":
				for($i=0;$i<=60;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "hour":
				for($i=0;$i<24;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "day":
				for($i=1;$i<32;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);
			
			case "month":
				for($i=1;$i<13;$i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
		}
	}

	function __setSubTabs()
	{
		global $rbacsystem,$ilUser;
		
		$this->tabs_gui->clearSubTabs();
		$this->tabs_gui->addSubTabTarget("activation",
										 $this->ctrl->getLinkTarget($this,'edit'),
										 "edit", get_class($this));
		$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
		$this->tabs_gui->addSubTabTarget("preconditions",
										 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
										 "", "ilConditionHandlerInterface");
		return true;
	}

} // END class.ilCourseItemAdminsitration
?>
