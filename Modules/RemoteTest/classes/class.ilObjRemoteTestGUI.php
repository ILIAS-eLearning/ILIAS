<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjRemoteTestGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteTestGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteTest
*/

class ilObjRemoteTestGUI extends ilRemoteObjectBaseGUI
{
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->lng->loadLanguageModule('rtst');
		$this->lng->loadLanguageModule('assessment');
	}
	
	function getType()
	{
		return 'rtst';
	}

	protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
	{		
		$a_info->addProperty($this->lng->txt('grp_visibility'),$this->availabilityToString());		
	}
	
	protected function availabilityToString()
	{
	 	switch($this->object->getAvailabilityType())
	 	{
	 		case ilObjRemoteTest::ACTIVATION_OFFLINE:
	 			return $this->lng->txt('offline');
	 		
	 		case ilObjRemoteTest::ACTIVATION_UNLIMITED:
	 			return $this->lng->txt('grp_unlimited');
	 		
	 		case ilObjRemoteTest::ACTIVATION_LIMITED:
	 			return ilDatePresentation::formatPeriod(
	 				new ilDateTime($this->object->getStartingTime(),IL_CAL_UNIX),
	 				new ilDateTime($this->object->getEndingTime(),IL_CAL_UNIX));
	 	}
	 	return '';
	}
	
	protected function addCustomEditForm(ilPropertyFormGUI $a_form)
	{				
		$radio_grp = new ilRadioGroupInputGUI($this->lng->txt('grp_visibility'),'activation_type');
		$radio_grp->setValue($this->object->getAvailabilityType());
		$radio_grp->setDisabled(true);

		$radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_unvisible'),ilObjRemoteTest::ACTIVATION_OFFLINE);
		$radio_grp->addOption($radio_opt);

		$radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_limitless'),ilObjRemoteTest::ACTIVATION_UNLIMITED);
		$radio_grp->addOption($radio_opt);	

		// :TODO: not supported in ECS yet
		$radio_opt = new ilRadioOption($this->lng->txt('grp_visibility_until'),ilObjRemoteTest::ACTIVATION_LIMITED);
		
		$start = new ilDateTimeInputGUI($this->lng->txt('grp_start'),'start');
		$start->setDate(new ilDateTime(time(),IL_CAL_UNIX));
		$start->setDisabled(true);
		$start->setShowTime(true);
		$radio_opt->addSubItem($start);
		$end = new ilDateTimeInputGUI($this->lng->txt('grp_end'),'end');
		$end->setDate(new ilDateTime(time(),IL_CAL_UNIX));
		$end->setDisabled(true);
		$end->setShowTime(true);
		$radio_opt->addSubItem($end);
		
		$radio_grp->addOption($radio_opt);
		$a_form->addItem($radio_grp);	
	}	
}

?>