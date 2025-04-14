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

namespace ILIAS\MetaData\Setup;

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\CLI\IOWrapper;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator;

/**
 * This migration deletes all LOM sets for a given type,
 * for (sub-)objects that have dropped LOM support.
 */
abstract class DeleteLOMForObjectTypeMigration implements Migration
{
    protected \ilDBInterface $db;
    protected IOWrapper $io;
    protected \ilSetting $settings;

    abstract protected function objectType(): string;

    abstract public function getLabel(): string;

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 50;
    }

    final public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseInitializedObjective(),
            new \ilDatabaseUpdatedObjective(),
        ];
    }

    final public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    final public function step(Environment $environment): void
    {
        $selects = [];
        foreach (LOMDictionaryInitiator::TABLES as $table) {
            $selects[] = 'SELECT rbac_id, obj_id FROM ' . $this->db->quoteIdentifier($table) .
                ' WHERE obj_type = ' . $this->quotedObjectType();
        }
        if (empty($selects)) {
            return;
        }
        $query = 'SELECT rbac_id, obj_id FROM (' . implode(' UNION ', $selects) .
            ') AS t ORDER BY t.rbac_id, t.obj_id ASC LIMIT 1';
        $res = $this->db->query($query);
        if (!($row = $this->db->fetchAssoc($res))) {
            $this->logInfo('No LOM found for ' . $this->objectType());
            return;
        }
        $rbac_id = $row['rbac_id'];
        $obj_id = $row['obj_id'];

        $this->logInfo('Deleting LOM for rbac_id = ' . $rbac_id . ' and obj_id = ' . $obj_id);

        foreach (LOMDictionaryInitiator::TABLES as $table) {
            $query = 'DELETE FROM ' . $this->db->quoteIdentifier($table) .
                ' WHERE obj_type = ' . $this->quotedObjectType() .
                ' AND rbac_id = ' . $this->db->quote($rbac_id, \ilDBConstants::T_INTEGER) .
                ' AND obj_id = ' . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER);
            $this->db->manipulate($query);
        }
        $this->logSuccess('Done!');
    }

    final public function getRemainingAmountOfSteps(): int
    {
        $selects = [];
        foreach (LOMDictionaryInitiator::TABLES as $table) {
            $selects[] = 'SELECT rbac_id, obj_id FROM ' . $this->db->quoteIdentifier($table) .
                ' WHERE obj_type = ' . $this->quotedObjectType();
        }
        if (empty($selects)) {
            return 0;
        }
        $query = 'SELECT COUNT(*) AS count FROM (' . implode(' UNION ', $selects) . ') AS t';
        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['count'];
        }
        return 0;
    }

    private function quotedObjectType(): string
    {
        return $this->db->quote($this->objectType(), \ilDBConstants::T_TEXT);
    }

    protected function logInfo(string $str): void
    {
        if (!isset($this->io) || !$this->io->isVerbose()) {
            return;
        }
        $this->io->inform($str);
    }

    protected function logSuccess(string $str): void
    {
        if (!isset($this->io) || !$this->io->isVerbose()) {
            return;
        }
        $this->io->success($str);
    }
}
