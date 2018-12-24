<?php

class ilGeoLocationDBRepository implements ilGeoLocationRepository {

    public const TABLE_NAME = 'geo_location';
    protected $sql_table_name;

    public function __construct(\ilDBInterfacee $a_db)
    {
        $this->db = $a_db;
        $this->sql_table_name = $this->db->quoteIdentifier('geo_location');
    }

    public function createGeoLocation(ilObjGeoLocation $obj)
    {
        // Get next free id for object
        $id = $this->db->nextId($this->sql_table_name);

        // Insert in database
        $this->db->insert($this->sql_table_name, array(
            'id' => array('integer', $id),
            'title' => array('text', $obj->getTitle()),
            'lattitude' => array('float', $obj->getLattitude()),
            'longitude' => array('float', $obj->getLongitude()),
            'expiration_timestamp' => array('timestamp', $obj->getIExpirationTimestamp())
        ));

        return $id;
    }

    public function getGeoLocationById($a_id)
    {
        // Setup SQL-Statement
        $query = 'Select * FROM ' . $this->sql_table_name . ' WHERE id = ' . $this->db->quote($a_id, 'integer');

        // Execute query
        $result = $this->db->query($query);

        // Fetch row for returning
        if($row = $this->db->fetchAssoc($result))
        {
            // Create object out of fetched data and return it
            return new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp']);
        }
        else
        {
            // Return NULL if nothing was found (throw an exception is also a possiblity)
            return NULL;
        }
    }

    public function getGeoLocationsByCoordinates(string $a_lattitude, string $a_longitude) : array
    {
        // Setup SQL-Statement
        $query = 'Select * FROM ' . $this->sql_table_name . ' WHERE lattitude = ' . $this->db->quote($a_lattitude, 'float')
                    . ' AND longitude = ' . $this->db->quote($a_longitude, 'float');

        // Execute query
        $result = $this->db->query($query);

        // Fill array with all matching objects
        $locations = array();
        while($row = $this->db->fetchAssoc($result))
        {
            $locations[] = new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp']);
        }

        // Return list of objects (might be empty if no object was found)
        return $locations;
    }

    public function checkIfLocationExistsById(int $a_id) : bool
    {
        $query = 'Select count(*) AS count FROM ' . $this->sql_table_name . ' WHERE id = ' . $this->db->quote($a_id, 'integer');
        $result = $this->db->query($query);

        return $result['count'] > 0;
    }

    /**
     * In this example, the name extendGeoLocationExpirationTimestamp would be more beautiful but 
     * doesn't match the notation
     */
    public function updateGeoLocationExpirationTimestamp(int $a_id, int $a_new_expiration_timestamp)
    {
        $this->db->update($this->sql_table_name, 
            // Update row:
            array("expiration_timestamp" => array("timestamp", $a_new_expiration_timestamp)),
            // Where:
            array("id" => array("int", $a_id))
        );
    }

    /**
     * 
     */
    public function deleteGeoLocationById(int $a_id)
    {
        $this->db->manipulate('DELETE FROM ' . $this->sql_table_name . ' WHERE id = ' . $this->db->quote($a_id, 'integer'));
    }

    /**
     * 
     */
    public function purgeExpiredGeoLocations()
    {
        $this->db->manipulate('DELETE FROM ' . $this->sql_table_name . ' WHERE expiration_timestamp < ' . $this->db->quote(time(), 'timestamp'));
    }
}