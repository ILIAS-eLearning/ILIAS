<?php

include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';

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
			return $this->handleAllInOne($a_content_id, $course);
		}
	}
	
	/**
	 * Handle all in one setting
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function handleAllInOne($a_content_id, $course)
	{
		$obj_id = $this->getImportId($a_content_id);
		
		if($obj_id)
		{
			// do update
			$this->updateCourseData($course,$obj_id);
			return true;
		}
		else
		{
			// create new course
			$crs = $this->createCourseData($course);
			$this->createCourseReference($crs,$this->getMapping()->getAllInOneCategory());
			$this->setImported($a_content_id,$crs);
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
