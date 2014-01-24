<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Tracking/classes/collection/class.ilLPCollection.php";

/**
* LP collection of learning module chapters
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPCollections.php 40326 2013-03-05 11:39:24Z jluetzen $
*
* @ingroup ServicesTracking
*/
class ilLPCollectionOfLMChapters extends ilLPCollection
{	
	protected static $possible_items = array(); 
	
	public function getPossibleItems($a_ref_id)
	{				
		if(!isset(self::$possible_items[$a_ref_id]))
		{
			$obj_id = ilObject::_lookupObjectId($a_ref_id);

			$items = array();

			// only top-level chapters

			include_once "Services/MetaData/classes/class.ilMDEducational.php";		
			$tree = new ilTree($obj_id);
			$tree->setTableNames('lm_tree','lm_data');
			$tree->setTreeTablePK("lm_id");
			foreach ($tree->getChilds($tree->readRootId()) as $child)
			{		
				if($child["type"] == "st")
				{											
					$child["tlt"] = ilMDEducational::_getTypicalLearningTimeSeconds($obj_id, $child["obj_id"]);				
					$items[$child["obj_id"]] = $child;
				}
			}
			
			self::$possible_items[$a_ref_id] = $items;
		}
		
		return self::$possible_items[$a_ref_id];
	}
	
	
	//
	// TABLE GUI
	// 
	
	public function getTableGUIData($a_parent_ref_id)
	{					
		$data = array();	
		
		$parent_type = ilObject::_lookupType($a_parent_ref_id, true);		
		include_once './Services/Link/classes/class.ilLink.php';
		
		foreach ($this->getPossibleItems($a_parent_ref_id) as $item)
		{					
			$tmp = array();
			$tmp['id'] = $item['obj_id'];
			$tmp['ref_id'] = 0;
			$tmp['title'] = $item['title'];
			$tmp['type'] = $item['type'];
			$tmp['status'] = $this->isAssignedEntry($item['obj_id']);
			
			// #12158
			$tmp['url'] = ilLink::_getLink($a_parent_ref_id, $parent_type, null, "_".$tmp['id']);		
			
			if($this->mode == ilLPObjSettings::LP_MODE_COLLECTION_TLT)
			{
				$tmp['tlt'] = $item['tlt'];
			}
			
			$data[] = $tmp;			
		}		
		
		return $data;
	}
}

?>