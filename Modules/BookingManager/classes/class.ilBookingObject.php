<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObject
{
	protected $id; 
	protected $title;
	protected $type_id;
	
	function __construct($a_id = NULL)
	{
		$this->id = (int)$a_id;
		$this->read();
	}

	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	function getTitle()
	{
		return $this->title;
	}

	function setTypeId($a_type_id)
	{
		$this->pool_id = $a_type_id;
	}

	function getTypeId()
	{
		return $this->type_id;
	}

	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT title,type_id'.
				' FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setTypeId($row['type_id']);
		}
	}

	function save()
	{
		global $ilDB;

		$id = $ilDB->nextId('booking_object');

		return $ilDB->query('INSERT INTO booking_objeect (booking_object_id,title,object_id)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getTypeId(), 'integer').')');
	}

	function update()
	{
		global $ilDB;

		return $ilDB->query('UPDATE booking_object'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', type_id = '.$ilDB->quote($this->getTypeId(), 'integer').
			' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
	}

	static function getList($a_type_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT * FROM booking_object'.
			' WHERE booking_object_id = '.$ilDB->quote($a_type_id, 'integer').
			' ORDER BY title');
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}
}

?>