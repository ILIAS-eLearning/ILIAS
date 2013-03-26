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
		$GLOBALS['ilLog']->write(__METHOD__.': --------- content id '.$a_content_id);
		
		
		if($this->getMapping()->isAttributeMappingEnabled())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Handling advanced attribute mapping');
			return $this->doAttributeMapping($a_content_id,$course);
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
	 * Sync attribute mapping
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doAttributeMapping($a_content_id, $course)
	{
		// Check if course is already created
		$course_id = (int) $course->basicData->id;
		$obj_id = $this->getImportId($course_id);
		
		if($obj_id)
		{
			// do update
			$GLOBALS['ilLog']->write(__METHOD__.' Performing update of already imported course.');
			
			$refs = ilObject::_getAllReferences($obj_id);
			$ref = end($refs);
			
			return $this->doSync(
				$a_content_id, 
				$course,
				ilObject::_lookupObjId($GLOBALS['tree']->getParentId($ref))
			);
		}
		
		// Get all rules
		$matching_rule = 0;
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseMappingRule.php';
		foreach(ilECSCourseMappingRule::getRuleRefIds($this->getServer()->getServerId(), $this->getMid()) as $ref_id)
		{
			if(ilECSCourseMappingRule::isMatching(
					$course,
					$this->getServer()->getServerId(),
					$this->getMid(),
					$ref_id))
			{
				$matching_rule = $ref_id;
			}
		}
		if(!$matching_rule)
		{
			// Put course in default category
			$GLOBALS['ilLog']->write(__METHOD__.': No matching attribute mapping rule found.');
			$GLOBALS['ilLog']->write(__METHOD__.': Using course default category');
			return $this->doSync($a_content_id,$course,ilObject::_lookupObjId($this->getMapping()->getDefaultCourseCategory()));
		}
		// map according mapping rules
		$parent_ref = ilECSCourseMappingRule::doMappings($course,$this->getServer()->getServerId(),$this->getMid(),$ref_id);
		$this->doSync($a_content_id, $course, ilObject::_lookupObjId($parent_ref));
		return false;
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
		
		// Handle parallel groups
		
		if($obj_id)
		{
			// update multiple courses/groups according to parallel scenario
			$GLOBALS['ilLog']->write(__METHOD__.': '.$course->basicData->parallelGroupScenario);
			include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
			switch((int) $course->basicData->parallelGroupScenario)
			{
				case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
					$GLOBALS['ilLog']->write(__METHOD__.': Performing update for parallel groups in course.');
					$this->updateParallelGroups($course,$obj_id);
					break;
				
				case ilECSMappingUtils::PARALLEL_ALL_COURSES:
					$GLOBALS['ilLog']->write(__METHOD__.': Performing update for parallel courses.');
					$this->updateParallelCourses($course, $a_parent_obj_id);
					break;
				
				case ilECSMappingUtils::PARALLEL_ONE_COURSE:
				default:
					// nothing to do
					break;
				
			}
			
			// do update
			return $this->updateCourseData($course,$obj_id);
		}
		else
		{
			switch((int) $course->basicData->parallelGroupScenario)
			{
				case ilECSMappingUtils::PARALLEL_GROUPS_IN_COURSE:
					$GLOBALS['ilLog']->write(__METHOD__.': Parallel scenario "groups in courses".');
					$crs = $this->createCourseData($course);
					$crs = $this->createCourseReference($crs, $a_parent_obj_id);
					$this->setImported($course_id, $crs, $a_content_id);

					// Create parallel groups under crs
					$this->createParallelGroups($course,$crs->getRefId());
					break;
				
					
				
				case ilECSMappingUtils::PARALLEL_COURSES_FOR_LECTURERS:
					// Import empty to store the ecs ressource id (used for course member update).
					$this->setImported($course_id,NULL,$a_content_id);
					$GLOBALS['ilLog']->write(__METHOD__.': Parallel scenario "Courses foreach Lecturer".');
					break;

				case ilECSMappingUtils::PARALLEL_ALL_COURSES:
					$GLOBALS['ilLog']->write(__METHOD__.': Parallel scenario "Many courses".');
					
					$refs = ilObject::_getAllReferences($a_parent_obj_id);
					$ref = end($refs);
					$crs = $this->createCourseData($course);
					$this->createCourseReference($crs, $a_parent_obj_id);
					$this->setImported($course_id, $crs, $a_content_id);
					$this->createParallelCourses($course, $ref);
					break;
					
				default:
				case ilECSMappingUtils::PARALLEL_ONE_COURSE:
					$GLOBALS['ilLog']->write(__METHOD__.': Parallel scenario "One Course".');
					$crs = $this->createCourseData($course);
					$this->createCourseReference($crs, $a_parent_obj_id);
					$this->setImported($course_id, $crs, $a_content_id);
					break;
				
					
			}
			return true;
		}
	}
	
	/**
	 * Create parallel courses
	 * @param type $course
	 * @param type $parent_ref
	 */
	protected function createParallelCourses($course, $parent_ref)
	{
		foreach((array) $course->parallelGroups as $group)
		{
			$this->createParallelCourse($course, $group, $parent_ref);
		}
		return true;
	}
	
	/**
	 * Create parallel course
	 * @param type $course
	 * @param type $group
	 * @param type $parent_ref
	 */
	protected function createParallelCourse($course, $group, $parent_ref)
	{
		include_once './Modules/Course/classes/class.ilObjCourse.php';
		$course_obj = new ilObjCourse();
		$title = $group->title;
		$GLOBALS['ilLog']->write(__METHOD__.': Creating new parallel course instance from ecs : '. $title);
		$course_obj->setTitle($title);
		$course_obj->setSubscriptionMaxMembers((int) $group->maxParticipants);
		$course_obj->create();
		
		$this->createCourseReference($course_obj, ilObject::_lookupObjId($parent_ref));
		$this->setImported($course->basicData->id, $course_obj,0, $group->id);
		return true;
	}
	
	/**
	 * Update parallel group data
	 * @param type $course
	 * @param type $parent_obj
	 */
	protected function updateParallelCourses($course,$parent_obj)
	{
		$parent_refs = ilObject::_getAllReferences($parent_obj);
		$parent_ref = end($parent_refs);
		
		foreach((array) $course->parallelGroups as $group)
		{
			$obj_id = $this->getImportId($course->basicData->id, $group->id);
			$GLOBALS['ilLog']->write(__METHOD__.': Imported obj id is ' .$obj_id);
			if(!$obj_id)
			{
				$this->createParallelCourse($course, $group, $parent_ref);
			}
			else
			{
				$course_obj = ilObjectFactory::getInstanceByObjId($obj_id,false);
				if($course_obj instanceof ilObjCourse)
				{
					$GLOBALS['ilLog']->write(__METHOD__.': New title is '. $group->title);
					$course_obj->setTitle($group->title);
					$course_obj->setSubscriptionMaxMembers($group->maxParticipants);
					$course_obj->update();
				}
			}
		}
		return true;
	}
	
	
	
	/**
	 * This create parallel groups
	 * @param type $course
	 * @param ilObjCourse
	 */
	protected function createParallelGroups($course, $parent_ref)
	{
		foreach((array) $course->parallelGroups as $group)
		{
			$this->createParallelGroup($course, $group, $parent_ref);
		}
		return true;
	}

	/**
	 * Create parallel group
	 * @param type $course
	 * @param type $group
	 */
	protected function createParallelGroup($course, $group, $parent_ref)
	{
		include_once './Modules/Group/classes/class.ilObjGroup.php';
		$group_obj = new ilObjGroup();
		$group_obj->setTitle($group->title);
		$group_obj->setMaxMembers((int) $group->maxParticipants);
		$group_obj->create();
		$group_obj->createReference();
		$group_obj->putInTree($parent_ref);
		$group_obj->setPermissions($parent_ref);
		$group_obj->initGroupStatus(GRP_TYPE_CLOSED);
		$this->setImported($course->basicData->id, $group_obj, 0, $group->id);
	}


	/**
	 * Update parallel group data
	 * @param type $course
	 * @param type $parent_obj
	 */
	protected function updateParallelGroups($course,$parent_obj)
	{
		$parent_refs = ilObject::_getAllReferences($parent_obj);
		$parent_ref = end($parent_refs);
		
		foreach((array) $course->parallelGroups as $group)
		{
			$obj_id = $this->getImportId($course->basicData->id, $group->id);
			$GLOBALS['ilLog']->write(__METHOD__.': Imported obj id is ' .$obj_id);
			if(!$obj_id)
			{
				$this->createParallelGroup($course, $group, $parent_ref);
			}
			else
			{
				$group_obj = ilObjectFactory::getInstanceByObjId($obj_id,false);
				if($group_obj instanceof ilObjGroup)
				{
					$GLOBALS['ilLog']->write(__METHOD__.': New title is '. $group->title);
					$group_obj->setTitle($group->title);
					$group_obj->setMaxMembers((int) $group->maxParticipants);
					$group_obj->update();
				}
			}
		}
	}
	
	/**
	 * Get import id of remote course
	 * Return 0 if object isn't imported.
	 * @param type $a_content_id
	 * @return type
	 */
	protected function getImportId($a_content_id,$a_sub_id = NULL)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		return ilECSImport::_isImported(
				$this->getServer()->getServerId(),
				$a_content_id,
				$this->getMid(),
				$a_sub_id
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
	 * @param ilObjCourse $crs_obj
	 * @param int $a_parent_obj_id
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
	protected function setImported($a_content_id, $object, $a_ecs_id = 0, $a_sub_id = 0)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$import = new ilECSImport(
				$this->getServer()->getServerId(),
				is_object($object) ? $object->getId() : 0
		);
		
		$GLOBALS['ilLog']->write(__METHOD__.': Imported with ecs id '.$a_ecs_id);
		
		$import->setSubId($a_sub_id);
		$import->setECSId($a_ecs_id);
		$import->setMID($this->getMid());
		$import->setEContentId($a_content_id);
		$import->setImported(true);
		$import->save();
		return true;
	}
}
?>
