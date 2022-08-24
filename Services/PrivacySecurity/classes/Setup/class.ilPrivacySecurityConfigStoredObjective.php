<?php

declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

/**
 * Store information about https is enabled
 */
class ilPrivacySecurityConfigStoredObjective implements Setup\Objective
{
    /**
     * @var    ilPrivacySecuritySetupConfig
     */
    protected ilPrivacySecuritySetupConfig $config;

    public function __construct(ilPrivacySecuritySetupConfig $config)
    {
        $this->config = $config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Store information about privacy security in settings";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesPopulatedObjective(),
            new ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");
        $settings->set("https", $this->bool2string($this->config->getForceHttpsOnLogin()));

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function bool2string(bool $value): string
    {
        if ($value) {
            return "1";
        }
        return "0";
    }
}
