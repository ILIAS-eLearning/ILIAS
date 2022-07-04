<?php declare(strict_types=1);

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

use ILIAS\Setup;

class ilSetupMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getLabel() : string
    {
        return "Collect common metrics for the ILIAS installation.";
    }

    protected function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $storage->storeStableBool(
            "is_installed",
            $ini !== null && $client_ini !== null,
            "Are there any indications an installation was performed?"
        );
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if ($client_id) {
            $storage->storeConfigText(
                "client_id",
                $client_id,
                "Id of the ILIAS client."
            );
        }
        $settings_factory  = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        if ($settings_factory) {
            $common_settings = $settings_factory->settingsFor("common");
            $nic_enabled = $common_settings->get("nic_enabled") == "1";
            $storage->storeStableBool(
                "nic_enabled",
                $nic_enabled,
                "Is the installation registered at the ILIAS NIC server?"
            );
            if ($nic_enabled) {
                $storage->storeConfigText(
                    "inst_id",
                    $common_settings->get("inst_id"),
                    "The id of the installation as provided by the ILIAS NIC server."
                );
            }
        }
    }
}
