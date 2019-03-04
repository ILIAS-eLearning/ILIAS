<?php

/**
 * This code is just an example for the Repository Pattern! It is a basic interface to the 
 * 'ilGeoLocation*Repository'-classes
 * 
 * Classes, which implement this interface persist/read/update an object of the class
 * ilGeoLocation to/from either a database, a file or something else. Due to this conditions,
 * this interface is as abstract as possible and should not contain any database or filesystem 
 * specific methods.
 */
interface ilGeoLocationRepository {

    /**
     * Create a new geo location entry
     */
    public function createGeoLocation(array $obj) : ilGeoLocation;

    /**
     * Get a single geo location, identified by its id
     */
    public function getGeoLocationById(int $a_id) : ilGeoLocation;

    /**
     * Example for reading an array of geo locations which have a given attribute
     */
    public function getGeoLocationsByCoordinates(string $a_latitude, string $a_longitude) : array;

    /**
     * Example for checking if geo location with a certain id exists
     */
    public function checkIfGeoLocationExistsById(int $a_id) : bool;

    /**
     * Example for checking if a geo location (one or more) with a given attribute exists
     */
    public function checkIfAnyGeoLocationExistsByCoordinates(string $a_latitude, string $a_longitude) : bool;

    /**
     * Example for updating all attributes of a given geo location
     */
    public function updateGeoLocationObject(ilGeoLocation $a_obj);

    /**
     * Example for updating multiple objects at once
     */
    public function updateGeoLocationTimestampByCoordinates(string $a_searched_latitude, string $a_searched_longitude, int $a_update_timestamp);

    /**
     * Exaple for deleting single geo location identified by its id
     */
    public function deleteGeoLocationObject(int $a_id);

    /**
     * Example for a condition based deletion of multiple geo locations
     */
    public function purgeGeoLocationsByCoordinates(string $a_latitude, string $a_longitude);

    /**
     * Example for a condition based deletion of multiple geo locations
     */
    public function purgeExpiredGeoLocations();
}
