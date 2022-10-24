<?php

declare(strict_types=1);

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
 * User interface class for Google Maps
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilGoogleMapGUI extends ilMapGUI
{
    protected string $css_row = "";

    public function __construct()
    {
        parent::__construct();
    }

    public function getHtml(): string
    {
        $html_tpl = new ilTemplate(
            "tpl.google_map.html",
            true,
            true,
            "Services/Maps"
        );

        $js_tpl = new ilTemplate(
            "tpl.google_map.js",
            true,
            true,
            "Services/Maps"
        );

        $this->tpl->addJavaScript("//maps.google.com/maps/api/js?key=" . ilMapUtil::getApiKey(), false);

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
        $type_control = $this->getEnableTypeControl()
            ? "true"
            : "false";
        $js_tpl->setVariable("TYPE_CONTROL", $type_control);
        $nav_control = $this->getEnableNavigationControl()
            ? "true"
            : "false";
        $js_tpl->setVariable("NAV_CONTROL", $nav_control);
        $update_listener = $this->getEnableUpdateListener()
            ? "true"
            : "false";
        $js_tpl->setVariable("UPDATE_LISTENER", $update_listener);
        $large_map_control = $this->getEnableLargeMapControl()
            ? "true"
            : "false";
        $js_tpl->setVariable("LARGE_CONTROL", $large_map_control);
        $central_marker = $this->getEnableCentralMarker()
            ? "true"
            : "false";
        $js_tpl->setVariable("CENTRAL_MARKER", $central_marker);

        $this->tpl->addOnLoadCode($js_tpl->get());

        return $html_tpl->get();
    }

    /**
    * Get User List HTML (to be displayed besides the map)
    */
    public function getUserListHtml(): string
    {
        $list_tpl = new ilTemplate(
            "tpl.google_map_user_list.html",
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
