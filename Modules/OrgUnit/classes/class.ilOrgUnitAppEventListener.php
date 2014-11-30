<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitAppEventListener
 *
  * @author  Nils Haagen <nhaagen@concepts-and-training.de>
 *
 */

class ilOrgUnitAppEventListener {
	
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		//self::initEventHandler();
		switch ($a_component) {
			case 'Services/User':
				switch ($a_event){
					case 'afterUpdate': 
						self::onServiceUserAfterUpdate($a_parameter);
						break;

				}
				break;

			case 'Modules/OrgUnit':
				switch ($a_event){
					case 'delete':  
					case 'toTrash':  
						self::onModulesOrgUnitDelete($a_parameter);
						break;
					default: 
						//print '<br>';
						//print $a_event;
						//die();

				}
				break;
			case 'Services/Object':
				//print '<br>';
				//print $a_event;
				/*

				This is called WAY TOO OFTEN
				i.e.: for every object!
				There is a desperate need for a 
				dedicated deletion-event of org-units!
				*/
				switch ($a_event){
					//case 'delete':  //delete is an update
					case 'toTrash':  
						self::onModulesOrgUnitDelete($a_parameter);
						break;
					default: 
						break;

				}
				break;


		}
		
		//die('ilOrgUnitAppEventListener');
		
	}

	private function onServiceUserAfterUpdate($a_parameter){
		require_once('./Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php');
		// do not fail, if there is no org.unit for the user...
		ilPersonalOrgUnits::updateOrgUnitTitleOf($a_parameter['user_obj'], True);
	}

	private function onModulesOrgUnitDelete($a_parameter){
		require_once('./Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php');
		// This won't work since user might have personal org-units in any domain
		/*if (ilPersonalOrgUnits::getInstance()->getOrgUnitIdOf() !== null) {
			ilPersonalOrgUnits::purgeOrgUnitLookupOf($a_parameter['obj_id']);
		}*/
		ilPersonalOrgUnits::purgeOrgUnitLookupOf($a_parameter['obj_id']);
	}
}