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

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Metrics\Metric;
use ILIAS\DI;

class ilTestDatabaseInconsistencyMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return array<\ilDatabaseInitializedObjective|\ilIniFilesLoadedObjective>
     */
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $metrics = [
            "database_available" => new Metric(
                Metric::STABILITY_VOLATILE,
                Metric::TYPE_BOOL,
                !is_null($db),
                "This metric is a canary to check for the general existence of this collection."
            )
        ];

        if ($db) {
            $this->collectMantis37759($metrics, $db);
        }

        $storage->store("database_inconsistencies", new Metric(
            Metric::STABILITY_MIXED,
            Metric::TYPE_COLLECTION,
            $metrics,
            "These metrics collect information about inconsistencies in the database of the T&A."
        ));
    }

    protected function collectMantis37759(array &$metrics, \ilDBInterface $db)
    {
        $result = $db->query("
            SELECT COUNT(*) as cnt
            FROM tst_active
            LEFT JOIN object_data ON tst_active.test_fi = object_data.obj_id
            WHERE object_data.obj_id IS NULL
        ");

        $metrics["mantis_37759"] = new Metric(
            Metric::STABILITY_VOLATILE,
            Metric::TYPE_GAUGE,
            $db->fetchAssoc($result)["cnt"],
            "Measures active tests runs where the corresponding Test object does not exist anymore."
        );
    }
}
