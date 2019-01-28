<?php

class ilObjGeoLocation {

    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $latitude;

    /** @var string */
    protected $longitude;

    /** @var DateTimeImmutable */
    protected $expiration_timestamp;

    public function __construct(int $a_id,
                                string $a_title, 
                                string $a_latitude,
                                string $a_longitude,
                                \DateTimeImmutable $a_expiration_timestamp)
    {
        $this->id = $a_id;
        $this->title = $a_title;
        $this->latitude = $a_latitude;
        $this->longitude = $a_longitude;
        $this->expiration_timestamp = $a_expiration_timestamp;
    }

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getTitle()
    {
        return $this->title;
    }

    /** @return string */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /** @return string */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /** @return \float */
    public function getExpirationAsTimestamp()
    {
        return $this->expiration_timestamp->getTimestamp();
    }

    /** @return \DateTimeImmutable */
    public function getExpirationAsImmutableDateTime()
    {
        return $this->expiration_timestamp;
    }
}