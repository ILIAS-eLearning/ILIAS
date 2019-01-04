<?

class ilGeoLocationObjectFactory {

    public static function createGeoLocationForCountry(array $raw_geolocation)
    {
        $location_information = $this->calculateLocationInformation($row['lattitude'], $row['longitude']);

        $obj_geolocation = new ilObjSpecificGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp']);
        $obj_geolocation->setCountry($location_information['country']);
        $obj_geolocation->setCity($location_information['city']);
        $obj_geolocation->setCity($location_information['street']);

        return $obj_geolocation;
    }
}
