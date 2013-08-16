<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Tracking/classes/collection/class.ilLPCollection.php";

/**
* LP collection of SCOs
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
class ilLPCollectionOfSCOs extends ilLPCollection
{	
	protected static $possible_items = array(); 
	
	// see ilSCORMCertificateAdapter
	public function getPossibleItems()
	{
		if(!isset(self::$possible_items[$this->obj_id]))
		{
			include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';

			$items = array();

			switch(ilObjSAHSLearningModule::_lookupSubType($this->obj_id))
			{
				case 'hacp':
				case 'aicc':
					include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';
					foreach(ilObjAICCLearningModule::_getTrackingItems($this->obj_id) as $item)
					{
						$items[$item['obj_id']]['title'] = $item['title'];
					}
					break;

				case 'scorm':
					include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
					include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
					foreach(ilObjSCORMLearningModule::_getTrackingItems($this->obj_id) as $item)
					{
						$items[$item->getId()]['title'] = $item->getTitle();
					}
					break;

				case 'scorm2004':
					include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
					foreach(ilObjSCORM2004LearningModule::_getTrackingItems($this->obj_id) as $item)
					{
						$items[$item['id']]['title'] = $item['title'];
					}
					break;
			}

			self::$possible_items[$this->obj_id] = $items;
		}
		
		return self::$possible_items[$this->obj_id];
	}	

	
	//
	// TABLE GUI
	// 
	
	public function getTableGUIData($a_parent_ref_id)
	{	
		$data = array();
		
		foreach($this->getPossibleItems() as $sco_id => $item)
		{
			$tmp = array();
			$tmp['id'] = $sco_id;
			$tmp['ref_id'] = 0;
			$tmp['title'] = $item['title'];
			$tmp["status"] = $this->isAssignedEntry($sco_id);

			$data[] = $tmp;
		}
	
		return $data;
	}
	
				
	//
	// HELPER
	//	
		
	// see ilSCORMCertificateAdapter
	public function getScoresForUserAndCP_Node_Id($item_id, $user_id)
	{
		include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
		switch(ilObjSAHSLearningModule::_lookupSubType($this->obj_id))
		{
			case 'hacp':
			case 'aicc':
				include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';
				return ilObjAICCLearningModule::_getScoresForUser($item_id, $user_id);

			case 'scorm':
				include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
				//include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
				return ilObjSCORMLearningModule::_getScoresForUser($item_id, $user_id);

			case 'scorm2004':
				include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
				return ilObjSCORM2004LearningModule::_getScores2004ForUser($item_id, $user_id);
		}
		
		return array("raw" => null, "max" => null, "scaled" => null);
	}
}

?>