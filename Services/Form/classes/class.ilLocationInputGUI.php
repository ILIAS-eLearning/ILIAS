<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a location property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilLocationInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    protected $latitude;
    protected $longitude;
    protected $zoom;
    protected $address;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        parent::__construct($a_title, $a_postvar);
        $this->setType("location");
    }

    /**
    * Set Latitude.
    *
    * @param	real	$a_latitude	Latitude
    */
    public function setLatitude($a_latitude)
    {
        $this->latitude = $a_latitude;
    }

    /**
    * Get Latitude.
    *
    * @return	real	Latitude
    */
    public function getLatitude()
    {
        return $this->latitude;
    }



    /**
    * Set Longitude.
    *
    * @param	real	$a_longitude	Longitude
    */
    public function setLongitude($a_longitude)
    {
        $this->longitude = $a_longitude;
    }

    /**
    * Get Longitude.
    *
    * @return	real	Longitude
    */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
    * Set Zoom.
    *
    * @param	int	$a_zoom	Zoom
    */
    public function setZoom($a_zoom)
    {
        $this->zoom = $a_zoom;
    }

    /**
    * Get Zoom.
    *
    * @return	int	Zoom
    */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
    * Set Address.
    *
    * @param        string  $a_Address      Address
    */
    public function setAddress($a_address)
    {
        $this->address = $a_address;
    }
    
    /**
    * Get Address.
    *
    * @return       string  Address
    */
    public function getAddress()
    {
        return $this->address;
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setLatitude($a_values[$this->getPostVar()]["latitude"]);
        $this->setLongitude($a_values[$this->getPostVar()]["longitude"]);
        $this->setZoom($a_values[$this->getPostVar()]["zoom"]);
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()]["latitude"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["latitude"]);
        $_POST[$this->getPostVar()]["longitude"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["longitude"]);
        if ($this->getRequired() &&
            (trim($_POST[$this->getPostVar()]["latitude"]) == "" || trim($_POST[$this->getPostVar()]["longitude"]) == "")) {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        return true;
    }

    /**
    * Insert property html
    *
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        
        $lng->loadLanguageModule("maps");
        $tpl = new ilTemplate("tpl.prop_location.html", true, true, "Services/Form");
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TXT_ZOOM", $lng->txt("maps_zoom_level"));
        $tpl->setVariable("TXT_LATITUDE", $lng->txt("maps_latitude"));
        $tpl->setVariable("TXT_LONGITUDE", $lng->txt("maps_longitude"));
        $tpl->setVariable("LOC_DESCRIPTION", $lng->txt("maps_std_location_desc"));
        
        $lat = is_numeric($this->getLatitude())
            ? $this->getLatitude()
            : 0;
        $long = is_numeric($this->getLongitude())
            ? $this->getLongitude()
            : 0;
        $tpl->setVariable("PROPERTY_VALUE_LAT", $lat);
        $tpl->setVariable("PROPERTY_VALUE_LONG", $long);
        for ($i = 0; $i <= 18; $i++) {
            $levels[$i] = $i;
        }
        
        $map_id = "map_" . md5(uniqid());
        
        $tpl->setVariable(
            "ZOOM_SELECT",
            ilUtil::formSelect(
                $this->getZoom(),
                $this->getPostVar() . "[zoom]",
                $levels,
                false,
                true,
                0,
                "",
                array("id" => $map_id . "_zoom",
                "onchange" => "ilUpdateMap('" . $map_id . "');")
            )
        );
        $tpl->setVariable("MAP_ID", $map_id);
        $tpl->setVariable("ID", $this->getPostVar());

        // only show address input if geolocation url available
        // else, if admin: show warning.

        if ($this->geolocationAvailiable()) {
            $tpl->setVariable("TXT_ADDR", $lng->txt("address"));
            $tpl->setVariable("TXT_LOOKUP", $lng->txt("maps_lookup_address"));
            $tpl->setVariable("TXT_ADDRESS", $this->getAddress());
            $tpl->setVariable("MAP_ID_ADDR", $map_id);
            $tpl->setVariable("POST_VAR_ADDR", $this->getPostVar());
        } else {
            if ($rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {
                $tpl->setVariable("TEXT", $lng->txt("configure_geolocation"));
            }
        }

        $map_gui = ilMapUtil::getMapGUI();
        $map_gui->setMapId($map_id)
                ->setLatitude($lat)
                ->setLongitude($long)
                ->setZoom($this->getZoom())
                ->setEnableTypeControl(true)
                ->setEnableLargeMapControl(true)
                ->setEnableUpdateListener(true)
                ->setEnableCentralMarker(true);

        $tpl->setVariable("MAP", $map_gui->getHtml());
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Is geolocation configured?
    * @return bool
    */
    protected function geolocationAvailiable()
    {
        switch (ilMapUtil::getType()) {
            case 'openlayers':
                return ilMapUtil::getStdGeolocationServer() ? true : false;
            case 'googlemaps':
                return true;
            default:
                return false;
        }
    }
}
