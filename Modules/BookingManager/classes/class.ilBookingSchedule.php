<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * schedule for booking ressource
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingSchedule
{
	protected $id;			// int
	protected $title;		// string
	protected $pool_id;		// id
	protected $raster;		// int
	protected $rent_min;	// int
	protected $rent_max;	// int
	protected $auto_break;	// int
	protected $deadline;	// int
	protected $definition;  // array

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
	 * Set booking pool id (aka parent obj ref id)
	 * @param	int	$a_type_id
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
	 * Set booking raster (in minutes)
	 * @param	int	$a_raster
	 */
	function setRaster($a_raster)
	{
		$this->raster = (int)$a_raster;
	}

	/**
	 * Get booking raster
	 * @return	int
	 */
	function getRaster()
	{
		return $this->raster;
	}

	/**
	 * Set minimum rental time
	 * @param	int	$a_min
	 */
	function setMinRental($a_min)
	{
		$this->rent_min = (int)$a_min;
	}

	/**
	 * Get minimum rental time
	 * @return	int
	 */
	function getMinRental()
	{
		return $this->rent_min;
	}

	/**
	 * Set maximum rental time
	 * @param	int	$a_max
	 */
	function setMaxRental($a_max)
	{
		$this->rent_max = (int)$a_max;
	}

	/**
	 * Get maximum rental time
	 * @return	int
	 */
	function getMaxRental()
	{
		return $this->rent_max;
	}

	/**
	 * Set break time
	 * @param	int	$a_break
	 */
	function setAutoBreak($a_break)
	{
		$this->auto_break = (int)$a_break;
	}

	/**
	 * Get break time
	 * @return	int
	 */
	function getAutoBreak()
	{
		return $this->auto_break;
	}

	/**
	 * Set deadline
	 * @param	int	$a_deadline
	 */
	function setDeadline($a_deadline)
	{
		$this->deadline = (int)$a_deadline;
	}

	/**
	 * Get deadline
	 * @return	int
	 */
	function getDeadline()
	{
		return $this->deadline;
	}

	/**
	 * Set definition
	 * @param	array	$a_definition
	 */
	function setDefinition($a_definition)
	{
		$this->definition = $a_definition;
	}

	/**
	 * Get definition
	 * @return	array
	 */
	function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT title,raster,rent_min,rent_max,auto_break,'.
				'deadline,definition'.
				' FROM booking_schedule'.
				' WHERE booking_schedule_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setDefinition(unserialize($row['definition']));
			$this->setDeadline($row['deadline']);
			if($row['raster'])
			{
				$this->setRaster($row['raster']);
				$this->setMinRental($row['rent_min']);
				$this->setMaxRental($row['rent_max']);
				$this->setAutoBreak($row['auto_break']);
			}
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

		$id = $ilDB->nextId('booking_schedule');

		return $ilDB->query('INSERT INTO booking_schedule'.
			' (booking_schedule_id,title,pool_id,raster,rent_min,rent_max,auto_break,'.
			'deadline,definition)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getPoolId(), 'integer').','.$ilDB->quote($this->getRaster(), 'integer').
			','.$ilDB->quote($this->getMinRental(), 'integer').','.$ilDB->quote($this->getMaxRental(), 'integer').
			','.$ilDB->quote($this->getAutoBreak(), 'integer').','.$ilDB->quote($this->getDeadline(), 'integer').
			','.$ilDB->quote(serialize($this->getDefinition()), 'text').')');
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

		return $ilDB->query('UPDATE booking_schedule'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', pool_id = '.$ilDB->quote($this->getPoolId(), 'integer').
			', raster = '.$ilDB->quote($this->getRaster(), 'integer').
			', rent_min = '.$ilDB->quote($this->getMinRental(), 'integer').
			', rent_max = '.$ilDB->quote($this->getMaxRental(), 'integer').
			', auto_break = '.$ilDB->quote($this->getAutoBreak(), 'integer').
			', deadline = '.$ilDB->quote($this->getDeadline(), 'integer').
			', definition = '.$ilDB->quote(serialize($this->getDefinition()), 'text').
			' WHERE booking_schedule_id = '.$ilDB->quote($this->id, 'integer'));
	}

	/**
	 * Get list of booking objects for given pool
	 * @param	int	$a_pool_id
	 * @return	array
	 */
	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT booking_schedule_id,title'.
			' FROM booking_schedule'.
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