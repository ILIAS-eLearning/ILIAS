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

use ILIAS\Refinery;
use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

class ilFileObjectMigrationDoneObjective implements Setup\Objective
{
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    public function getLabel(): string
    {
        return "File Migration has been performed in ILIAS 7.";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        /**
         * @var $db ilDBInterface
         */
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $db_error = false;
        try {
            $res = $db->query("SELECT file_id FROM file_data WHERE rid IS NULL OR rid =''");
        } catch (Throwable $t) {
            $db_error = true;
        } finally {
            if ($db_error || $res->numRows() > 0) {
                throw new Setup\NotExecutableException(
                    "File-Object migration has not been performed in ILIAS 7, at least {$res->numRows()} File-Objects won't be accessible anymore. Best you can do is revert to your backup and perform an upgrade to the latest ILIAS 7 and perform all Migrations. After that you can upgrade to ILIAS 8. Find more information in Modules/File/classes/Setup/MISSING_MIGRATION.md"
                );
            }
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        /**
         * @var $db ilDBInterface
         */
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $res = $db->query("SELECT COUNT(file_id) AS amount FROM file_data WHERE rid IS NULL OR rid =''");
        $d = $db->fetchObject($res);

        return (int) $d->amount > 0;
    }
}
