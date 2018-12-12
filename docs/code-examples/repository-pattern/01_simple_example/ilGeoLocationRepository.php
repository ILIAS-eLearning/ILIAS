<?php

class ilGeoLocationRepository {

    public const $table = 'geo_location';

    public function __construct(ilDB $a_db)
    {
        $this->db = $a_db;
    }

    public function createGeoLocation(ilObjGeoLocation $obj)
    {
        $this->db->insert($this->table, array(
            'id' => array('integer', $obj->getId()),
            'title' => array('text', $obj->getId()),
            'lattitude' => array('float', $obj->getId()),
            'longitude' => array('float', $obj->getId()),
            'expiration_timestamp' => array('timestamp', $obj->getId())
        ));
    }

    public function getGeoLocationById($a_id)
    {
        $query = "Select * FROM $this->table WHERE id = " . $this->db->quote($a_id, 'integer');
        $result = $this->db->query($query);

        if($row = $this->db->fetchAssoc($result))
        {
            return new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp'])
        }
        else
        {
            return NULL;
        }
    }

    public function getGeoLocationsByCoordinates($a_lattitude, $a_longitude)
    {
        $query = "Select * FROM $this->table WHERE lattitude = " . $this->db->quote($a_lattitude, 'float')
                    . ' AND longitude = ' . $this->db->quote($a_longitude, 'float');
        $result = $this->db->query($query);

        $locations = array();
        while($row = $this->db->fetchAssoc($result))
        {
            $locations[] = new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp'])
        }

        return $locations;
    }

    public function checkIfLocationExistsById($a_id) : bool
    {
        $query = "Select count(*) AS count FROM $this->table WHERE id = " . $this->db->quote($a_id, 'integer');
        $result = $this->db->query($query);

        return $result['count'] > 0;
    }

    /**
     * The name extendGeoLocationExpirationTimestamp would be more beautiful but doesn't match the notation
     */
    public function updateGeoLocationExpirationTimestamp($a_id, $a_new_expiration_timestamp)
    {
        $this->db->update($this->table, 
            // Update row:
            array("expiration_timestamp" => array("timestamp", $a_new_expiration_timestamp)),
            // Where:
            array("id" => array("int", $a_id))
        );
    }

    /**
     * 
     */
    public function deleteGeoLocationById($a_id)
    {
        $this->db->manipulate("DELETE FROM $this->table WHERE id = " . $this->db->quote($a_id, 'integer'));
    }

    /**
     * 
     */
    public function purgeExpiredGeoLocations()
    {
        $this->db->manipulate("DELETE FROM $this->table WHERE expiration_timestamp < " . $this->db->quote(time(), 'timestamp'));
    }
}