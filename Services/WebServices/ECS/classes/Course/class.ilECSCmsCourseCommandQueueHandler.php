<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseCommandQueueHandler implements ilECSCommandQueueHandler
{
	private $server = null;
	private $mid = 0;
	
	
	/**
	 * Constructor
	 */
	public function __construct(ilECSSetting $server)
	{
		$this->server = $server;
		$this->init();
	}
	
	/**
	 * Get server
	 * @return ilECSServerSetting
	 */
	public function getServer()
	{
		return $this->server;
	}


	/**
	 * Handle create
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleCreate(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';

		try 
		{
			$crs_reader = new ilECSCourseConnector($this->getServer());
			$course = $crs_reader->getCourse($a_content_id);
			
			$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($course,true));
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course creation failed  with mesage ' . $e->getMessage());
			return false;
		}
		
		return true;
	}

	/**
	 * Handle delete
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleDelete(ilECSSetting $server, $a_content_id)
	{
		return true;
	}

	/**
	 * Handle update
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleUpdate(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';

		try 
		{
			$crs_reader = new ilECSCourseConnector($this->getServer());
			$course = $crs_reader->getCourse($a_content_id);
			
			$this->doUpdate($a_content_id, $course[0]);
			return true;
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Course creation failed  with mesage ' . $e->getMessage());
			return false;
		}
		return true;
	}
	
	/**
	 * init handler
	 */
	private function init()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		$this->mid = ilECSParticipantSettings::loookupCmsMid($this->getServer()->getServerId());
	}
	
	/**
	 * Perform update
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doUpdate($a_content_id, $course)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Handling content id: '.$a_content_id);
		
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$obj_id = ilECSImport::_isImported(
				$this->getServer()->getServerId(),
				$a_content_id,
				$this->mid
			);
		
		if($obj_id)
		{
			
			// do update
			$refs = ilObject::_getAllReferences($obj_id);
			$ref_id = end($refs);
			$crs_obj = ilObjectFactory::getInstanceByRefId($ref_id,false);
			if(!$crs_obj instanceof ilObject)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Cannot create course instance');
				return false;
			}
			
			// Update title
			$title = $course->basicData->title;
			$GLOBALS['ilLog']->write(__METHOD__.': new title is : '. $title);
			
			$crs_obj->setTitle($title);
			$crs_obj->update();
			return true;
		}
		else
		{
			
			// lookup parent
			$allocationObj = $course->allocation;
			foreach((array) $allocationObj as $allocs)
			{
				$parent_id = $allocs->parentID;
				break;
			}
			$parentObjId = ilECSImport::_isImported(
					$this->getServer()->getServerId(),
					$parent_id,
					$this->mid
				);
			if(!$parentObjId)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Cannot create course. no imported parent given.');
				return false;
			}
			$refs = ilObject::_getAllReferences($parentObjId);
			$parent_ref_id = end($refs);
			
			include_once './Modules/Course/classes/class.ilObjCourse.php';
			$course_obj = new ilObjCourse();
			$title = $course->basicData->title;
			$GLOBALS['ilLog']->write(__METHOD__.': new title is : '. $title);
			$course_obj->setTitle($title);
			$course_obj->create(); // true for upload
			$course_obj->createReference();
			$course_obj->putInTree($parent_ref_id);
			$course_obj->setPermissions($parent_ref_id);

			// set imported
			$import = new ilECSImport(
					$this->getServer()->getServerId(),
					$course_obj->getId()
				);
			$import->setMID($this->mid);
			$import->setEContentId($a_content_id);
			$import->setImported(true);
			$import->save();
			return true;
		}
	}
}
?>
