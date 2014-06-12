<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectListGUI.php");

/**
 * Preloader for object list GUIs
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilObject.php 46291 2013-11-19 15:09:45Z jluetzen $
 */
class ilObjectListGUIPreloader
{
	protected $context; // [int]
	protected $obj_ids; // [array]
	protected $obj_ids_by_type; // [array]
	protected $ref_ids; // [array]
	protected $ref_ids_by_type; // [array]
	protected $types; // [array]
	
	public function __construct($a_context)
	{
		$this->context = $a_context;		
	}
	
	public function addItem($a_obj_id, $a_type, $a_ref_id = null)
	{
		$this->obj_ids[] = $a_obj_id;
		$this->obj_ids_by_type[$a_type][] = $a_obj_id;
		$this->types[] = $a_type;
		
		if($a_ref_id)
		{
			$this->ref_ids[] = $a_ref_id;
			$this->ref_ids_by_type[$a_type][] = $a_ref_id;
		}		
	}
	
	public function preload()
	{						
		global $objDefinition, $tree, $ilObjDataCache, $ilUser, $rbacsystem;
								
		if(!$this->obj_ids)
		{
			return;
		}
		
		$this->obj_ids = array_unique($this->obj_ids);	
		$this->types = array_unique($this->types);		
		if($this->ref_ids)
		{
			$this->ref_ids = array_unique($this->ref_ids);
		}
						
		// type specific preloads
		include_once("./Services/AccessControl/classes/class.ilConditionHandler.php");
		foreach ($this->types as $type)
		{
			$this->obj_ids_by_type[$type] = array_unique($this->obj_ids_by_type[$type]);
			
			if(is_array($this->ref_ids_by_type[$type]))
			{
				$this->ref_ids_by_type[$type] = array_unique($this->ref_ids_by_type[$type]);
			}

			if($this->context == ilObjectListGUI::CONTEXT_REPOSITORY ||				
				$this->context == ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP ||
				$this->context == ilObjectListGUI::CONTEXT_SEARCH)
			{
				ilConditionHandler::preloadConditionsForTargetRecords($type,
					$this->obj_ids_by_type[$type]);
			}

			$class = $objDefinition->getClassName($type);
			$location = $objDefinition->getLocation($type);
			if($class && $location) // #12775
			{
				$full_class = "ilObj".$class."Access";
				include_once($location."/class.".$full_class.".php");
				if(class_exists($full_class))
				{
					call_user_func(array($full_class, "_preloadData"),
						$this->obj_ids_by_type[$type], $this->ref_ids_by_type[$type]);
				}
			}
		}
		
		if($this->ref_ids)
		{		
			$tree->preloadDeleted($this->ref_ids);
			$tree->preloadDepthParent($this->ref_ids);
			$ilObjDataCache->preloadReferenceCache($this->ref_ids, false);			
			$rbacsystem->preloadRbacPaCache($this->ref_ids, $ilUser->getId());
			
			if($ilUser->getId != ANONYMOUS_USER_ID && 
				$this->context != ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP)
			{
				ilObjUser::preloadIsDesktopItem($ilUser->getId(), $this->ref_ids);
			}
			
			include_once("./Services/Object/classes/class.ilObjectActivation.php");
			ilObjectActivation::preloadData($this->ref_ids);		
		}
						
		include_once("./Services/Object/classes/class.ilObjectListGUI.php");
		ilObjectListGUI::preloadCommonProperties($this->obj_ids, $this->context);	
		
		if($this->context == ilObjectListGUI::CONTEXT_REPOSITORY)
		{
			include_once("./Services/Rating/classes/class.ilRating.php");		
			include_once("./Services/Rating/classes/class.ilRatingGUI.php");		
			ilRating::preloadListGUIData($this->obj_ids);		
			
			include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");		
			ilAdvancedMDValues::preloadByObjIds($this->obj_ids);
		}
		
		if($this->context == ilObjectListGUI::CONTEXT_REPOSITORY ||
			$this->context == ilObjectListGUI::CONTEXT_PERSONAL_DESKTOP ||
			$this->context == ilObjectListGUI::CONTEXT_SEARCH)
		{
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			ilLPStatus::preloadListGUIData($this->obj_ids);
		}	
	}	
}

?>