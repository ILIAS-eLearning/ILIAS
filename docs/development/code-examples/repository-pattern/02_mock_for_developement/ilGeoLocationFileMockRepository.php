<?php

class ilGeoLocationFileMockRepository implements ilGeoLocationRepository
{

    /**
     * Create a new geo location entry
     */
    public function createGeoLocation(
        string $a_title,
        float $a_latitude,
        float $a_longitude,
        \DateTimeImmutable $a_expiration_timestamp
    ) : ilGeoLocation {
        // Generate a random object id. This should just work fine for development
        $generated_id = rand(1, 100000);
        $file = fopen('mocked_geolocation_data.txt', FILE_APPEND);

        // Create a csv-string for object data for the file
        $write_string = "$generated_id;$a_title;$a_latitude;$a_longitude;$a_expiration_timestamp\n";

        // Write new object to file
        fwrite($file, $write_string);
        fclose($file);

        return new ilGeoLocation(
            $generated_id,
            $a_title,
            $a_latitude,
            $a_longitude,
            $a_expiration_timestamp
        );
    }

    /**
     * Get a single geo location, identified by its id
     */
    public function getGeoLocationById(int $a_id) : ilGeoLocation
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        // Go line by line through the file and return object if it was found
        while ($row = fgetcsv($file)) {
            if ($row[0] == $a_id) {
                return new ilGeoLocation((int) $a_id, $row[1], (float) $row[2], (float) $row[3], new DateTimeImmutable($row[4]));
            }
        }

        throw new \InvalidArgumentException("Unknown id for geolocation: $a_id");
    }

    /**
     * Example for reading an array of geo locations which have a given attribute
     */
    public function getGeoLocationsByCoordinates(float $a_latitude, float $a_longitude) : array
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = array();

        // Go line by line through the file and add searched objects to list
        while ($row = fgetcsv($file)) {
            if ($row[2] == $a_latitude && $row[3] == $a_longitude) {
                $geo_locations[] = new ilGeoLocation((int) $row[0], $row[1], (float) $row[2], (float) $row[3], new DateTimeImmutable($row[4]));
            }
        }

        return $geo_locations;
    }

    /**
     * Example for checking if a geo location (one or more) with a given attribute exists
     */
    public function ifGeoLocationExistsById(int $a_id) : bool
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        // Go line by line through the file and search for given id
        while ($row = fgetcsv($file)) {
            if ($row[0] == $a_id) {
                return true;
            }
        }

        return false;
    }


    /**
     * Example for checking if a geo location (one or more) with a given attribute exists
     */
    public function ifAnyGeoLocationExistsByCoordinates(float $a_latitude, float $a_longitude) : bool
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        // Go line by line through the file and search for given attributes
        while ($row = fgetcsv($file)) {
            if ($row[2] == $a_latitude && $row[3] == $a_longitude) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update all attributes of a given geo location
     */
    public function updateGeoLocation(ilGeoLocation $a_obj)
    {
        // Read entire file
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = $this->readFileAndReturnAsList($file);
        fclose($file);

        // Update searched object in list
        foreach ($geo_locations as $key => $row) {
            if ($row[0] == $a_obj->getId()) {
                $row[1] = $a_obj->getTitle();
                $row[2] = $a_obj->getLatitude();
                $row[3] = $a_obj->getLongitude();
                $row[4] = $a_obj->getExpirationAsTimestamp();
            }
        }

        // Write back all geo locations to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        $this->writeGeoLocationListToFile($file, $geo_locations);
        fclose($file);
    }

    /**
     * Example for updating multiple objects at once
     */
    public function updateGeoLocationTimestampByCoordinates(float $a_searched_latitude, float $a_searched_longitude, \DateTimeImmutable $a_update_timestamp)
    {
        // Read entire file
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = $this->readFileAndReturnAsList($file);
        fclose($file);

        // Update searched objects in list
        foreach ($geo_locations as $key => $row) {
            if ($row[2] == $a_searched_latitude && $row[3] == $a_searched_longitude) {
                $row[4] = $a_update_timestamp->getTimestamp();
            }
        }

        // Write back all geo locations to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        $this->writeGeoLocationListToFile($file, $geo_locations);
        fclose($file);
    }

    /**
     * Delete single geo location identified by its id
     */
    public function deleteGeoLocation(int $a_id)
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = $this->readFileAndReturnAsList($file);
        fclose($file);

        // Delete searched object from list
        foreach ($geo_locations as $key => $row) {
            // Check if current row has searched id
            if ($row[0] == $a_id) {
                unset($geo_locations[$key]);
            }
        }

        // Write back all geo locations to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        $this->writeGeoLocationListToFile($file, $geo_locations);
        fclose($file);
    }

    public function deleteGeoLocationsByCoordinates(float $a_latitude, float $a_longitude)
    {
        // Read all geo locations from file
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = $this->readFileAndReturnAsList($file);
        fclose($file);

        // Filter out expired objects
        $now = new DateTimeImmutable();
        foreach ($geo_locations as $key => $row) {
            // Check if current row has searched attributes
            if ($row[2] == $a_latitude && $row[3] == $a_longitude) {
                unset($geo_locations[$key]);
            }
        }

        // Write objects back to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        $this->writeGeoLocationListToFile($file, $geo_locations);
        fclose($file);
    }

    /**
     * Example for a condition based deletion of multiple geo locations
     */
    public function deleteExpiredGeoLocations()
    {
        // Read all geo locations from file
        $file = fopen('mocked_geolocation_data.txt', 'r');
        $geo_locations = $this->readFileAndReturnAsList($file);
        fclose($file);

        // Filter out expired objects
        $now = new DateTimeImmutable();
        foreach ($geo_locations as $key => $row) {
            // Check if current row contains an expired timestamp
            // Note: This is an example but it needs to be tested if differences can be calculated like this
            $dt = new DateTimeImmutable($row[4]);
            if ($now->diff($dt)->s >= 0) {
                unset($geo_locations[$key]);
            }
        }

        // Write objects back to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        $this->writeGeoLocationListToFile($file, $geo_locations);
        fclose($file);
    }

    /**
     * Protected function. Just for development purpose
     */
    protected function readFileAndReturnAsList($file)
    {
        $geo_locations = array();
        while ($row = fgetcsv($file)) {
            $geo_locations[] = $row;
        }
        return $geo_locations;
    }

    /**
     * Protected function. Just for development purpose
     */
    protected function writeGeoLocationListToFile($file, $list)
    {
        foreach ($list as $obj_data) {
            // implode(';', $list) . "\n"; // <- this way might also work
            $write_string = $obj_data[0] . ';' . $obj_data[1] . ';' . $obj_data[2] . ';' . $obj_data[3] . ';' . $obj_data[4] . "\n";
            fwrite($file, $write_string . "\n");
        }
    }
}
