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
            Setup\Metrics\MetricStability::CONFIG,
            Setup\Metrics\MetricType::TEXT,
            fn() => $settings->get("admin_firstname", "")
        );
        $lastname = new Setup\Metrics\Metric(
            Setup\Metrics\MetricStability::CONFIG,
            Setup\Metrics\MetricType::TEXT,
            fn() => $settings->get("admin_lastname", "")
        );
        $email = new Setup\Metrics\Metric(
            Setup\Metrics\MetricStability::CONFIG,
            Setup\Metrics\MetricType::TEXT,
            fn() => $settings->get("admin_email", "")
        );
        $contact = new Setup\Metrics\Metric(
            Setup\Metrics\MetricStability::CONFIG,
            Setup\Metrics\MetricType::COLLECTION,
            fn() => [
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email
            ],
            "Contact information for this installation."
        );
        $storage->store("contact", $contact);
    }
}
