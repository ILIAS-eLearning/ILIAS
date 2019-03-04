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
	public function createGeoLocation(
		string $a_title,
		float $a_latitude,
		float $a_longitude,
		\DateTimeImmutable $a_expiration_timestamp
	) : ilGeoLocation;

	/**
	 * Get a single geo location, identified by its id
	 */
	public function getGeoLocationById(int $a_id) : ilGeoLocation;

	/**
	 * Example for reading an array of geo locations which have a given attribute
	 *
	 * @return ilGeoLocation[]
	 */
	public function getGeoLocationsByCoordinates(float $a_latitude, float $a_longitude) : array;

	/**
	 * Example for checking if geo location with a certain id exists
	 */
	public function ifGeoLocationExistsById(int $a_id) : bool;

	/**
	 * Example for checking if a geo location (one or more) with a given attribute exists
	 */
	public function ifAnyGeoLocationExistsByCoordinates(float $a_latitude, float $a_longitude) : bool;

	/**
	 * Example for updating all attributes of a given geo location
	 */
	public function updateGeoLocation(ilGeoLocation $a_obj);

	/**
	 * Example for updating multiple objects at once
	 */
	public function updateGeoLocationTimestampByCoordinates(float $a_searched_latitude, float $a_searched_longitude, \DateTimeImmutable $a_update_timestamp);

	/**
	 * Exaple for deleting single geo location identified by its id
	 */
	public function deleteGeoLocation(int $a_id);

	/**
	 * Example for a condition based deletion of multiple geo locations
	 */
	public function deleteGeoLocationsByCoordinates(float $a_latitude, float $a_longitude);

	/**
	 * Example for a condition based deletion of multiple geo locations
	 */
	public function deleteExpiredGeoLocations();
}
