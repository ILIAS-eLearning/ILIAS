<?php

/**
 * This code is just an example for the Repository Pattern! It is a basic interface to the 
 * 'ilGeoLocation*Repository'-classes
 * 
 * Classes, which implement this interface persist/read/update an object of the class
 * ilObjGeoLocation to/from either a database, a file or something else. Due to this conditions,
 * this interface is as abstract as possible and should not contain any database or filesystem 
 * specific methods.
 */
interface ilGeoLocationRepository {

    /**
     * Create a new geo location entry
     */
    public function createGeoLocation(ilObjGeoLocation $obj);

    /**
     * Get a singel geo location, identified by its id
     */
    public function getGeoLocationById(int $a_id);

    /**
     * Example for reading an array of geo locations which have a given attribute
     */
    public function getGeoLocationsByCoordinates(string $a_lattitude, string $a_longitude);

    /**
     * Example for checking if a geo location (one or more) with a given attribute exists
     */
    public function checkIfLocationExistsById(int $a_id) : bool;

    /**
     * Update all attributes of a given geo location
     */
    public function updateGeoLocationObject(ilObjGeoLocation $a_obj);

    /**
     * Delete single geo location identified by its id
     */
    public function deleteGeoLocationById(int $a_id);

    /**
     * Example for a condition based deletion of multiple geo locations
     */
    public function purgeExpiredGeoLocations();
}