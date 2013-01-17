<?php

include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseCreationHandler
{
	private $server = null;
	private $mapping = null;
	
	private $mid;
	

	/**
	 * @maybe 
	 * Constructor
	 */
	public function __construct(ilECSSetting $server)
	{
		$this->server = $server;
		$this->mapping = ilECSNodeMappingSettings::getInstance();
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		$this->mid = ilECSParticipantSettings::loookupCmsMid($this->getServer()->getServerId());
	}
	
	
	/**
	 * Get server settings
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->server;
	}
	
	/**
	 * Get mapping settings
	 * @return ilECSNodeMappingSettings
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
	
	/**
	 * Get mid of course event
	 * @return type
	 */
	public function getMid()
	{
		return $this->mid;
	}
	
	/**
	 * Handle sync request
	 * @param int ecs content id
	 * @param type $course
	 */
	public function handle($a_content_id,$course)
	{
		if($this->getMapping()->isAttributeMappingEnabled())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Handling advanced attribute mapping');
			// Do advanced attribute mapping
			return true;
		}
		
		if($this->getMapping()->isAllInOneCategoryEnabled())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Handling course all in one category setting');
			return $this->doSync($a_content_id, $course,$this->getMapping()->getAllInOneCategory());
		}

		$parent_obj_id = $this->syncParentContainer($a_content_id,$course);
		if($parent_obj_id)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Using already mapped category: '. ilObject::_lookupTitle($parent_obj_id));
			return $this->doSync($a_content_id,$course,$parent_obj_id);
		}
		$GLOBALS['ilLog']->write(__METHOD__.': Using course default category');
		return $this->doSync($a_content_id,$course,$this->getMapping()->getDefaultCourseCategory());
	}
	
	/**
	 * Sync parent container
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function syncParentContainer($a_content_id, $course)
	{
		if(!is_array($course->allocation))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': No allocation in course defined.');
			return 0;
		}
		if(!$course->allocation[0]->parentID)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': No allocation parent in course defined.');
			return 0;
		}
		$parent_id = $course->allocation[0]->parentID;
		
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		$parent_tid = ilECSCmsData::lookupFirstTreeOfNode($this->getServer()->getServerId(), $this->getMid(), $parent_id);
		return $this->syncNodetoTop($parent_tid, $parent_id);
	}
	
	/**
	 * Sync node to top
	 * @param type $tree_id
	 * @param type $parent_id
	 * @return int obj_id of container
	 */
	protected function syncNodeToTop($tree_id, $cms_id)
	{
		$obj_id = $this->getImportId($cms_id);
		if($obj_id)
		{
			// node already imported
			return $obj_id;
		}

		$tobj_id = ilECSCmsData::lookupObjId(
				$this->getServer()->getServerId(), 
				$this->getMid(), 
				$tree_id, 
				$cms_id);
		
		// node is not imported
		$GLOBALS['ilLog']->write(__METHOD__.': ecs node with id '. $cms_id. ' is not imported!');
		
		// check for mapping: if mapping is available create category
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';
		$ass = new ilECSNodeMappingAssignment(
				$this->getServer()->getServerId(),
				$this->getMid(),
				$tree_id,
				$tobj_id);
		
		if($ass->isMapped())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': node is mapped');
			return $this->syncCategory($tobj_id,$ass->getRefId());
		}
		
		// Start recursion to top
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		$tree = new ilECSCmsTree($tree_id);
		$parent_tobj_id = $tree->getParentId($tobj_id);
		if($parent_tobj_id)
		{
			$cms_ids = ilECSCmsData::lookupCmsIds(array($parent_tobj_id));
			$obj_id = $this->syncNodeToTop($tree_id, $cms_ids[0]);
		}
		
		if($obj_id)
		{
			$refs = ilObject::_getAllReferences($obj_id);
			$ref_id = end($refs);
			return $this->syncCategory($tobj_id, $ref_id);
		}
		return 0;
	}
	
	/**
	 * Sync category
	 * @param type $tobj_id
	 * @param type $parent_ref_id
	 */
	protected function syncCategory($tobj_id, $parent_ref_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		$data = new ilECSCmsData($tobj_id);
		
		include_once './Modules/Category/classes/class.ilObjCategory.php';
		$cat = new ilObjCategory();
		$cat->setTitle($data->getTitle());
		$cat->create(); // true for upload
		$cat->createReference();
		$cat->putInTree($parent_ref_id);
		$cat->setPermissions($parent_ref_id);
		$cat->deleteTranslation($GLOBALS['lng']->getDefaultLanguage());
		$cat->addTranslation(
				$data->getTitle(),
				$cat->getLongDescription(),
				$GLOBALS['lng']->getDefaultLanguage(),
				1
		);
			
		// set imported
		$import = new ilECSImport(
			$this->getServer()->getServerId(),
			$cat->getId()
		);
		$import->setMID($this->getMid());
		$import->setEContentId($data->getCmsId());
		$import->setImported(true);
		$import->save();
		
		return $cat->getId();
	}

	/**
	 * Handle all in one setting
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doSync($a_content_id, $course, $a_parent_obj_id)
	{
		$course_id = (int) $course->basicData->id;
		$obj_id = $this->getImportId($course_id);
		
		if($obj_id)
		{
			// do update
			return $this->updateCourseData($course,$obj_id);
		}
		else
		{
			// create new course
			$crs = $this->createCourseData($course);
			$this->createCourseReference($crs,$a_parent_obj_id);
			$this->setImported($course_id,$crs);
			return true;
		}
	}
	
	/**
	 * Get import id of remote course
	 * Return 0 if object isn't imported.
	 * @param type $a_content_id
	 * @return type
	 */
	protected function getImportId($a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		return ilECSImport::_isImported(
				$this->getServer()->getServerId(),
				$a_content_id,
				$this->getMid()
		);
	}
	
	/**
	 * Update course data
	 * @param type $course
	 */
	protected function updateCourseData($course,$obj_id)
	{
		// do update
		$refs = ilObject::_getAllReferences($obj_id);
		$ref_id = end($refs);
		$crs_obj = ilObjectFactory::getInstanceByRefId($ref_id,false);
		if(!$crs_obj instanceof ilObject)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot instantiate course instance');
			return false;
		}
			
		// Update title
		$title = $course->basicData->title;
		$GLOBALS['ilLog']->write(__METHOD__.': new title is : '. $title);
			
		$crs_obj->setTitle($title);
		$crs_obj->update();
		return true;
	}
	
	/**
	 * Create course data from json
	 * @return ilObjCourse
	 */
	protected function createCourseData($course)
	{
		include_once './Modules/Course/classes/class.ilObjCourse.php';
		$course_obj = new ilObjCourse();
		$title = $course->basicData->title;
		$GLOBALS['ilLog']->write(__METHOD__.': Creating new course instance from ecs : '. $title);
		$course_obj->setTitle($title);
		$course_obj->create();
		return $course_obj;
	}
	
	/**
	 * Create course reference
	 * @param type $crs
	 * @param ilObjCourse $a_parent_obj_id
	 * @return ilObjCourse
	 */
	protected function createCourseReference($crs,$a_parent_obj_id)
	{
		$ref_ids = ilObject::_getAllReferences($a_parent_obj_id);
		$ref_id = end($ref_ids);
		
		$crs->createReference();
		$crs->putInTree($ref_id);
		$crs->setPermissions($ref_id);
		
		return $crs;
	}
	
	/**
	 * Set new course object imported
	 * @param int $a_content_id
	 * @param ilObjCourse $crs
	 */
	protected function setImported($a_content_id, $crs)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$import = new ilECSImport(
				$this->getServer()->getServerId(),
				$crs->getId()
		);
		$import->setMID($this->getMid());
		$import->setEContentId($a_content_id);
		$import->setImported(true);
		$import->save();
		return true;
	}
}
?>
