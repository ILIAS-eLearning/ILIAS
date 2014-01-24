<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjRemoteCourseGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteCourseGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteCourse 
*/

class ilObjRemoteCourseGUI extends ilRemoteObjectBaseGUI
{
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->lng->loadLanguageModule('rcrs');
		$this->lng->loadLanguageModule('crs');
	}
	
	function getType()
	{
		return 'rcrs';
	}

	protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
	{		
		$a_info->addProperty($this->lng->txt('crs_visibility'),$this->availabilityToString());		
	}
	
	protected function availabilityToString()
	{
	 	switch($this->object->getAvailabilityType())
	 	{
	 		case ilObjRemoteCourse::ACTIVATION_OFFLINE:
	 			return $this->lng->txt('offline');
	 		
	 		case ilObjRemoteCourse::ACTIVATION_UNLIMITED:
	 			return $this->lng->txt('crs_unlimited');
	 		
	 		case ilObjRemoteCourse::ACTIVATION_LIMITED:
	 			return ilDatePresentation::formatPeriod(
	 				new ilDateTime($this->object->getStartingTime(),IL_CAL_UNIX),
	 				new ilDateTime($this->object->getEndingTime(),IL_CAL_UNIX));
	 	}
	 	return '';
	}
	
	protected function addCustomEditForm(ilPropertyFormGUI $a_form)
	{				
		$radio_grp = new ilRadioGroupInputGUI($this->lng->txt('crs_visibility'),'activation_type');
		$radio_grp->setValue($this->object->getAvailabilityType());
		$radio_grp->setDisabled(true);

		$radio_opt = new ilRadioOption($this->lng->txt('crs_visibility_unvisible'),ilObjRemoteCourse::ACTIVATION_OFFLINE);
		$radio_grp->addOption($radio_opt);

		$radio_opt = new ilRadioOption($this->lng->txt('crs_visibility_limitless'),ilObjRemoteCourse::ACTIVATION_UNLIMITED);
		$radio_grp->addOption($radio_opt);	

		// :TODO: not supported in ECS yet
		$radio_opt = new ilRadioOption($this->lng->txt('crs_visibility_until'),ilObjRemoteCourse::ACTIVATION_LIMITED);
		
		$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'start');
		$start->setDate(new ilDateTime(time(),IL_CAL_UNIX));
		$start->setDisabled(true);
		$start->setShowTime(true);
		$radio_opt->addSubItem($start);
		$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'end');
		$end->setDate(new ilDateTime(time(),IL_CAL_UNIX));
		$end->setDisabled(true);
		$end->setShowTime(true);
		$radio_opt->addSubItem($end);
		
		$radio_grp->addOption($radio_opt);
		$a_form->addItem($radio_grp);	
	}	
}

?>