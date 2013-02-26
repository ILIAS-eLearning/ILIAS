<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesContainerReference 
*/
class ilContainerReference extends ilObject
{
	const TITLE_TYPE_REUSE = 1;
	const TITLE_TYPE_CUSTOM = 2;
	
	protected $db = null;
	protected $target_id = null;
	protected $target_ref_id = null;
	protected $title_type = self::TITLE_TYPE_REUSE;
	
	/**
	 * Constructor 
	 * @param int $a_id reference id
	 * @param bool $a_call_by_reference  
	 * @return void
	 */
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $ilDB;

		parent::__construct($a_id,$a_call_by_reference);
	}
	
	/**
	 * lookup target id
	 *
	 * @access public
	 * @param int $a_ref_id Course reference obj_id
	 * @return
	 * @static
	 */
	public static function _lookupTargetId($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM container_reference ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->target_obj_id;
		}
		return $a_obj_id;
	}
	
	/**
	 * Lookup target ref_id 
	 * @param int $a_obj_id obj_id
	 * @return
	 * @static
	 */
	 public static function _lookupTargetRefId($a_obj_id)
	 {
	 	global $ilDB;
	 	
	 	$query = "SELECT ref_id FROM object_reference obr ".
	 		"JOIN container_reference cr ON obr.obj_id = cr.target_obj_id ".
	 		"WHERE cr.obj_id = ".$ilDB->quote($a_obj_id,'integer');
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		return $row->ref_id;
	 	}
	 	return false;
	 }
	 
	 /**
	  * Overwitten from base class
	  * @param int $a_obj_id
	  * @return 
	  */
	 public static function _lookupTitle($a_obj_id)
	 {
		 global $ilDB;
		 
		 $query = 'SELECT title,title_type FROM container_reference cr '.
				 'JOIN object_data od ON cr.obj_id = od.obj_id '.
				 'WHERE cr.obj_id = '.$ilDB->quote($a_obj_id,'integer');
		 $res = $ilDB->query($query);
		 while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		 {
			 if($row->title_type == ilContainerReference::TITLE_TYPE_CUSTOM)
			 {
				 return $row->title;
			 }
		 }
		 return ilContainerReference::_lookupTargetTitle($a_obj_id);
	 }
	
	/**
	 * Lookup target title 
	 *
	 * @return string title
	 * @static
	 */
	 public static function _lookupTargetTitle($a_obj_id)
	 {
	 	global $ilDB;
	 	
	 	$query = "SELECT title FROM object_data od ".
	 		"JOIN container_reference cr ON target_obj_id = od.obj_id ".
	 		"WHERE cr.obj_id = ".$ilDB->quote($a_obj_id ,'integer');
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		return $row->title;
	 	}
	 	return '';
	 }
	
	/**
	 * lookup source id
	 *  
	 * @param int $a_target_id obj_id of course or category
	 * @return int obj_id of references
	 * @static
	 */
	 public static function _lookupSourceId($a_target_id)
	 {
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM container_reference ".
	 		"WHERE target_obj_id = ".$ilDB->quote($a_target_id,'integer')." ";
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		return $row->obj_id;
	 	}
	 	return false;
	 }
	
	/**
	 * get target id
	 *
	 * @access public
	 * @return
	 */
	public function getTargetId()
	{
		return $this->target_id;
	}
	
	
	/**
	 * set target id
	 *
	 * @access public
	 * @param int $a_target_id target id
	 * @return
	 */
	public function setTargetId($a_target_id)
	{
		$this->target_id = $a_target_id;
	}
	
	/**
	 * set target ref_id
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setTargetRefId($a_id)
	{
		$this->target_ref_id = $a_id;
	}
	
	/**
	 * get Target ref_id
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getTargetRefId()
	{
		return $this->target_ref_id;
	}
	
	/**
	 * get title type
	 * @return type 
	 */
	public function getTitleType()
	{
		return $this->title_type;
	}
	
	/**
	 * Set title type
	 * @param type $type 
	 */
	public function setTitleType($type)
	{
		$this->title_type = $type;
	}
	
	/**
	 * read
	 *
	 * @access public
	 * @return
	 */
	public function read()
	{
		global $ilDB;
		
		parent::read();
		
		$query = "SELECT * FROM container_reference ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(),'integer')." ";
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTargetId($row->target_obj_id);
			$this->setTitleType($row->title_type);
		}
		$ref_ids = ilObject::_getAllReferences($this->getTargetId());
		$this->setTargetRefId(current($ref_ids));
		
		if($this->getTitleType() == ilContainerReference::TITLE_TYPE_REUSE)
		{
			#$this->title = $this->lng->txt('reference_of').' '.ilObject::_lookupTitle($this->getTargetId());
			$this->title = ilObject::_lookupTitle($this->getTargetId());
		}
	}

	/**
	 * Get presentation title
	 * @return string presentation title
	 */
	public function getPresentationTitle()
	{
		if($this->getTitleType() == self::TITLE_TYPE_CUSTOM)
		{
			return $this->getTitle();
		}
		else
		{
			return $this->lng->txt('reference_of').' '.$this->getTitle();
		}
	}
	
	/**
	 * update object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function update()
	{
		global $ilDB;
		
		parent::update();
		
		$query = "DELETE FROM container_reference ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(),'integer')." ";
		$ilDB->manipulate($query);
		
		$query = "INSERT INTO container_reference (obj_id, target_obj_id, title_type) ".
			"VALUES( ".
			$ilDB->quote($this->getId(),'integer').", ".
			$ilDB->quote($this->getTargetId(),'integer').", ".
			$ilDB->quote($this->getTitleType(),'integer').' '.
			")";
		$ilDB->manipulate($query);
	}
	
	/**
	 * delete
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function delete()
	{
		global $ilDB;

		if(!parent::delete())
		{
			return false;
		}

		$query = "DELETE FROM container_reference ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(),'integer')." ";
		$ilDB->manipulate($query);
		
		return true;
	}
	
	/**
	 * Clone course reference
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 * 
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB,$ilUser;
		
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	
		$query = "INSERT INTO container_reference (obj_id, target_obj_id, title_type) ".
			"VALUES( ".
			$ilDB->quote($new_obj->getId(),'integer').", ".
			$ilDB->quote($this->getTargetId(),'integer').", ".
			$ilDB->quote($this->getTitleType(),'integer').' '.
			")";
		$ilDB->manipulate($query);
		return $new_obj;
	}
	
}

?>