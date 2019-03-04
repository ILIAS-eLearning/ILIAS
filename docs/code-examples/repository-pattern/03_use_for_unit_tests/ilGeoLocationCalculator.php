<?

class ilGeoLocationCalculator
{
    /** @var ilGeoLocationRepository */
    protected $geo_repository;

    public function __construct(ilGeoLocationRepository $a_geo_repository)
    {
        $this->geo_repository = $a_geo_repository;
    }

    public function calculateTimeTillNextExpiredGeoLocation(ilObjGeoLocation $given_geolocation)
    {
        $geo_locations = $this->geo_repository->getGeoLocationsByCoordinates("48° 52' 0\" N","2° 20' 0\" E");

        foreach($geo_locations as $geo_location)
        {
            if($given_geolocation <= $geo_location)
                return $geo_location;
        }
    }
}