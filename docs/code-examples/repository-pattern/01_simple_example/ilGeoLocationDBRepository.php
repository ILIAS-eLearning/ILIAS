<?php

class ilGeoLocationDBRepository implements ilGeoLocationRepository {

    public const TABLE_NAME = 'geo_location';

    public function __construct(\ilDBInterfacee $a_db)
    {
        $this->db = $a_db;
    }

    public function createGeoLocation(array $obj_data) : ilGeoLocation
    {
        // Get next free id for object
        $id = $this->db->nextId($this->db->quoteIdentifier(self::TABLE_NAME));

        // Insert in database
        $this->db->insert($this->db->quoteIdentifier(self::TABLE_NAME), array(
            'id' => array('integer', $id),
            'title' => array('text', $obj_data['title']),
            'latitude' => array('float', $obj_data['latitude'],
            'longitude' => array('float', $obj_data['longitude']),
            'expiration_timestamp' => array('timestamp', $obj_data['expirationAsTimestamp'])
        )));

        // Return the new created object or just the id
        return new ilGeoLocation($id,
                                    $obj_data['title'],
                                    $obj_data['latitude'],
                                    $obj_data['longitude'],
                                    $obj_data['expiration_timestamp']);
    }

    public function getGeoLocationById(int $a_id) : ilGeoLocation
    {
        // Set up SQL-Statement
        $query = 'Select * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
                 ' WHERE id = ' . $this->db->quote($a_id, 'integer');

        // Execute query
        $result = $this->db->query($query);

        // Fetch row for returning
        if($row = $this->db->fetchAssoc($result))
        {
            // Create object out of fetched data and return it
            return new ilGeoLocation($row['id'],
                                        $row['title'],
                                        $row['latitude'],
                                        $row['longitude'],
                                        new DateTimeImmutable($row['expiration_timestamp']));
        }
        else
        {
            // Return NULL if nothing was found (throw an exception is also a possibility)
            return NULL;
        }
    }

    public function getGeoLocationsByCoordinates(string $a_latitude, string $a_longitude) : array
    {
        // Set up SQL-Statement
        $query = 'Select * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
                 ' WHERE latitude = ' . $this->db->quote($a_latitude, 'float') .
                 ' AND longitude = ' . $this->db->quote($a_longitude, 'float');

        // Execute query
        $result = $this->db->query($query);

        // Fill array with all matching objects
        $locations = array();
        while($row = $this->db->fetchAssoc($result))
        {
            // Create object and add it to list
            $locations[] = new ilGeoLocation($row['id'],
                                                $row['title'],
                                                $row['latitude'],
                                                $row['longitude'],
                                                new DateTimeImmutable($row['expiration_timestamp']));
        }

        // Return list of objects (might be empty if no object was found)
        return $locations;
    }

    public function checkIfGeoLocationExistsById(int $a_id) : bool
    {
        // Set up SQL-Statement
        $query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
            ' WHERE id = ' . $this->db->quote($a_id, 'integer');

        // Execute statement
        $result = $this->db->query($query);

        // Return if object was found
        return $result['count'] > 0;
    }

    public function checkIfAnyGeoLocationExistsByCoordinates(string $a_latitude, string $a_longitude) : bool
    {
        // Set up SQL-Statement
        $query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
                 ' WHERE latitude = ' . $this->db->quote($a_latitude, 'text') .
                 ' AND longitude = ' . $this->db->quote($a_longitude, 'text');

        // Execute statement
        $result = $this->db->query($query);

        // Return if any object was found
        return $result['count'] > 0;
    }

    public function updateGeoLocationObject(ilGeoLocation $a_obj)
    {
        // Update of one entire geo location object
        $this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
            // Update columns (in this case all except for id):
            array('title' => array($a_obj->getTitle(), 'text')),
            array('latitude' => array($a_obj->getLatitude(), 'text')),
            array('longitude' => array($a_obj->getLongitude(), 'text')),
            array('expiration_timestamp' => array($a_obj->getExpirationAsTimestamp(), 'timestamp')),
            // Where (in this case only the object with the given id):
            array('id' => array($a_obj->getId(), 'int'))
        );
    }

    public function updateGeoLocationTimestampByCoordinates(string $a_searched_latitude, string $a_searched_longitude, int $a_update_timestamp)
    {
        // Update for single attribute of a set of geo location objects
        $this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
            // Update columns (in this case only the timestamp):
            array('expiration_timestamp' => array('timestamp', $a_update_timestamp)),
            // Where (in this case every object on the given location):
            array('latitude' => array($a_searched_latitude, 'latitude'),
                  'longitude' => array($a_searched_longitude, 'longitude'))
        );
    }

    public function deleteGeoLocationObject(int $a_id)
    {
        // Set up delete query
        $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
        ' WHERE id = ' . $this->db->quote($a_id, 'integer');

        // Execute delete query
        $this->db->manipulate($query);
    }

    public function purgeGeoLocationsByCoordinates(string $a_latitude, string $a_longitude)
    {
        // Set up delete query
        $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
            ' WHERE latitude < ' . $this->db->quote($a_latitude, 'text') .
            ' AND longitude = ' . $this->db->quote($a_longitude, 'text');

        // Execute delete query
        $this->db->manipulate($query);
    }

    public function purgeExpiredGeoLocations()
    {
        // Set up delete query
        $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
            ' WHERE expiration_timestamp < ' . $this->db->quote(time(), 'timestamp');

        // Execute delete query
        $this->db->manipulate($query);
    }
}
