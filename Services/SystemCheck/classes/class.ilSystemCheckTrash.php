<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ilSystemCheckTrash
{
	const MODE_TRASH_RESTORE = 1;
	const MODE_TRASH_REMOVE = 2;
	
	private $limit_number = 0;
	private $limit_age = null;
	private $limit_types = array();
	
	
	public function __construct()
	{
		;
	}
	
	public function setNumberLimit($a_limit)
	{
		$this->limit_number = $a_limit;
	}
	
	public function getNumberLimit()
	{
		return $this->limit_number;
	}
	
	public function setAgeLimit(ilDateTime $dt)
	{
		$this->limit_age = $dt;
	}
	
	public function getAgeLimit()
	{
		return $this->limit_age;
	}
	
	public function setTypesLimit($a_types)
	{
		$this->limit_types = (array) $a_types;
	}
	
	public function getTypesLimit()
	{
		return (array) $this->limit_types;
	}
	
	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	
	public function getMode()
	{
		return $this->mode;
	}
	
	public function start()
	{
		switch($this->getMode())
		{
			case self::MODE_TRASH_RESTORE:
				$this->restore();
				break;
				
			case self::MODE_TRASH_REMOVE:
				
		}
	}

	/**
	 * Restore to recovery folder
	 */
	protected function restore()
	{
		$deleted = $this->readDeleted();
		
		$factory = new ilObjectFactory();
		
		foreach($deleted as $tree_id => $ref_id)
		{
			$ref_obj = $factory->getInstanceByRefId($ref_id, FALSE);
			if(!$ref_obj instanceof ilObject)
			{
				continue;
			}

			$GLOBALS['tree']->deleteNode($tree_id,$ref_id);
			
			if($ref_obj->getType() != 'rolf')
			{
				$GLOBALS['rbacadmin']->revokePermission($ref_id);
				$ref_obj->putInTree(RECOVERY_FOLDER_ID);
				$ref_obj->setPermissions(RECOVERY_FOLDER_ID);
			}
			break;
		}
		
	}
	
	
	/**
	 * Read deleted objects
	 * @global type $ilDB
	 * @return type
	 */
	protected function readDeleted()
	{
		global $ilDB;
		
		$query = 'SELECT child,tree FROM tree t JOIN object_reference r ON child = r.ref_id '.
				'JOIN object_data o on r.obj_id = o.obj_id '.
				'WHERE tree < '.$ilDB->quote(0,'integer').' '.
				'ORDER BY depth desc';
		$res = $ilDB->query($query);
		
		$deleted = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$deleted[$row->tree] = $row->child;
		}
		return $deleted;
	}
	
}
?>
