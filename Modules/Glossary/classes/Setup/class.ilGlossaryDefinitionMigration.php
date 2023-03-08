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
final class ilGlossaryDefinitionMigration implements Setup\Migration
{
    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return "Migration of glossary definitions after abolition of multiple definitions";
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
            "SELECT glossary_definition.id AS glo_def_id, glossary_definition.term_id AS glo_def_term_id, " .
            " glossary_definition.short_text, glossary_definition.short_text_dirty, " .
            " glossary_term.id AS glo_term_id, glossary_term.glo_id, glossary_term.term, glossary_term.language, " .
            " glossary_term.create_date, glossary_term.last_update " .
            " FROM glossary_definition JOIN glossary_term " .
            " WHERE glossary_definition.term_id = glossary_term.id " .
            " ORDER BY glossary_term.glo_id, glossary_term.id, glossary_definition.id"
        );
        $tmp = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            // check if there are multiple definitions for a term
            if (!empty($tmp)
                && $tmp["glo_id"] == $rec["glo_id"]
                && $tmp["glo_term_id"] == $rec["glo_term_id"]
            ) {
                $new_term_id = $this->db->nextId('glossary_term');
                // create new term with same values, but new id
                $this->log("Create new glossary term with id: " . $new_term_id);
                $this->db->manipulate("INSERT INTO glossary_term (id, glo_id, term, language, import_id, create_date, last_update)" .
                    " VALUES (" .
                    $this->db->quote($new_term_id, "integer") . ", " .
                    $this->db->quote($rec["glo_id"], "integer") . ", " .
                    $this->db->quote($rec["term"], "text") . ", " .
                    $this->db->quote($rec["language"], "text") . "," .
                    $this->db->quote("", "text") . "," .
                    $this->db->quote($rec["create_date"], "text") . ", " .
                    $this->db->quote($rec["last_update"], "text") . ")");
                // change definition with new term id
                $this->db->manipulate(
                    "UPDATE glossary_definition SET " .
                    " term_id = " . $this->db->quote($new_term_id, "integer") .
                    " WHERE id = " . $this->db->quote($rec["glo_def_id"], "integer")
                );
            }
            $tmp["glo_id"] = $rec["glo_id"];
            $tmp["glo_term_id"] = $rec["glo_term_id"];
        }

        $set = $this->db->query("SELECT * FROM glossary_definition WHERE migration = " . $this->db->quote("0", "integer"));
        while ($rec = $this->db->fetchAssoc($set)) {
            // merge glossary_term and glossary_definition table
            $this->log("Add short text ('" . $rec["short_text"] . "') and short text dirty ('" .
                $rec["short_text_dirty"] . "') to glossary term with id: " . $rec["term_id"]);
            $this->db->manipulate(
                "UPDATE glossary_term SET " .
                " short_text = " . $this->db->quote($rec["short_text"], "text") . ", " .
                " short_text_dirty = " . $this->db->quote($rec["short_text_dirty"], "integer") .
                " WHERE id = " . $this->db->quote($rec["term_id"], "integer")
            );
            // update id and type in page objects
            $this->log("Update id and type ('gdf' to 'term') for page object with id: " . $rec["id"]);
            $this->db->manipulate(
                "UPDATE page_object SET " .
                " page_id = " . $this->db->quote($rec["term_id"], "integer") . ", " .
                " parent_type = " . $this->db->quote("term", "text") .
                " WHERE parent_type = " . $this->db->quote("gdf", "text") .
                " AND page_id = " . $this->db->quote($rec["id"], "integer")
            );
            // set migration marker to 1 when it's done
            $this->db->manipulate(
                "UPDATE glossary_definition SET " .
                " migration = " . $this->db->quote("1", "integer") .
                " WHERE id = " . $this->db->quote($rec["id"], "integer")
            );
        }

        // update type in page object definition
        $this->log("Update type ('gdf' to 'term') for page object definition.");
        $this->db->manipulate(
            "UPDATE copg_pobj_def SET " .
            " parent_type = " . $this->db->quote("term", "text") .
            " WHERE parent_type = " . $this->db->quote("gdf", "text")
        );
    }

    protected function log(string $str): void
    {
        echo "\n" . $str;
    }

    protected function manipulate(string $query): void
    {
        $this->db->manipulate($query);
        $this->log($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $set = $this->db->query(
            "SELECT glossary_definition.id AS glo_def_id, glossary_definition.term_id AS glo_def_term_id, " .
            " glossary_definition.short_text, glossary_definition.short_text_dirty, " .
            " glossary_term.id AS glo_term_id, glossary_term.glo_id, glossary_term.term, glossary_term.language, " .
            " glossary_term.create_date, glossary_term.last_update " .
            " FROM glossary_definition JOIN glossary_term " .
            " WHERE glossary_definition.term_id = glossary_term.id " .
            " ORDER BY glossary_term.glo_id, glossary_term.id, glossary_definition.id"
        );
        $tmp = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            // check if there are multiple definitions for a term
            if (!empty($tmp)
                && $tmp["glo_id"] == $rec["glo_id"]
                && $tmp["glo_term_id"] == $rec["glo_term_id"]
            ) {
                return 1;
            }
            $tmp["glo_id"] = $rec["glo_id"];
            $tmp["glo_term_id"] = $rec["glo_term_id"];
        }

        $set = $this->db->query("SELECT * FROM glossary_definition WHERE migration = " . $this->db->quote("0", "integer"));
        if ($rec = $this->db->fetchAssoc($set)) {
            return 1;
        }

        $set = $this->db->query("SELECT parent_type FROM copg_pobj_def WHERE parent_type = " . $this->db->quote("gdf", "text"));
        if ($rec = $this->db->fetchAssoc($set)) {
            return 1;
        }

        return 0;
    }
}
