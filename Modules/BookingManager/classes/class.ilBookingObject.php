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
	protected $id;			// int
	protected $pool_id;		// int
	protected $title;		// string
	protected $description; // string
	protected $nr_of_items; // int
	protected $schedule_id; // int

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
	 * Set object description
	 * @param	string	$a_value
	 */
	function setDescription($a_value)
	{
		$this->description = $a_value;
	}

	/**
	 * Get object description
	 * @return	string
	 */
	function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set booking pool id
	 * @param	int	$a_pool_id
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = (int)$a_pool_id;
	}

	/**
	 * Get booking pool id
	 * @return	int
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Set booking schedule id
	 * @param	int	$a_schedule_id
	 */
	function setScheduleId($a_schedule_id)
	{
		$this->schedule_id = (int)$a_schedule_id;
	}

	/**
	 * Get booking schedule id
	 * @return	int
	 */
	function getScheduleId()
	{
		return $this->schedule_id;
	}
	
	/**
	 * Set number of items
	 * @param	int	$a_value
	 */
	function setNrOfItems($a_value)
	{
		$this->nr_of_items = (int)$a_value;
	}

	/**
	 * Get number of items
	 * @return	int
	 */
	function getNrOfItems()
	{
		return $this->nr_of_items;
	}

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT *'.
				' FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setDescription($row['description']);
			$this->setPoolId($row['pool_id']);
			$this->setScheduleId($row['schedule_id']);
			$this->setNrOfItems($row['nr_items']);
		}
	}
	
	/**
	 * Parse properties for sql statements
	 * @return array 
	 */
	protected function getDBFields()
	{
		$fields = array(
			'title' => array('text', $this->getTitle()),
			'description' => array('text', $this->getDescription()),
			'schedule_id' => array('text', $this->getScheduleId()),
			'nr_items' => array('text', $this->getNrOfItems())			
		);
		
		return $fields;		
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
		
		$this->id = $ilDB->nextId('booking_object');
		
		$fields = $this->getDBFields();
		$fields['booking_object_id'] = array('integer', $this->id);
		$fields['pool_id'] = array('integer', $this->getPoolId());

		return $ilDB->insert('booking_object', $fields);
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
		
		$fields = $this->getDBFields();
						
		return $ilDB->update('booking_object', $fields, 
			array('booking_object_id'=>array('integer', $this->id)));
	}

	/**
	 * Get list of booking objects for given type	 
	 * @param	int	$a_pool_id
	 * @return	array
	 */
	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT *'.
			' FROM booking_object'.
			' WHERE pool_id = '.$ilDB->quote($a_pool_id, 'integer').
			' ORDER BY title');
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Delete single entry
	 * @return bool
	 */
	function delete()
	{
		global $ilDB;

		if($this->id)
		{
			return $ilDB->manipulate('DELETE FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
		}
	}
}

?>