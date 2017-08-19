<?php
/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version           $Id$
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
		$this->limit_age = new ilDate(0, IL_CAL_UNIX);
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
	
	/**
	 * 
	 * @return ilDateTime
	 */
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
		$GLOBALS['ilLog']->write(__METHOD__.': Handling delete');
		switch($this->getMode())
		{
			case self::MODE_TRASH_RESTORE:
				$GLOBALS['ilLog']->write(__METHOD__.': Restore trash to recovery folder');
				$this->restore();
				break;
				
			case self::MODE_TRASH_REMOVE:
				$GLOBALS['ilLog']->write(__METHOD__.': Remove selected from system.');
				$GLOBALS['ilLog']->write(__METHOD__.': Type limit: '. print_r($this->getTypesLimit(),TRUE));
				$GLOBALS['ilLog']->write(__METHOD__.': Age limit: '. (string) $this->getAgeLimit());
				$GLOBALS['ilLog']->write(__METHOD__.': Number limit: '. (string) $this->getNumberLimit());
				$this->removeSelectedFromSystem();
				return TRUE;
		}
	}

	/**
	 * Restore to recovery folder
	 */
	protected function restore()
	{
		$deleted = $this->readDeleted();
		
		$GLOBALS['ilLog']->write(__METHOD__.': Found deleted : '.print_r($deleted,TRUE));
		
		$factory = new ilObjectFactory();
		
		foreach($deleted as $tmp_num => $deleted_info)
		{
			$ref_obj = $factory->getInstanceByRefId($deleted_info['child'], FALSE);
			if(!$ref_obj instanceof ilObject)
			{
				continue;
			}

			$GLOBALS['tree']->deleteNode($deleted_info['tree'],$deleted_info['child']);
			$GLOBALS['ilLog']->write(__METHOD__.': Object tree entry deleted');
			
			if($ref_obj->getType() != 'rolf')
			{
				$GLOBALS['rbacadmin']->revokePermission($deleted_info['child']);
				$ref_obj->putInTree(RECOVERY_FOLDER_ID);
				$ref_obj->setPermissions(RECOVERY_FOLDER_ID);
				$GLOBALS['ilLog']->write(__METHOD__.': Object moved to recovery folder');
			}
		}
	}
	
	/**
	 * remove (containers) from system
	 */
	protected function removeSelectedFromSystem()
	{
		$factory = new ilObjectFactory();

		$deleted = $this->readSelectedDeleted();
		foreach($deleted as $tmp_num => $deleted_info)
		{
			$sub_nodes = $this->readDeleted($deleted_info['tree']);
			
			foreach($sub_nodes as $tmp_num => $subnode_info)
			{
				$ref_obj = $factory->getInstanceByRefId($subnode_info['child'], FALSE);
				if(!$ref_obj instanceof ilObject)
				{
					continue;
				}
				
				$ref_obj->delete();
				ilTree::_removeEntry($subnode_info['tree'],$subnode_info['child']);
			}
		}
	}
	
	/**
	 * read deleted according to filter settings
	 */
	protected function readSelectedDeleted()
	{
		global $ilDB;
		
		$and_types = '';
		ilLoggerFactory::getLogger('sysc')->dump($this->getTypesLimit());
		
		$types = array();
		foreach((array) $this->getTypesLimit() as $id => $type)
		{
			if($type)
			{
				$types[] = $type;
			}
		}
		if(count($types))
		{
			$and_types = 'AND '.$ilDB->in('o.type', $this->getTypesLimit(),FALSE,'text').' ';
		}
		
		$and_age = '';
		$age_limit = $this->getAgeLimit()->get(IL_CAL_UNIX);
		if($age_limit > 0)
		{
			$and_age = 'AND r.deleted < '.$ilDB->quote($this->getAgeLimit()->get(IL_CAL_DATETIME)).' ';
		}
		$limit = '';
		if($this->getNumberLimit())
		{
			$limit = 'LIMIT '.(int) $this->getNumberLimit();
		}
		
		$query = 'SELECT child,tree FROM tree t JOIN object_reference r ON child = r.ref_id '.
				'JOIN object_data o on r.obj_id = o.obj_id '.
				'WHERE tree < '.$ilDB->quote(0,'integer').' '.
				'AND child = -tree ';
		
		$query .= $and_age;
		$query .= $and_types;
		$query .= 'ORDER BY depth desc ';
		$query .= $limit;
		
		$GLOBALS['ilLog']->write($query);
		
		$deleted = array();
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$deleted[] = array(
				'tree' => $row->tree,
				'child' => $row->child
			);
		}
		return $deleted;
		
	}




	/**
	 * Read deleted objects
	 * @global type $ilDB
	 * @return type
	 */
	protected function readDeleted($tree_id = null)
	{
		global $ilDB;
		
		$query = 'SELECT child,tree FROM tree t JOIN object_reference r ON child = r.ref_id '.
				'JOIN object_data o on r.obj_id = o.obj_id ';
		
		if($tree_id === null)
		{
			$query .= 'WHERE tree < '.$ilDB->quote(0,'integer').' ';
		}
		else
		{
			$query .= 'WHERE tree = '.$ilDB->quote($tree_id,'integer').' ';
		}
		$query .= 'ORDER BY depth desc';
		
		$res = $ilDB->query($query);
		
		$deleted = array();
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$deleted[] = array(
				'tree' => $row->tree,
				'child' => $row->child
			);
		}
		return $deleted;
	}
	
}
?>
