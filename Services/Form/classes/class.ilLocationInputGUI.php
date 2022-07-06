<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * This class represents a location property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLocationInputGUI extends ilFormPropertyGUI
{
    protected ilRbacSystem $rbacsystem;
    protected ?float $latitude = null;
    protected ?float $longitude = null;
    protected ?int $zoom = null;
    protected string $address = "";
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        parent::__construct($a_title, $a_postvar);
        $this->setType("location");
    }

    public function setLatitude(?float $a_latitude) : void
    {
        $this->latitude = $a_latitude;
    }

    public function getLatitude() : ?float
    {
        return $this->latitude;
    }

    public function setLongitude(?float $a_longitude) : void
    {
        $this->longitude = $a_longitude;
    }

    public function getLongitude() : ?float
    {
        return $this->longitude;
    }

    public function setZoom(?int $a_zoom) : void
    {
        $this->zoom = $a_zoom;
    }

    public function getZoom() : ?int
    {
        return $this->zoom;
    }

    public function setAddress(string $a_address) : void
    {
        $this->address = $a_address;
    }
    
    public function getAddress() : string
    {
        return $this->address;
    }

    public function setValueByArray(array $a_values) : void
    {
        $lat = (isset($a_values[$this->getPostVar()]["latitude"]) && $a_values[$this->getPostVar()]["latitude"] != "")
            ? (float) $a_values[$this->getPostVar()]["latitude"]
            : null;
        $lon = (isset($a_values[$this->getPostVar()]["longitude"]) && $a_values[$this->getPostVar()]["longitude"] != "")
            ? (float) $a_values[$this->getPostVar()]["longitude"]
            : null;
        $this->setLatitude($lat);
        $this->setLongitude($lon);
        $this->setZoom((int) $a_values[$this->getPostVar()]["zoom"]);
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        $val = $this->strArray($this->getPostVar());
        if ($this->getRequired() &&
            (trim($val["latitude"]) == "" || trim($val["longitude"]) == "")) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return true;
    }

    public function getInput() : array
    {
        $val = $this->strArray($this->getPostVar());
        return [
            "latitude" => (float) $val["latitude"],
            "longitude" => (float) $val["longitude"],
            "zoom" => (int) ($val["zoom"] ?? 0),
            "address" => ($val["address"] ?? "")
        ];
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $levels = [];
        
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
            ilLegacyFormElementsUtil::formSelect(
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
                ->setLatitude((string) $lat)
                ->setLongitude((string) $long)
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

    protected function geolocationAvailiable() : bool
    {
        switch (ilMapUtil::getType()) {
            case 'openlayers':
                return (bool) ilMapUtil::getStdGeolocationServer();
            case 'googlemaps':
                return true;
            default:
                return false;
        }
    }
}
