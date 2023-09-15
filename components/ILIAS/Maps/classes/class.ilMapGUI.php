<?php

declare(strict_types=1);

/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * User interface class for maps
 */
abstract class ilMapGUI
{
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
}
