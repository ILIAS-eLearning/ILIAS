<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeTasks
{
	private $db = null;
	private $task = null;
	
	public function __construct(ilSCTask $task)
	{
		$this->db = $GLOBALS['ilDB'];
		$this->task = $task;
	}
	
	/**
	 * @return ilDB
	 */
	public function getDB()
	{
		return $this->db;
	}
	
	/**
	 * 
	 * @return ilSCTask
	 */
	public function getTask()
	{
		return $this->task;
	}
	
	/**
	 * 
	 */
	public function validateDuplicates()
	{
		$failures = $this->checkDuplicates();
		
		if(count($failures))
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
		}
		else
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
		}
		$this->getTask()->setLastUpdate(new ilDateTime(time(),IL_CAL_UNIX));
		$this->getTask()->update();
		return count($failures);
	}
	
	/**
	 * Check for duplicates
	 */
	protected function checkDuplicates()
	{
		$query = 'SELECT child, count(child) num FROM tree '.
				'GROUP BY child '.
				'HAVING count(child) > 1';
		$res = $this->getDB()->query($query);
		
		$failures = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$failures[] = $row->child;
		}
		return $failures;
	}
	
	/**
	 * Find missing objects
	 */
	public function findMissing()
	{
		global $ilDB;
		
		$failures = $this->readMissing();
		
		if(count($failures))
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
		}
		else
		{
			$this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
		}
		
		$this->getTask()->setLastUpdate(new ilDateTime(time(),IL_CAL_UNIX));
		$this->getTask()->update();
		return count($failures);
		
	}
	
	/**
	 * Repair missing objects
	 */
	public function repairMissing()
	{
		$failures = $this->readMissing();
		$recf_ref_id = $this->createRecoveryContainer();
		foreach($failures as $ref_id)
		{
			$this->repairMissingObject($recf_ref_id,$ref_id);
		}
		
	}
	
	/**
	 * Repair missing object
	 * @param type $a_parent_ref
	 */
	protected function repairMissingObject($a_parent_ref, $a_ref_id)
	{
		global $ilDB;
		
		// check if object entry exist
		$query = 'SELECT obj_id FROM object_reference '.
				'WHERE ref_id = '.$ilDB->quote($a_ref_id,'integer');
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = 'SELECT type, title FROM object_data '.
					'WHERE obj_id = '.$ilDB->quote($row->obj_id,'integer');
			$ores = $ilDB->query($query);
			
			$done = FALSE;
			while($orow = $ores->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Moving to recovery folder: '.$orow->type.': '.$orow->title);
				$done = TRUE;
				
				include_once './Services/Object/classes/class.ilObjectFactory.php';
				$factory = new ilObjectFactory();
				$ref_obj = $factory->getInstanceByRefId($a_ref_id,FALSE);
				
				if($ref_obj instanceof ilObjRoleFolder) 
				{
					$ref_obj->delete();
				}
				elseif($ref_obj instanceof ilObject)
				{
					$ref_obj->putInTree($a_parent_ref);
					$ref_obj->setPermissions($a_parent_ref);
					$GLOBALS['ilLog']->write(__METHOD__.': Moving finished');
					break;
				}
			}
			if(!$done)
			{
				// delete reference value
				$query = 'DELETE FROM object_reference WHERE ref_id = '.$ilDB->quote($a_ref_id,'integer');
				$ilDB->manipulate($query);
				$GLOBALS['ilLog']->write(__METHOD__.': Delete reference for "object" without tree and object_data entry: ref_id= '.$a_ref_id );
			}
		}
		
	}

	/**
	 * Read missing objects in tree
	 * Entry in oject_reference but no entry in tree
	 * @global type $ilDB
	 * @return type
	 */
	protected function readMissing()
	{
		global $ilDB;
		
		$query = 'SELECT ref_id FROM object_reference '.
				'LEFT JOIN tree ON ref_id = child '.
				'WHERE child IS NULL';
		$res = $ilDB->query($query);
		
		$failures = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$failures[] = $row->ref_id;
		}
		return $failures;
	}
	
	/**
	 * Create a reccovery folder
	 */
	protected function createRecoveryContainer()
	{
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		
		include_once './Modules/Folder/classes/class.ilObjFolder.php';
		$folder = new ilObjFolder();
		$folder->setTitle('__System check recovery: '.$now->get(IL_CAL_DATETIME));
		$folder->create();
		$folder->createReference();
		$folder->putInTree(RECOVERY_FOLDER_ID);
		
		return $folder->getRefId();
	}
	
}
?>