<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;

class ilMathJaxMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $repo = new ilMathJaxConfigSettingsRepository($factory);
        $config = $repo->getConfig();

        $setup_config = new ilMathJaxSetupConfig([]);
        foreach ($setup_config->getDataFromConfig($config) as $key => $value) {

            if (is_bool($value)) {
                $storage->storeStableBool($key, $value);
            }
            else {
                $storage->storeStableText($key, (string) $value);
            }
        }
    }
}
