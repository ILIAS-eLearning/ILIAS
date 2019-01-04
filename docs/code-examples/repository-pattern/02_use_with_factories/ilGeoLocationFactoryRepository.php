<?php

class ilGeoLocationFactoryRepository {

    public function __construct(ilDB $a_db)
    {
    }

    public function createGeoLocation(ilObjGeoLocation $obj)
    {
    }

    public function getGeoLocationById($a_id) : ilObjGeoLocation
    {
    }

    public function getGeoLocationsByCoordinates($a_lattitude, $a_longitude) : array
    {
    }

    public function checkIfLocationExistsById($a_id) : bool
    {
    }

    public function updateGeoLocationExpirationTimestamp($a_id, $a_new_expiration_timestamp)
    {
    }

    public function deleteGeoLocationById($a_id)
    {
    }

    public function purgeExpiredGeoLocations()
    {
    }
}
