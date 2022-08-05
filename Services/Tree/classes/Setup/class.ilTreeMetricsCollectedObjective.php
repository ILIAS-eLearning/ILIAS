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

use ILIAS\Setup\Metrics\CollectedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Metrics\Storage;

class ilTreeMetricsCollectedObjective extends CollectedObjective
{
    protected function getTentativePreconditions(Environment $environment) : array
    {
        return [
            new ilSettingsFactoryExistsObjective(),
            new ilDatabaseInitializedObjective(),
        ];
    }

    protected function collectFrom(Environment $environment, Storage $storage) : void
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);

        if (!$settings_factory || !$db) {
            return;
        }

        $settings = $settings_factory->settingsFor('common');

        $storage->storeConfigText(
            'Tree Implementation',
            $settings->get('main_tree_impl', 'ns') === 'ns' ? 'Nested Set' : 'Materialized Path',
            'The database implementation of the ILIAS repository tree.'
        );
    }
}
