<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Setup;
use ILIAS\Refinery;

class ilMathJaxMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $factory = $environment->getResource(Setup\Environment::RESOURCE_SETTINGS_FACTORY);
        $repo = new ilMathJaxConfigSettingsRepository($factory);
        $config = $repo->getConfig();

        $setup_config = new ilMathJaxSetupConfig([]);
        foreach ($setup_config->getDataFromConfig($config) as $key => $value) {
            if (is_bool($value)) {
                $storage->storeStableBool($key, $value);
            } else {
                $storage->storeStableText($key, (string) $value);
            }
        }
    }
}
