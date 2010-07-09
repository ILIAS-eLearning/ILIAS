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
class ilBookingType
{
	protected $id; 
	protected $title;
	protected $pool_id;
	
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

	function setPoolId($a_pool_id)
	{
		$this->pool_id = $a_pool_id;
	}

	function getPoolId()
	{
		return $this->pool_id;
	}

	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT title,pool_id'.
				' FROM booking_type'.
				' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setPoolId($row['pool_id']);
		}
	}

	function save()
	{
		global $ilDB;

		$id = $ilDB->nextId('booking_type');

		return $ilDB->query('INSERT INTO booking_type (booking_type_id,title,pool_id)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getPoolId(), 'integer').')');
	}

	function update()
	{
		global $ilDB;

		return $ilDB->query('UPDATE booking_type'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', pool_id = '.$ilDB->quote($this->getPoolId(), 'integer').
			' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
	}

	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT * FROM booking_type'.
			' WHERE pool_id = '.$ilDB->quote($a_pool_id, 'integer').
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