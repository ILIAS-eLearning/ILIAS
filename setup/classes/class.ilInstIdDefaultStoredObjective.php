<?php

declare(strict_types=1);

/* Copyright (c) 2021 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilInstIdDefaultStoredObjective extends ilSetupObjective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Store default installation id.";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");

        $settings->set("inst_id", "0");

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $factory->settingsFor("common");

        return !(bool) $settings->get("inst_id");
    }
}
