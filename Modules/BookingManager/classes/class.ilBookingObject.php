<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * a bookable ressource
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObject
{
	protected $id;		// int
	protected $title;	// string
	protected $type_id;	// id

	/**
	 * Constructor
	 *
	 * if id is given will read dataset from db
	 *
	 * @param	int	$a_id
	 */
	function __construct($a_id = NULL)
	{
		$this->id = (int)$a_id;
		$this->read();
	}

	/**
	 * Set object title
	 * @param	string	$a_title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get object title
	 * @return	string
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set booking type id
	 * @param	int	$a_type_id
	 */
	function setTypeId($a_type_id)
	{
		$this->type_id = (int)$a_type_id;
	}

	/**
	 * Get booking type id
	 * @return	int
	 */
	function getTypeId()
	{
		return $this->type_id;
	}

	/**
	 * Get dataset from db
	 */
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

	/**
	 * Create new entry in db
	 * @return	bool
	 */
	function save()
	{
		global $ilDB;

		if($this->id)
		{
			return false;
		}

		$id = $ilDB->nextId('booking_object');

		return $ilDB->query('INSERT INTO booking_object'.
			' (booking_object_id,title,type_id)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getTypeId(), 'integer').')');
	}

	/**
	 * Update entry in db
	 * @return	bool
	 */
	function update()
	{
		global $ilDB;

		if(!$this->id)
		{
			return false;
		}

		return $ilDB->query('UPDATE booking_object'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', type_id = '.$ilDB->quote($this->getTypeId(), 'integer').
			' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
	}

	/**
	 * Get list of booking objects for given type
	 * @param	int	$a_type_id
	 * @return	array
	 */
	static function getList($a_type_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT booking_object_id,title'.
			' FROM booking_object'.
			' WHERE type_id = '.$ilDB->quote($a_type_id, 'integer').
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