<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Config;

/**
 * Class ilCtrlPluginArtifactConfig
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlPluginArtifactConfig implements Config
{
    public const PLUGIN_STATUS_INSTALL   = 0;
    public const PLUGIN_STATUS_UNINSTALL = 1;
    public const PLUGIN_STATUS_UPDATE    = 2;

    /**
     * @var ilPlugin
     */
    private ilPlugin $plugin;

    /**
     * @var int
     */
    private int $status;

    /**
     * ilCtrlPluginArtifactConfig Constructor
     *
     * @param ilPlugin $plugin
     * @param int      $status
     */
    public function __construct(ilPlugin $plugin, int $status)
    {
        $this->plugin = $plugin;
        $this->status = $status;
    }

    /**
     * @return ilPlugin
     */
    public function getPlugin() : ilPlugin
    {
        return $this->plugin;
    }

    /**
     * @return int
     */
    public function getStatus() : int
    {
        return $this->status;
    }
}