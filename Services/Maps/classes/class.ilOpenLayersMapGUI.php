<?php declare(strict_types=1);

/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * User interface class for OpenLayers maps
 */
class ilOpenLayersMapGUI extends ilMapGUI
{
    protected string $css_row;
    protected string $tile_server;
    protected ?string $geolocation_server;

    public function __construct()
    {
        parent::__construct();

        $this->css_row = "";
        $this->tile_server = "";
        $this->geolocation_server = "";
    }

    public function getTileServers() : string
    {
        return  $this->tile_server;
    }

    public function setTileServers(string $tile) : ilOpenLayersMapGUI
    {
        $this->tile_server = $tile;
        return $this;
    }

    public function getGeolocationServer() : ?string
    {
        return $this->geolocation_server;
    }

    public function setGeolocationServer(?string $geolocation) : ilOpenLayersMapGUI
    {
        $this->geolocation_server = $geolocation;
        return $this;
    }

    public function getHtml() : string
    {
        $html_tpl = new ilTemplate(
            "tpl.openlayers_map.html",
            true,
            true,
            "Services/Maps"
        );

        $js_tpl = new ilTemplate(
            "tpl.openlayers_map.js",
            true,
            true,
            "Services/Maps"
        );

        $this->lng->loadLanguageModule("maps");
        $this->tpl->addCss("node_modules/ol/ol.css");
        $this->tpl->addCss("Services/Maps/css/service_openlayers.css");
        $this->tpl->addJavaScript("Services/Maps/js/dist/ServiceOpenLayers.js");

        // add user markers
        $cnt = 0;
        foreach ($this->user_marker as $user_id) {
            if (ilObject::_exists($user_id)) {
                $user = new ilObjUser($user_id);
                if ($user->getLatitude() != 0 && $user->getLongitude() != 0 &&
                    $user->getPref("public_location") == "y") {
                    $js_tpl->setCurrentBlock("user_marker");
                    $js_tpl->setVariable(
                        "UMAP_ID",
                        $this->getMapId()
                    );
                    $js_tpl->setVariable("CNT", $cnt);

                    $js_tpl->setVariable("ULAT", htmlspecialchars($user->getLatitude()));
                    $js_tpl->setVariable("ULONG", htmlspecialchars($user->getLongitude()));
                    $info = htmlspecialchars($user->getFirstName() . " " . $user->getLastName());
                    $delim = "<br />";
                    if ($user->getPref("public_institution") == "y") {
                        $info .= $delim . htmlspecialchars($user->getInstitution());
                        $delim = ", ";
                    }
                    if ($user->getPref("public_department") == "y") {
                        $info .= $delim . htmlspecialchars($user->getDepartment());
                    }
                    $delim = "<br />";
                    if ($user->getPref("public_street") == "y") {
                        $info .= $delim . htmlspecialchars($user->getStreet());
                    }
                    if ($user->getPref("public_zip") == "y") {
                        $info .= $delim . htmlspecialchars($user->getZipcode());
                        $delim = " ";
                    }
                    if ($user->getPref("public_city") == "y") {
                        $info .= $delim . htmlspecialchars($user->getCity());
                    }
                    $delim = "<br />";
                    if ($user->getPref("public_country") == "y") {
                        $info .= $delim . htmlspecialchars($user->getCountry());
                    }
                    $js_tpl->setVariable(
                        "USER_INFO",
                        $info
                    );
                    $js_tpl->setVariable(
                        "IMG_USER",
                        $user->getPersonalPicturePath("xsmall")
                    );
                    $js_tpl->parseCurrentBlock();
                    $cnt++;
                }
            }
        }

        $html_tpl->setVariable("MAP_ID", $this->getMapId());
        $html_tpl->setVariable("WIDTH", $this->getWidth());
        $html_tpl->setVariable("HEIGHT", $this->getHeight());

        $js_tpl->setVariable("MAP_ID", $this->getMapId());
        $js_tpl->setVariable("LAT", $this->getLatitude());
        $js_tpl->setVariable("LONG", $this->getLongitude());
        $js_tpl->setVariable("ZOOM", (int) $this->getZoom());

        $nav_control = $this->getEnableNavigationControl()
            ? "true"
            : "false";
        $js_tpl->setVariable("NAV_CONTROL", $nav_control);
        $central_marker = $this->getEnableCentralMarker()
            ? "true"
            : "false";
        $js_tpl->setVariable("CENTRAL_MARKER", $central_marker);
        $replace_marker = $this->getEnableUpdateListener()
            ? "true"
            : "false";
        $js_tpl->setVariable("REPLACE_MARKER", $replace_marker);

        $tile_servers = $this->getTileServers();
        $tile_servers = explode(" ", $tile_servers);
        array_walk($tile_servers, function (&$string) {
            $string = '"' . $string . '"';
        });
        $tile_servers = '[' . implode(', ', $tile_servers) . ']';

        $js_tpl->setVariable("TILES", $tile_servers);
        $js_tpl->setVariable("GEOLOCATION", $this->getGeolocationServer());
        $js_tpl->setVariable("INVALID_ADDRESS_STRING", $this->lng->txt("invalid_address"));

        $this->tpl->addOnLoadCode($js_tpl->get());

        return $html_tpl->get();
    }

    /**
     * Get User List HTML (to be displayed besides the map)
     */
    public function getUserListHtml() : string
    {
        $list_tpl = new ilTemplate(
            "tpl.openlayers_map_user_list.html",
            true,
            true,
            "Services/Maps"
        );

        $cnt = 0;
        foreach ($this->user_marker as $user_id) {
            if (ilObject::_exists($user_id)) {
                $user = new ilObjUser($user_id);
                $this->css_row = ($this->css_row != "tblrow1_mo")
                    ? "tblrow1_mo"
                    : "tblrow2_mo";
                if ($user->getLatitude() != 0 && $user->getLongitude() != 0
                    && $user->getPref("public_location") == "y") {
                    $list_tpl->setCurrentBlock("item");
                    $list_tpl->setVariable("MARKER_CNT", $cnt);
                    $list_tpl->setVariable("MAP_ID", $this->getMapId());
                    $cnt++;
                } else {
                    $list_tpl->setCurrentBlock("item_no_link");
                }
                $list_tpl->setVariable("CSS_ROW", $this->css_row);
                $list_tpl->setVariable("TXT_USER", $user->getLogin());
                $list_tpl->setVariable(
                    "IMG_USER",
                    $user->getPersonalPicturePath("xxsmall")
                );
                $list_tpl->parseCurrentBlock();
                $list_tpl->touchBlock("row");
            }
        }

        return $list_tpl->get();
    }
}
