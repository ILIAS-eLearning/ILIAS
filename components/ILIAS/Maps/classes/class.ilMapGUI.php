<?php

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

declare(strict_types=1);

use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirements;
use ILIAS\Data\Privacy\Types\GeoLocationValue;

/**
 * User interface class for maps
 */
abstract class ilMapGUI
{
    // how the use of person data is described in the generated privacy documentation
    private const string PRIVACY_MARKER = 'in the marker of a person on the map';
    private const string PRIVACY_VIEWERS = 'every person who can see the map';
    private const string PRIVACY_IN_ORDER_TO = 'show who is where';

    protected TechnicalRequirements $privacy_requirements;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected string $map_id;
    protected string $width;
    protected string $height;
    protected string $latitude;
    protected string $longitude;
    protected ?int $zoom;
    protected bool $enable_type_control;
    protected bool $enable_update_listener;
    protected bool $enable_navigation_control;
    /** @var int[] */
    protected array $user_marker;
    protected bool $large_map_control;
    protected bool $central_marker;

    public function __construct()
    {
        $this->privacy_requirements = new TechnicalRequirements();
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];

        $this->lng->loadLanguageModule("maps");

        $this->map_id = "";
        $this->width = "500px";
        $this->height = "300px";
        $this->latitude = "";
        $this->longitude = "";
        $this->zoom = null;
        $this->enable_type_control = false;
        $this->enable_update_listener = false;
        $this->enable_navigation_control = false;
        $this->user_marker = [];
        $this->large_map_control = false;
        $this->central_marker = false;
    }

    public function setMapId(string $map_id): ilMapGUI
    {
        $this->map_id = $map_id;
        return $this;
    }

    public function getMapId(): string
    {
        return $this->map_id;
    }

    public function setWidth(string $width): ilMapGUI
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function setHeight(string $height): ilMapGUI
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function setLatitude(string $latitude): ilMapGUI
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function setLongitude(string $longitude): ilMapGUI
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function setZoom(?int $zoom): ilMapGUI
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function getZoom(): ?int
    {
        return $this->zoom;
    }

    public function setEnableTypeControl(bool $enable_type_control): ilMapGUI
    {
        $this->enable_type_control = $enable_type_control;
        return $this;
    }

    public function getEnableTypeControl(): bool
    {
        return $this->enable_type_control;
    }

    public function setEnableNavigationControl(bool $enable_navigation_control): ilMapGUI
    {
        $this->enable_navigation_control = $enable_navigation_control;
        return $this;
    }

    public function getEnableNavigationControl(): bool
    {
        return $this->enable_navigation_control;
    }

    public function setEnableUpdateListener(bool $enable_update_listener): ilMapGUI
    {
        $this->enable_update_listener = $enable_update_listener;
        return $this;
    }

    public function getEnableUpdateListener(): bool
    {
        return $this->enable_update_listener;
    }

    public function setEnableLargeMapControl(bool $large_map_control): ilMapGUI
    {
        $this->large_map_control = $large_map_control;
        return $this;
    }

    public function getEnableLargeMapControl(): bool
    {
        return $this->large_map_control;
    }

    public function setEnableCentralMarker(bool $central_marker): ilMapGUI
    {
        $this->central_marker = $central_marker;
        return $this;
    }

    public function getEnableCentralMarker(): bool
    {
        return $this->central_marker;
    }

    public function addUserMarker(int $user_id): ilMapGUI
    {
        $this->user_marker[] = $user_id;
        return $this;
    }

    abstract public function getHtml(): string;

    /**
    * Get User List HTML (to be displayed besides the map)
    */
    abstract public function getUserListHtml(): string;

    /**
     * The location of a person for a marker on the map, if the person has
     * one and has published it in their profile.
     */
    protected function userMarkerLocation(ilObjUser $user): ?GeoLocationValue
    {
        if ($user->getPref('public_location') !== 'y') {
            return null;
        }
        return $user->getProfileData()->getGeoLocation()?->resolve(
            $this->privacy_requirements->displayToUser(
                self::PRIVACY_MARKER,
                self::PRIVACY_VIEWERS,
                self::PRIVACY_IN_ORDER_TO,
                'the person has published their location in their profile'
            )
        );
    }

    /**
     * The HTML shown in the marker of a person: the name, and what the
     * person has published in their profile of their affiliation and
     * address.
     */
    protected function userMarkerInfo(ilObjUser $user): string
    {
        $profile = $user->getProfileData();
        $published = static fn(string $field): bool => $user->getPref('public_' . $field) === 'y';

        $name = $profile->getPersonName()->resolve($this->privacy_requirements->displayToUser(
            self::PRIVACY_MARKER,
            self::PRIVACY_VIEWERS,
            self::PRIVACY_IN_ORDER_TO,
            'the person has published their location in their profile'
        ));
        $info = htmlspecialchars($name->firstname . ' ' . $name->lastname);

        if ($published('institution') || $published('department')) {
            $affiliation = $profile->getAffiliation()->resolve(
                $this->privacy_requirements->displayToUser(
                    self::PRIVACY_MARKER,
                    self::PRIVACY_VIEWERS,
                    self::PRIVACY_IN_ORDER_TO,
                    'the person has published it in their profile'
                )
            );
            $delim = '<br />';
            if ($published('institution')) {
                $info .= $delim . htmlspecialchars($affiliation->institution);
                $delim = ', ';
            }
            if ($published('department')) {
                $info .= $delim . htmlspecialchars($affiliation->department);
            }
        }

        if ($published('street') || $published('zipcode') || $published('city') || $published('country')) {
            $address = $profile->getPostalAddress()->resolve(
                $this->privacy_requirements->displayToUser(
                    self::PRIVACY_MARKER,
                    self::PRIVACY_VIEWERS,
                    self::PRIVACY_IN_ORDER_TO,
                    'the person has published it in their profile'
                )
            );
            $delim = '<br />';
            if ($published('street')) {
                $info .= $delim . htmlspecialchars($address->street);
            }
            if ($published('zipcode')) {
                $info .= $delim . htmlspecialchars($address->zipcode);
                $delim = ' ';
            }
            if ($published('city')) {
                $info .= $delim . htmlspecialchars($address->city);
            }
            if ($published('country')) {
                $info .= '<br />' . htmlspecialchars($address->country);
            }
        }

        return $info;
    }

    /**
     * The login of a person in the list of persons beside the map.
     */
    protected function userListLogin(ilObjUser $user): string
    {
        return $user->getProfileData()->getLogin()->resolve(
            $this->privacy_requirements->displayToUser(
                'in the list of persons beside the map',
                self::PRIVACY_VIEWERS,
                'open the marker of a person on the map'
            )
        );
    }
}
