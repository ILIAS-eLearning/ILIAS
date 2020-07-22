<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

class ilADTLocationSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected $radius; // [int]
    
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
    {
        return ($a_adt_def instanceof ilADTLocationDefinition);
    }
    
    
    // table2gui / filter
    
    public function loadFilter()
    {
        $value = $this->readFilter();
        if ($value !== null) {
            // :TODO:
        }
    }
    
    
    // form
    
    public function addToForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $adt = $this->getADT();
        
        $default = false;
        if ($adt->isNull()) {
            // see ilPersonalProfileGUI::addLocationToForm()
            
            // use installation default
            include_once("./Services/Maps/classes/class.ilMapUtil.php");
            $def = ilMapUtil::getDefaultSettings();
            $adt->setLatitude($def["latitude"]);
            $adt->setLongitude($def["longitude"]);
            $adt->setZoom($def["zoom"]);
            
            $default = true;
        }
        
        $optional = new ilCheckboxInputGUI($this->getTitle(), $this->addToElementId("tgl"));
        
        if (!$default && !$adt->isNull()) {
            $optional->setChecked(true);
        }
            
        $loc = new ilLocationInputGUI($lng->txt("location"), $this->getElementId());
        $loc->setLongitude($adt->getLongitude());
        $loc->setLatitude($adt->getLatitude());
        $loc->setZoom($adt->getZoom());
        $optional->addSubItem($loc);
            
        $rad = new ilNumberInputGUI($lng->txt("form_location_radius"), $this->addToElementId("rad"));
        $rad->setSize(4);
        $rad->setSuffix($lng->txt("form_location_radius_km"));
        $rad->setValue($this->radius);
        $rad->setRequired(true);
        $optional->addSubItem($rad);
                
        $this->addToParentElement($optional);
    }
    
    protected function shouldBeImportedFromPost($a_post)
    {
        return (bool) $a_post["tgl"];
    }
    
    public function importFromPost(array $a_post = null)
    {
        $post = $this->extractPostValues($a_post);
                
        if ($post && $this->shouldBeImportedFromPost($post)) {
            $tgl = $this->getForm()->getItemByPostVar($this->addToElementId("tgl"));
            $tgl->setChecked(true);
            
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setLongitude($post["longitude"]);
            $item->setLatitude($post["latitude"]);
            $item->setZoom($post["zoom"]);
            
            $this->radius = (int) $post["rad"];
                        
            $this->getADT()->setLongitude($post["longitude"]);
            $this->getADT()->setLatitude($post["latitude"]);
            $this->getADT()->setZoom($post["zoom"]);
        } else {
            // optional empty is valid
            $this->force_valid = true;
            
            $this->getADT()->setLongitude(null);
            $this->getADT()->setLatitude(null);
            $this->getADT()->setZoom(null);
            $this->radius = null;
        }
    }
        
    public function isValid()
    {
        return (parent::isValid() && ((int) $this->radius || (bool) $this->force_valid));
    }
    
    
    // bounding
    
    /**
     * Get bounding box for location circum search
     *
     * @param float $a_latitude
     * @param float $a_longitude
     * @param int $a_radius
     * @return array
     */
    protected function getBoundingBox($a_latitude, $a_longitude, $a_radius)
    {
        $earth_radius = 6371;
        
        // http://www.d-mueller.de/blog/umkreissuche-latlong-und-der-radius/
        $max_lat = $a_latitude + rad2deg($a_radius / $earth_radius);
        $min_lat = $a_latitude - rad2deg($a_radius / $earth_radius);
        $max_long = $a_longitude + rad2deg($a_radius / $earth_radius / cos(deg2rad($a_latitude)));
        $min_long = $a_longitude - rad2deg($a_radius / $earth_radius / cos(deg2rad($a_latitude)));

        return array(
            "lat" => array("min" => $min_lat, "max" => $max_lat)
            ,"long" => array("min" => $min_long, "max" => $max_long)
        );
    }

    
    // db
    
    public function getSQLCondition($a_element_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->isNull() && $this->isValid()) {
            $box = $this->getBoundingBox($this->getADT()->getLatitude(), $this->getADT()->getLongitude(), $this->radius);
                        
            $res = array();
            $res[] = $a_element_id . "_lat >= " . $ilDB->quote($box["lat"]["min"], "float");
            $res[] = $a_element_id . "_lat <= " . $ilDB->quote($box["lat"]["max"], "float");
            $res[] = $a_element_id . "_long >= " . $ilDB->quote($box["long"]["min"], "float");
            $res[] = $a_element_id . "_long <= " . $ilDB->quote($box["long"]["max"], "float");
                
            return "(" . implode(" AND ", $res) . ")";
        }
    }
    
    
    //  import/export
        
    public function getSerializedValue()
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array(
                "lat" => $this->getADT()->getLatitude()
                ,"long" => $this->getADT()->getLongitude()
                ,"zoom" => $this->getADT()->getZoom()
                ,"radius" => (int) $this->radius
            ));
        }
    }
    
    public function setSerializedValue($a_value)
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setLatitude($a_value["lat"]);
            $this->getADT()->setLongitude($a_value["long"]);
            $this->getADT()->setZoom($a_value["zoom"]);
            $this->radius = (int) $a_value["radius"];
        }
    }
}
