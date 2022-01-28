<?php

class ilGeoLocationDBRepository implements ilGeoLocationRepository {

	public const TABLE_NAME = 'geo_location';

	public function __construct(\ilDBInterface $a_db)
	{
		$this->db = $a_db;
	}

	public function createGeoLocation(
		string $a_title,
		float $a_latitude,
		float $a_longitude,
		\DateTimeImmutable $a_expiration_timestamp
	) : ilGeoLocation
	{
		// Get next free id for object
		$id = $this->db->nextId($this->db->quoteIdentifier(self::TABLE_NAME));

		// Insert in database
		$this->db->insert($this->db->quoteIdentifier(self::TABLE_NAME), array(
			'id' => array('integer', $id),
			'title' => array('text', $a_title),
			'latitude' => array('float', $a_latitude),
			'longitude' => array('float', $a_longitude),
			'expiration_timestamp' => array('timestamp', $a_expiration_timestamp->getTimestamp())
		));

		// Return the new created object or just the id
		return new ilGeoLocation(
			$id,
			$a_title,
			$a_latitude,
			$a_longitude,
			$a_expiration_timestamp
		);
	}

	public function getGeoLocationById(int $a_id) : ilGeoLocation
	{
		// Set up SQL-Statement
		$query = 'SELECT title, latitude, longitude, expiration_timestamp' .
				' FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
				' WHERE id = ' . $this->db->quote($a_id, 'integer');

		// Execute query
		$result = $this->db->query($query);

		// Fetch row for returning
		if($row = $this->db->fetchAssoc($result))
		{
			// Create object out of fetched data and return it
			return new ilGeoLocation(
				$a_id,
				$row['title'],
				(float)$row['latitude'],
				(float)$row['longitude'],
				new DateTimeImmutable($row['expiration_timestamp'])
			);
		}

		throw new \InvalidArgumentException("Unknown id for geolocation: $a_id");
	}

	public function getGeoLocationsByCoordinates(float $a_latitude, float $a_longitude) : array
	{
		// Set up SQL-Statement
		$query = 'SELECT title, expiration_timestamp' .
				' FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
				' WHERE latitude = ' . $this->db->quote($a_latitude, 'float') .
				' AND longitude = ' . $this->db->quote($a_longitude, 'float');

		// Execute query
		$result = $this->db->query($query);

		// Fill array with all matching objects
		$locations = array();
		while($row = $this->db->fetchAssoc($result))
		{
			// Create object and add it to list
			$locations[] = new ilGeoLocation(
				(int)$row['id'],
				$row['title'],
				$a_latitude,
				$a_longitude,
				new DateTimeImmutable($row['expiration_timestamp'])
			);
		}

		// Return list of objects (might be empty if no object was found)
		return $locations;
	}

	public function ifGeoLocationExistsById(int $a_id) : bool
	{
		// Set up SQL-Statement
		$query = 'SELECT EXISTS(SELECT 1 FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
				' WHERE id = ' . $this->db->quote($a_id, 'integer') . ") AS count";

		// Execute statement
		$result = $this->db->query($query);

		// Return true if object was found
		return $result['count'] == 1;
	}

	public function ifAnyGeoLocationExistsByCoordinates(float $a_latitude, float $a_longitude) : bool
	{
		// Set up SQL-Statement
		$query = 'SELECT EXISTS(SELECT 1 FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
				 ' WHERE latitude = ' . $this->db->quote($a_latitude, 'float') .
				 ' AND longitude = ' . $this->db->quote($a_longitude, 'float') . ") AS count";
		// Execute statement
		$result = $this->db->query($query);

		// Return if any object was found
		return $result['count'] == 1;
	}

	public function updateGeoLocation(ilGeoLocation $a_obj)
	{
		// Update of one entire geo location object
		$this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
			// Update columns (in this case all except for id):
			array('title' => array($a_obj->getTitle(), 'text')),
			array('latitude' => array($a_obj->getLatitude(), 'float')),
			array('longitude' => array($a_obj->getLongitude(), 'float')),
			array('expiration_timestamp' => array($a_obj->getExpirationAsTimestamp(), 'timestamp')),
			// Where (in this case only the object with the given id):
			array('id' => array($a_obj->getId(), 'int'))
		);
	}

	public function updateGeoLocationTimestampByCoordinates(float $a_searched_latitude, float $a_searched_longitude, \DateTimeImmutable $a_update_timestamp)
	{
		// Update for single attribute of a set of geo location objects
		$this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
			// Update columns (in this case only the timestamp):
			array('expiration_timestamp' => array('timestamp', $a_update_timestamp->getTimestamp())),
			// Where (in this case every object on the given location):
			array('latitude' => array($a_searched_latitude, 'float'),
				  'longitude' => array($a_searched_longitude, 'float'))
		);
	}

	public function deleteGeoLocation(int $a_id)
	{
		// Set up delete query
		$query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
		' WHERE id = ' . $this->db->quote($a_id, 'integer');

		// Execute delete query
		$this->db->manipulate($query);
	}

	public function deleteGeoLocationsByCoordinates(float $a_latitude, float $a_longitude)
	{
		// Set up delete query
		$query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
			' WHERE latitude < ' . $this->db->quote($a_latitude, 'float') .
			' AND longitude = ' . $this->db->quote($a_longitude, 'float');

		// Execute delete query
		$this->db->manipulate($query);
	}

	public function deleteExpiredGeoLocations()
	{
		// Set up delete query
		$query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
			' WHERE expiration_timestamp < ' . $this->db->quote(time(), 'timestamp');

		// Execute delete query
		$this->db->manipulate($query);
	}
}
