<?php

class ilObjGeoLocation {

    protected $id;
    protected $title;
    protected $lattitude;
    protected $longitude;
    protected $expiration_timestamp;

    public function __construct($a_id, $a_title, $a_lattitude, $a_longitude, $a_expiration_timestamp)
    {
        $this->id = $a_id;
        $this->title = $a_title;
        $this->lattitude = $lattitude;
        $this->longitude = $longitude;
        $this->expiration_timestamp = $a_expiration_timestamp;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getLattitude()
    {
        return $this->lattitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getExpirationTimestamp()
    {
        return $this->getExpirationTimestamp;
    }
}