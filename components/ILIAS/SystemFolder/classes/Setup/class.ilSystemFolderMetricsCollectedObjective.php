<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilSystemFolderMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        if (!$factory) {
            return;
        }
        $settings = $factory->settingsFor("common");
        $firstname = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_CONFIG,
            Setup\Metrics\Metric::TYPE_TEXT,
            $settings->get("admin_firstname", "")
        );
        $lastname = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_CONFIG,
            Setup\Metrics\Metric::TYPE_TEXT,
            $settings->get("admin_lastname", "")
        );
        $email = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_CONFIG,
            Setup\Metrics\Metric::TYPE_TEXT,
            $settings->get("admin_email", "")
        );
        $contact = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_CONFIG,
            Setup\Metrics\Metric::TYPE_COLLECTION,
            [
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email
            ],
            "Contact information for this installation."
        );
        $storage->store("contact", $contact);
    }
}
