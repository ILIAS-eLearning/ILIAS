<?php

class ilGeoLocationAR extends ActiveRecord {

    const TABLE_NAME = 'geo_location';

    static function returnDbTableName()
    {
        ilGeoLocationRepository::TABLE_NAME;
    }
    
    public function __construct($a_id, $a_title, $a_lattitude, $a_longitude, $a_expiration_timestamp)
    {
        $this->id = $a_id;
        $this->title = $a_title;
        $this->lattitude = $lattitude;
        $this->longitude = $longitude;
        $this->expiration_timestamp = $a_expiration_timestamp;
    }

    /**
     * @var integer 
     * @con_is_primary true
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length 4
     */
    protected $id;

    /**
     * @var string 
     * @con_has_field true
     * @con_fieldtype text
     * @con_length 255
     */
    protected $title;

    /**
     * @var string 
     * @con_has_field true
     * @con_fieldtype text
     * @con_length 255
     */
    protected $lattitude;

    /**
     * @var string 
     * @con_has_field true
     * @con_fieldtype text
     * @con_length 255
     */
    protected $longitude;

    /**
     * @var integer 
     * @con_is_primary false
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    protected $expiration_timestamp;

    
    public function getId()
    {
        return $this->id;
    }

    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    public function getLattitude()
    {
        return $this->lattitude;
    }

    public function setLattitude($a_lattitude)
    {
        $this->lattitude = $a_lattitude;
    }
    
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($a_longitude)
    {
        $this->longitude = $a_longitude;
    }
    
    public function getExpirationTimestamp()
    {
        return $this->getExpirationTimestamp;
    }

    public function setExpirationTimestamp($a_expiration_timestamp)
    {
        $this->expiration_timestamp = $a_expiration_timestamp;
    }
}