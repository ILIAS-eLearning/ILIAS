<?php

declare(strict_types=1);

/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilMapUtil
{
    public const DEFAULT_TILE = "a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org";
    public const DEFAULT_GEOLOCATION = null;

    public static ?ilSetting $_settings = null;

    public static function settings(): ilSetting
    {
        if (self::$_settings === null) {
            self::$_settings = new ilSetting("maps");
        }
        return self::$_settings;
    }

    /**
    * Checks whether Map feature is activated.
    * API key must be provided.
    */
    public static function isActivated(): bool
    {
        return self::settings()->get("enable") == 1;
    }

    public static function setActivated(bool $activated): void
    {
        self::settings()->set("enable", $activated ? "1" : "0");
    }

    public static function setType(string $type): void
    {
        self::settings()->set("type", $type);
    }

    public static function getType(): ?string
    {
        return self::settings()->get("type");
    }

    public static function setStdLatitude(string $lat): void
    {
        self::settings()->set("std_latitude", $lat);
    }

    public static function getStdLatitude(): ?string
    {
        return self::settings()->get("std_latitude");
    }

    public static function setStdLongitude(string $lon): void
    {
        self::settings()->set("std_longitude", $lon);
    }

    public static function getStdLongitude(): ?string
    {
        return self::settings()->get("std_longitude");
    }

    public static function setStdZoom(string $zoom): void
    {
        self::settings()->set("std_zoom", $zoom);
    }

    public static function getStdZoom(): ?string
    {
        return self::settings()->get("std_zoom");
    }

    public static function setApiKey(string $api_key): void
    {
        self::settings()->set("api_key", $api_key);
    }

    public static function getApiKey(): ?string
    {
        return self::settings()->get("api_key");
    }

    public static function setStdTileServers(string $tile): void
    {
        self::settings()->set("std_tile", $tile);
    }

    /**
     * Returns the tile server to be used in the installation.
     *
     * @return	string tile server url
     */
    public static function getStdTileServers(): string
    {
        $std_tile = self::settings()->get("std_tile");
        return $std_tile ?: self::DEFAULT_TILE;
    }


    public static function setStdGeolocationServer($geolocation): void
    {
        self::settings()->set("std_geolocation", $geolocation);
    }

    /**
     * Returns the reverse geolocation server to be used in the installation.
     */
    public static function getStdGeolocationServer(): ?string
    {
        $std_geoloc = self::settings()->get("std_geolocation");
        return $std_geoloc ?: self::DEFAULT_GEOLOCATION;
    }

    /**
    * Get default longitude, latitude and zoom.
    * @returns array<string, string>
    */
    public static function getDefaultSettings(): array
    {
        return [
            "longitude" => self::settings()->get("std_longitude") ?? "",
            "latitude" => self::settings()->get("std_latitude") ?? "",
            "zoom" => self::settings()->get("std_zoom") ?? 0
        ];
    }

    /**
    * Get an instance of the GUI class.
    */
    public static function getMapGUI(): ilMapGui
    {
        $type = self::getType();
        switch ($type) {
            case "openlayers":
                 $map = new ilOpenLayersMapGUI();
                 $map->setTileServers(self::getStdTileServers());
                 $map->setGeolocationServer(self::getStdGeolocationServer());
                 return $map;
            default:
                return new ilGoogleMapGUI();
        }
    }

    /**
    * Get a dict { $id => $name } for available maps services.
    *
    * @returns array<string, string>
    */
    public static function getAvailableMapTypes(): array
    {
        global $DIC;
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("maps");

        return [
            "openlayers" => $lng->txt("maps_open_layers_maps"),
            "googlemaps" => $lng->txt("maps_google_maps")
        ];
    }
}
