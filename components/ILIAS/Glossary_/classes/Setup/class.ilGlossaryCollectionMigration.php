<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
final class ilGlossaryCollectionMigration implements Setup\Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "Migration of collection glossaries due to revision";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $set = $this->db->query(
            "SELECT glossary.id AS glossary_id, glossary.virtual, glossary_term.id AS term_id, glossary_term.glo_id " .
            " FROM glossary LEFT JOIN glossary_term ON glossary.id = glossary_term.glo_id " .
            " WHERE glossary.virtual = 'level' OR glossary.virtual = 'subtree' " .
            " ORDER BY glossary.id"
        );
        $tmp_id = 0;
        while ($rec = $this->db->fetchAssoc($set)) {
            $glo_id = (int) $rec["glossary_id"];
            $term_id = (int) $rec["term_id"];
            if ($glo_id === $tmp_id) {
                continue;
            }
            if ($term_id > 0) {
                $this->db->manipulate(
                    "UPDATE glossary SET " .
                    " glossary.virtual = " . $this->db->quote("none", "text") .
                    " WHERE glossary.id = " . $this->db->quote($glo_id, "integer")
                );
                $this->log("Convert glossary with id " . $glo_id . " into Standard Glossary.");
            } else {
                $this->db->manipulate(
                    "UPDATE glossary SET " .
                    " glossary.virtual = " . $this->db->quote("coll", "text") .
                    " WHERE glossary.id = " . $this->db->quote($glo_id, "integer")
                );
                $this->log("Convert glossary with id " . $glo_id . " into Collection Glossary.");
            }

            $tmp_id = $glo_id;
        }
    }

    protected function log(string $str): void
    {
        echo "\n" . $str;
    }

    public function getRemainingAmountOfSteps(): int
    {
        $set = $this->db->query(
            "SELECT glossary.virtual FROM glossary " .
            " WHERE glossary.virtual = 'level' OR glossary.virtual = 'subtree'"
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return 1;
        }

        return 0;
    }
}
