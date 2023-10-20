<?php

class ilGeoLocationCalculator
{
    /** @var ilGeoLocationRepository */
    protected $geo_repository;

    public function __construct(ilGeoLocationRepository $a_geo_repository)
    {
        $this->geo_repository = $a_geo_repository;
    }

    public function calculateTimeTillNextExpiredGeoLocationAt(float $latitude, float $longitude)
    {
        $geo_locations = $this->geo_repository->getGeoLocationsByCoordinates($latitude, $longitude);

        if (count($geo_locations) === 0) {
            return null;
        }

        $current = array_shift($geo_locations);

        foreach ($geo_locations as $geo_location) {
            if ($current->getExpirationAsTimestamp() >= $geo_location->getExpirationAsTimestamp()) {
                $current = $geo_location;
            }
        }

        return $geo_location;
    }
}
