<?php

class ilGeoLocationRepository extends ActiveRecord {

    public function createGeoLocation(ilObjGeoLocation $obj)
    {
    }

    public function getGeoLocationById($a_id)
    {
    }

    public function getGeoLocationsByCoordinates($a_lattitude, $a_longitude)
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