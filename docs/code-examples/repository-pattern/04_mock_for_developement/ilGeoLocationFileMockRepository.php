<?

class ilGeoLocationFileMockRepository implements ilGeoLocationRepository
{

    /**
     * Create a new geo location entry
     */
    public function createGeoLocation(ilObjGeoLocation $obj)
    {
        $file = fopen('mocked_geolocation_data.txt', FILE_APPEND);
        $write_string = $obj->getId().';'.$obj->getTitle().';'.$obj->getLattitude().';'.$obj->getLongitude().';'.$obj->getExpirationAsTimestamp();
        fwrite($file, $write_string . "\n");
        fclose($obj);
    }

    /**
     * Get a singel geo location, identified by its id
     */
    public function getGeoLocationById(int $a_id)
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        while($row = fgetcsv($file))
        {
            if($row[0] == $a_id)
            {
                return new ilObjGeoLocation($a_id, $row[1], $row[2], $row[3], new DateTimeImmutable($row[4]));
            }
        }

        return null;
    }

    /**
     * Example for reading an array of geo locations which have a given attribute
     */
    public function getGeoLocationsByCoordinates(string $a_lattitude, string $a_longitude)
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        $geo_locations = array();
        while($row = fgetcsv($file))
        {
            if($row[2] == $a_lattitude && $row[3] == $a_longitude)
            {
                $geo_locations[] = new ilObjGeoLocation($row[0], $row[1], $row[2], $row[3], new DateTimeImmutable($row[4]));
            }
        }

        return $geo_locations;
    }

    /**
     * Example for checking if a geo location (one or more) with a given attribute exists
     */
    public function checkIfLocationExistsById(int $a_id): bool
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        while($row = fgetcsv($file))
        {
            if($row[2] == $a_lattitude && $row[3] == $a_longitude)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Update all attributes of a given geo location
     */
    public function updateGeoLocationObject(ilObjGeoLocation $a_obj)
    {
        // TODO: Implement updateGeoLocationObject() method.
    }

    /**
     * Delete single geo location identified by its id
     */
    public function deleteGeoLocationById(int $a_id)
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        $geo_locations = array();
        while($geo_locations[] = fgetcsv($file));

        foreach($geo_locations as $key => $row)
        {
            if($row[0] == $a_id)
            {
                unset($geo_locations[$key]);
            }
        }
        fclose($file);

        $file = fopen('mocked_geolocation_data.txt', 'w');
        foreach($geo_locations as $row)
        {
            fwrite($file, implode(';', $row) . "\n");
        }
        fclose($file);
    }

    /**
     * Example for a condition based deletion of multiple geo locations
     */
    public function purgeExpiredGeoLocations()
    {
        $file = fopen('mocked_geolocation_data.txt', 'r');

        // Read all geo locations from file
        $geo_locations = array();
        while($geo_locations[] = fgetcsv($file));

        // Filter out expired objects
        $now = new DateTimeImmutable();
        foreach($geo_locations as $key => $row)
        {
            // TODO: This is an example but it needs to be tested if differences can be calculated like this
            $dt = new DateTimeImmutable($row[4]);
            if($now->diff($dt)->s >= 0)
            {
                unset($geo_locations[$key]);
            }
        }
        fclose($file);

        // Write objects back to file
        $file = fopen('mocked_geolocation_data.txt', 'w');
        foreach($geo_locations as $row)
        {
            fwrite($file, implode(';', $row) . "\n");
        }
        fclose($file);
    }
}
