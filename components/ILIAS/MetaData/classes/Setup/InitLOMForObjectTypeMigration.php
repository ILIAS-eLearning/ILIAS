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

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\Setup\CLI\IOWrapper;

/**
 * This migration creates LOM sets for all repository objects of a given type,
 * with title and description taken from object_data and object_description.
 */
abstract class InitLOMForObjectTypeMigration implements Setup\Migration
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
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    final public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->settings = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY)->settingsFor();

        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    final public function step(Environment $environment): void
    {
        $this->logInfo('');

        // Read out next object without LOM
        $res = $this->db->query(
            "SELECT
                object_data.title AS title,
                object_data.description AS short_description,
                object_data.obj_id AS obj_id,
                object_description.description AS long_description
            FROM object_data
                LEFT JOIN object_description ON object_data.obj_id = object_description.obj_id
                LEFT JOIN il_meta_general ON il_meta_general.rbac_id = object_data.obj_id
            WHERE object_data.type = " . $this->quotedObjectType() . " " .
            "AND il_meta_general.rbac_id IS NULL
            AND NOT COALESCE(object_data.title, '') = ''
            LIMIT 1"
        );
        $row = $this->db->fetchAssoc($res);
        if (!$row) {
            $this->logInfo('No object without LOM found.');
            return;
        }

        $obj_id = (int) $row['obj_id'];
        $title = (string) $row['title'];
        if (($row['long_description'] ?? '') !== '') {
            $description = (string) $row['long_description'];
        } else {
            $description = (string) $row['short_description'];
        }
        $identifier_entry = 'il_' . $this->settings->get('inst_id', '0') . '_' . $this->objectType() . '_' . $obj_id;
        $this->logInfo('Found object with obj_id ' . $obj_id . ' and title ' . $title);

        // Write to LOM tables
        $next_id_general = $this->db->nextId('il_meta_general');
        $this->db->insert('il_meta_general', [
            'meta_general_id' => [\ilDBConstants::T_INTEGER, $next_id_general],
            'rbac_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_type' => [\ilDBConstants::T_TEXT, $this->objectType()],
            'title' => [\ilDBConstants::T_TEXT, $title]
        ]);
        $this->logInfo('Inserted in il_meta_general.');

        $next_id_identifier = $this->db->nextId('il_meta_identifier');
        $this->db->insert('il_meta_identifier', [
            'meta_identifier_id' => [\ilDBConstants::T_INTEGER, $next_id_identifier],
            'rbac_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_type' => [\ilDBConstants::T_TEXT, $this->objectType()],
            'parent_type' => [\ilDBConstants::T_TEXT, 'meta_general'],
            'parent_id' => [\ilDBConstants::T_INTEGER, $next_id_general],
            'catalog' => [\ilDBConstants::T_TEXT, 'ILIAS'],
            'entry' => [\ilDBConstants::T_TEXT, $identifier_entry]
        ]);
        $this->logInfo('Inserted in il_meta_identifier.');

        if ($description === '') {
            $this->logInfo('No description, skipping il_meta_description.');
            $this->logSuccess('LOM set created!');
            return;
        }
        $next_id_description = $this->db->nextId('il_meta_description');
        $this->db->insert('il_meta_description', [
            'meta_description_id' => [\ilDBConstants::T_INTEGER, $next_id_description],
            'rbac_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_id' => [\ilDBConstants::T_INTEGER, $obj_id],
            'obj_type' => [\ilDBConstants::T_TEXT, $this->objectType()],
            'parent_type' => [\ilDBConstants::T_TEXT, 'meta_general'],
            'parent_id' => [\ilDBConstants::T_INTEGER, $next_id_general],
            'description' => [\ilDBConstants::T_TEXT, $description]
        ]);
        $this->logInfo('Inserted in il_meta_description.');
        $this->logSuccess('LOM set created!');
    }

    final public function getRemainingAmountOfSteps(): int
    {
        $res = $this->db->query(
            $query = "SELECT count(*) AS count FROM object_data LEFT JOIN il_meta_general
            ON il_meta_general.rbac_id = object_data.obj_id
            WHERE object_data.type = " . $this->quotedObjectType() . " " .
            "AND il_meta_general.rbac_id IS NULL
            AND NOT COALESCE(object_data.title, '') = ''"
        );
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
