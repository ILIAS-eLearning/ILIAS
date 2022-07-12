<?php declare(strict_types=1);

use ILIAS\Data\Version;

/**
 * Simple value class for information about a plugin.
 */
class ilPluginInfo
{
    protected Version $actual_ilias_version;
    protected ilPluginSlotInfo $pluginslot;
    protected string $id;
    protected string $name;
    protected bool $activated;
    protected ?Version $current_version;
    protected ?int $current_db_version;
    protected Version $available_version;
    protected Version $minimum_ilias_version;
    protected Version $maximum_ilias_version;
    protected string $responsible;
    protected string $responsible_mail;
    protected bool $supports_learning_progress;
    protected bool $supports_export;
    protected bool $supports_cli_setup;

    public function __construct(
        Version $actual_ilias_version,
        ilPluginSlotInfo $pluginslot,
        string $id,
        string $name,
        bool $activated,
        ?Version $current_version,
        ?int $current_db_version,
        Version $available_version,
        Version $minimum_ilias_version,
        Version $maximum_ilias_version,
        string $responsible,
        string $responsible_mail,
        bool $supports_learning_progress,
        bool $supports_export,
        bool $supports_cli_setup
    ) {
        if ($current_version === null && $current_db_version !== null) {
            throw new \InvalidArgumentException(
                "If there is no current version for the plugin, we also should not " .
                "have a db-version."
            );
        }
        $this->actual_ilias_version = $actual_ilias_version;
        $this->pluginslot = $pluginslot;
        $this->id = $id;
        $this->name = $name;
        $this->activated = $activated;
        $this->current_version = $current_version;
        $this->current_db_version = $current_db_version;
        $this->available_version = $available_version;
        $this->minimum_ilias_version = $minimum_ilias_version;
        $this->maximum_ilias_version = $maximum_ilias_version;
        $this->responsible = $responsible;
        $this->responsible_mail = $responsible_mail;
        $this->supports_learning_progress = $supports_learning_progress;
        $this->supports_export = $supports_export;
        $this->supports_cli_setup = $supports_cli_setup;
    }

    public function getPluginSlot() : ilPluginSlotInfo
    {
        return $this->pluginslot;
    }

    public function getComponent() : ilComponentInfo
    {
        return $this->pluginslot->getComponent();
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getPath() : string
    {
        return $this->pluginslot->getPath() . "/" . $this->getName();
    }

    public function getClassName() : string
    {
        return "il" . $this->getName() . "Plugin";
    }

    public function getConfigGUIClassName() : string
    {
        return "il" . $this->getName() . "ConfigGUI";
    }

    /**
     * "activated" tells if the administrator of the installation
     * wants the plugin to be effective. Compare to "active".
     */
    public function isActivated() : bool
    {
        return $this->activated;
    }

    public function getCurrentVersion() : ?Version
    {
        return $this->current_version;
    }

    public function getCurrentDBVersion() : ?int
    {
        return $this->current_db_version;
    }

    public function getAvailableVersion() : Version
    {
        return $this->available_version;
    }

    public function getMinimumILIASVersion() : Version
    {
        return $this->minimum_ilias_version;
    }

    public function getMaximumILIASVersion() : Version
    {
        return $this->maximum_ilias_version;
    }

    public function getResponsible() : string
    {
        return $this->responsible;
    }

    public function getResponsibleMail() : string
    {
        return $this->responsible_mail;
    }

    public function supportsLearningProgress() : bool
    {
        return $this->supports_learning_progress;
    }

    public function supportsExport() : bool
    {
        return $this->supports_export;
    }

    public function supportsCLISetup() : bool
    {
        return $this->supports_cli_setup;
    }

    /**
     * "Installed" tells if the plugin has some installed version.
     */
    public function isInstalled() : bool
    {
        return $this->current_version !== null;
    }

    /**
     * "Update required" tells if the plugin needs an update.
     */
    public function isUpdateRequired() : bool
    {
        return $this->isInstalled() && !$this->current_version->equals($this->available_version);
    }

    /**
     * "Version to old" tells if the plugin code has a version that is below the
     * version that was updated last.
     */
    public function isVersionToOld() : bool
    {
        return $this->current_version->isGreaterThan($this->available_version);
    }

    /**
     * "ILIAS Version compliance" tells if the plugin can be operated with the
     * given ILIAS version.
     */
    public function isCompliantToILIAS() : bool
    {
        return
            $this->actual_ilias_version->isGreaterThanOrEquals($this->minimum_ilias_version)
            && $this->actual_ilias_version->isSmallerThanOrEquals($this->maximum_ilias_version);
    }

    /**
     * Can this plugin be activated right now.
     */
    public function isActivationPossible() : bool
    {
        return $this->isCompliantToILIAS()
            && $this->isInstalled()
            && !$this->isVersionToOld()
            && !$this->isUpdateRequired();
    }

    /**
     * Is this plugin active right now?
     */
    public function isActive() : bool
    {
        return $this->isActivationPossible()
            && $this->isActivated();
    }

    /**
     * Which is the reason for the inactivity?
     *
     * @throws \LogicException if plugin is actually active.
     * @return string to be used as identifier for language service.
     */
    public function getReasonForInactivity() : string
    {
        if ($this->isActive()) {
            throw new \LogicException(
                "Plugin is active, so no reason for inactivity."
            );
        }

        if (!$this->isCompliantToILIAS()) {
            return "cmps_needs_matching_ilias_version";
        }

        if (!$this->isInstalled()) {
            return "cmps_must_installed";
        }

        if ($this->isVersionToOld()) {
            return "cmps_needs_upgrade";
        }

        if ($this->isUpdateRequired()) {
            return "cmps_needs_update";
        }

        if (!$this->isActivated()) {
            return "cmps_not_activated";
        }

        throw new \LogicException(
            "Unknown reason for inactivity of the plugin."
        );
    }
}
