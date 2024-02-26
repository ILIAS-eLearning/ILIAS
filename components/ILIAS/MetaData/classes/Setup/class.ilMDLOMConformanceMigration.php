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
use ILIAS\Setup\CLI\IOWrapper;

class ilMDLOMConformanceMigration implements Setup\Migration
{
    protected const SELECT_LIMIT = 1000;
    protected const MAX_LOOPS = 10000;

    protected ilDBInterface $db;
    protected IOWrapper $io;

    public function getLabel(): string
    {
        return "Migration of some LOM elements that should be non-unique to their own tables.";
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

        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }
    }

    public function step(Environment $environment): void
    {
        $this->startLog();

        $this->migrateCoverage(); // general > coverage
        $this->setIndices('il_meta_coverage');

        $this->migrateSchema(); // metaMetadata > metadataSchema
        $this->setIndices('il_meta_meta_schema');

        $this->migrateOrComposite(); // technical > requirement > orComposite
        $this->setIndices('il_meta_or_composite');

        $this->migrateFromEducational();// educational > learningResourceType, intendedEndUserRole, context
        $this->setIndices('il_meta_lr_type');
        $this->setIndices('il_meta_end_usr_role');
        $this->setIndices('il_meta_context');
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (
            $this->countNotMigratedCoverages() > 0 ||
            !$this->areIndicesSet('il_meta_coverage')
        ) {
            return 1;
        }

        if (
            $this->countNotMigratedSchemas() > 0 ||
            !$this->areIndicesSet('il_meta_meta_schema')
        ) {
            return 1;
        }

        if (
            $this->countNotMigratedOrComposites() > 0 ||
            !$this->areIndicesSet('il_meta_or_composite')
        ) {
            return 1;
        }

        if (
            $this->countNotMigratedFromEducational() > 0 ||
            !$this->areIndicesSet('il_meta_lr_type') ||
            !$this->areIndicesSet('il_meta_end_usr_role') ||
            !$this->areIndicesSet('il_meta_context')
        ) {
            return 1;
        }
        return 0;
    }

    protected function areIndicesSet(string $table): bool
    {
        return $this->db->indexExistsByFields($table, ['rbac_id', 'obj_id']);
    }

    protected function setIndices(string $table): void
    {
        if (!$this->areIndicesSet($table)) {
            $this->logDetailed('Set index for ' . $table);
            $this->db->addIndex($table, ['rbac_id', 'obj_id'], 'i1');
        }
    }

    protected function migrateCoverage(): void
    {
        $count = 0;
        while ($this->countNotMigratedCoverages() > 0 && $count < self::MAX_LOOPS) {
            $this->migrateSetOfCoverages(self::SELECT_LIMIT);
            $count++;
        }
        $this->logSuccess('Migrated all coverages.');
    }

    protected function migrateSetOfCoverages(int $limit): void
    {
        $this->db->setLimit($limit);
        $coverages = $this->db->query(
            "SELECT meta_general_id, rbac_id, obj_id, obj_type, coverage,
            coverage_language FROM il_meta_general WHERE CHAR_LENGTH(coverage) > 0
            OR CHAR_LENGTH(coverage_language) > 0 ORDER BY meta_general_id"
        );

        while ($coverage = $this->db->fetchAssoc($coverages)) {
            $this->logDetailed('Insert coverage from meta_general_id ' . $coverage['meta_general_id']);
            $next_id = $this->db->nextId('il_meta_coverage');
            $this->db->manipulate(
                "INSERT INTO il_meta_coverage (meta_coverage_id, rbac_id, obj_id,
                obj_type, parent_type, parent_id, coverage, coverage_language) VALUES (" .
                $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($coverage['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($coverage['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($coverage['obj_type'], ilDBConstants::T_TEXT) . ", " .
                $this->db->quote('meta_general', ilDBConstants::T_TEXT) . ", " .
                $this->db->quote($coverage['meta_general_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($coverage['coverage'], ilDBConstants::T_TEXT) . ", " .
                $this->db->quote($coverage['coverage_language'], ilDBConstants::T_TEXT) . ")"
            );
            $this->logDetailed('Success, deleting old entry');
            $this->db->manipulate(
                "UPDATE il_meta_general SET coverage = '', coverage_language = ''
                WHERE meta_general_id = " . $this->db->quote($coverage['meta_general_id'], ilDBConstants::T_INTEGER)
            );
        }
    }

    protected function countNotMigratedCoverages(): int
    {
        $res = $this->db->query(
            "SELECT COUNT(*) AS count FROM il_meta_general WHERE CHAR_LENGTH(coverage) > 0
            OR CHAR_LENGTH(coverage_language) > 0"
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['count'];
        }
        return 0;
    }

    protected function migrateSchema(): void
    {
        $count = 0;
        while ($this->countNotMigratedSchemas() > 0 && $count < self::MAX_LOOPS) {
            $this->migrateSetOfSchemas(self::SELECT_LIMIT);
            $count++;
        }
        $this->logSuccess('Migrated all schemas.');
    }

    protected function migrateSetOfSchemas(int $limit): void
    {
        $this->db->setLimit($limit);
        $schemas = $this->db->query(
            "SELECT meta_meta_data_id, rbac_id, obj_id, obj_type, meta_data_scheme 
            FROM il_meta_meta_data WHERE CHAR_LENGTH(meta_data_scheme) > 0 ORDER BY meta_meta_data_id"
        );

        while ($schema = $this->db->fetchAssoc($schemas)) {
            $this->logDetailed('Insert schema from meta_meta_data_id ' . $schema['meta_meta_data_id']);
            $next_id = $this->db->nextId('il_meta_meta_schema');
            $this->db->manipulate(
                "INSERT INTO il_meta_meta_schema (meta_meta_schema_id, rbac_id, obj_id,
                obj_type, parent_type, parent_id, meta_data_schema) VALUES (" .
                $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($schema['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($schema['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote($schema['obj_type'], ilDBConstants::T_TEXT) . ", " .
                $this->db->quote('meta_meta_data', ilDBConstants::T_TEXT) . ", " .
                $this->db->quote($schema['meta_meta_data_id'], ilDBConstants::T_INTEGER) . ", " .
                $this->db->quote('LOMv1.0', ilDBConstants::T_TEXT) . ")"
            );
            $this->logDetailed('Success, deleting old entry');
            $this->db->manipulate(
                "UPDATE il_meta_meta_data SET meta_data_scheme = '' WHERE meta_meta_data_id = " .
                $this->db->quote($schema['meta_meta_data_id'], ilDBConstants::T_INTEGER)
            );
        }
    }

    protected function countNotMigratedSchemas(): int
    {
        $res = $this->db->query(
            "SELECT COUNT(*) AS count FROM il_meta_meta_data WHERE CHAR_LENGTH(meta_data_scheme) > 0"
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['count'];
        }
        return 0;
    }

    protected function migrateOrComposite(): void
    {
        $count = 0;
        while ($this->countNotMigratedOrComposites() > 0 && $count < self::MAX_LOOPS) {
            $this->migrateSetOfOrComposites(self::SELECT_LIMIT);
            $count++;
        }
        $this->logSuccess('Migrated all orComposites.');
    }

    protected function migrateSetOfOrComposites(int $limit): void
    {
        $this->db->setLimit($limit);
        $ors = $this->db->query(
            "SELECT meta_requirement_id, rbac_id, obj_id, obj_type, operating_system_name,
            os_min_version, os_max_version, browser_name, browser_minimum_version,
            browser_maximum_version FROM il_meta_requirement WHERE
            CHAR_LENGTH(operating_system_name) > 0 OR CHAR_LENGTH(os_min_version) > 0
            OR CHAR_LENGTH(os_max_version) > 0 OR CHAR_LENGTH(browser_name) > 0
            OR CHAR_LENGTH(browser_minimum_version) > 0 OR CHAR_LENGTH(browser_maximum_version) > 0
            ORDER BY meta_requirement_id"
        );

        while ($or = $this->db->fetchAssoc($ors)) {
            $has_os =
                ($or['operating_system_name'] ?? '') !== '' ||
                ($or['os_min_version'] ?? '') !== '' ||
                ($or['os_max_version'] ?? '') !== '';
            $has_browser =
                ($or['browser_name'] ?? '') !== '' ||
                ($or['browser_minimum_version'] ?? '') !== '' ||
                ($or['browser_maximum_version'] ?? '') !== '';

            if ($has_os) {
                $this->logDetailed('Insert orComposite (type os) from meta_requirement_id ' . $or['meta_requirement_id']);
                $next_id = $this->db->nextId('il_meta_or_composite');
                $name = $this->normalizeOrCompositeName($or['operating_system_name']);
                $this->db->manipulate(
                    "INSERT INTO il_meta_or_composite (meta_or_composite_id, rbac_id, obj_id,
                    obj_type, parent_type, parent_id, type, name, min_version, max_version) VALUES (" .
                    $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['obj_type'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote('meta_requirement', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['meta_requirement_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote('operating system', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['operating_system_name'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['os_min_version'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['os_max_version'], ilDBConstants::T_TEXT) . ")"
                );
                $this->logDetailed('Success, deleting old entry');
                $this->db->manipulate(
                    "UPDATE il_meta_requirement SET operating_system_name = '', os_min_version = '',
                    os_max_version = '' WHERE meta_requirement_id = " .
                    $this->db->quote($or['meta_requirement_id'], ilDBConstants::T_INTEGER)
                );
            }

            if ($has_browser) {
                $this->logDetailed('Insert orComposite (type browser) from meta_requirement_id ' . $or['meta_requirement_id']);
                $next_id = $this->db->nextId('il_meta_or_composite');
                $name = $this->normalizeOrCompositeName($or['browser_name']);
                $this->db->manipulate(
                    "INSERT INTO il_meta_or_composite (meta_or_composite_id, rbac_id, obj_id,
                    obj_type, parent_type, parent_id, type, name, min_version, max_version) VALUES (" .
                    $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($or['obj_type'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote('meta_requirement', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['meta_requirement_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote('browser', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($name, ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['browser_minimum_version'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($or['browser_maximum_version'], ilDBConstants::T_TEXT) . ")"
                );
                $this->logDetailed('Success, deleting old entry');
                $this->db->manipulate(
                    "UPDATE il_meta_requirement SET browser_name = '', browser_minimum_version = '',
                    browser_maximum_version = '' WHERE meta_requirement_id = " .
                    $this->db->quote($or['meta_requirement_id'], ilDBConstants::T_INTEGER)
                );
            }
        }
    }

    protected function countNotMigratedOrComposites(): int
    {
        $res = $this->db->query(
            "SELECT COUNT(*) AS count FROM il_meta_requirement WHERE
            CHAR_LENGTH(operating_system_name) > 0 OR CHAR_LENGTH(os_min_version) > 0
            OR CHAR_LENGTH(os_max_version) > 0 OR CHAR_LENGTH(browser_name) > 0
            OR CHAR_LENGTH(browser_minimum_version) > 0 OR CHAR_LENGTH(browser_maximum_version) > 0"
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['count'];
        }
        return 0;
    }

    protected function normalizeOrCompositeName(string $name): string
    {
        $dict = [
            'Any' => 'any',
            'NetscapeCommunicator' => 'netscape communicator',
            'MS-InternetExplorer' => 'ms-internet explorer',
            'Opera' => 'opera',
            'Amaya' => 'amaya',
            'PC-DOS' => 'pc-dos',
            'MS-Windows' => 'ms-windows',
            'MAC-OS' => 'macos',
            'Unix' => 'unix',
            'Multi-OS' => 'multi-os',
            'None' => 'none'
        ];

        if (key_exists($name, $dict)) {
            return $dict[$name];
        }
        return $name;
    }

    protected function migrateFromEducational(): void
    {
        $count = 0;
        while ($this->countNotMigratedFromEducational() > 0 && $count < self::MAX_LOOPS) {
            $this->migrateSetFromEducational(self::SELECT_LIMIT);
            $count++;
        }
        $this->logSuccess('Migrated all learningResourceTypes, intendedEndUserRoles, and contexts.');
    }

    protected function migrateSetFromEducational(int $limit): void
    {
        $this->db->setLimit($limit);
        $educationals = $this->db->query(
            "SELECT meta_educational_id, rbac_id, obj_id, obj_type,
            learning_resource_type, intended_end_user_role, context FROM il_meta_educational
            WHERE CHAR_LENGTH(learning_resource_type) > 0 OR CHAR_LENGTH(intended_end_user_role) > 0
            OR CHAR_LENGTH(context) > 0 ORDER BY meta_educational_id"
        );

        while ($educational = $this->db->fetchAssoc($educationals)) {
            $has_type = ($educational['learning_resource_type'] ?? '') !== '';
            $has_role = ($educational['intended_end_user_role'] ?? '') !== '';
            $has_context = ($educational['context'] ?? '') !== '';

            if ($has_type) {
                $this->logDetailed('Insert learningResourceType from meta_educational_id ' . $educational['meta_educational_id']);
                $next_id = $this->db->nextId('il_meta_lr_type');
                $type = $this->normalizeLearningResourceType($educational['learning_resource_type']);
                $this->db->manipulate(
                    "INSERT INTO il_meta_lr_type (meta_lr_type_id, rbac_id, obj_id, obj_type,
                    parent_type, parent_id, learning_resource_type) VALUES (" .
                    $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_type'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote('meta_educational', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($type, ilDBConstants::T_TEXT) . ")"
                );
                $this->logDetailed('Success, deleting old entry');
                $this->db->manipulate(
                    "UPDATE il_meta_educational SET learning_resource_type = '' WHERE meta_educational_id = " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER)
                );
            }

            if ($has_role) {
                $this->logDetailed('Insert intendedEndUserRole from meta_educational_id ' . $educational['meta_educational_id']);
                $next_id = $this->db->nextId('il_meta_end_usr_role');
                $role = $this->normalizeIntendedEndUserRole($educational['intended_end_user_role']);
                $this->db->manipulate(
                    "INSERT INTO il_meta_end_usr_role (meta_end_usr_role_id, rbac_id, obj_id, obj_type,
                    parent_type, parent_id, intended_end_user_role) VALUES (" .
                    $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_type'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote('meta_educational', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($role, ilDBConstants::T_TEXT) . ")"
                );
                $this->logDetailed('Success, deleting old entry');
                $this->db->manipulate(
                    "UPDATE il_meta_educational SET intended_end_user_role = '' WHERE meta_educational_id = " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER)
                );
            }

            if ($has_context) {
                $this->logDetailed('Insert context from meta_educational_id ' . $educational['meta_educational_id']);
                $next_id = $this->db->nextId('il_meta_context');
                $context = $this->normalizeContext($educational['context']);
                $this->db->manipulate(
                    "INSERT INTO il_meta_context (meta_context_id, rbac_id, obj_id, obj_type,
                    parent_type, parent_id, context) VALUES (" .
                    $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['rbac_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($educational['obj_type'], ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote('meta_educational', ilDBConstants::T_TEXT) . ", " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER) . ", " .
                    $this->db->quote($context, ilDBConstants::T_TEXT) . ")"
                );
                $this->logDetailed('Success, deleting old entry');
                $this->db->manipulate(
                    "UPDATE il_meta_educational SET context = '' WHERE meta_educational_id = " .
                    $this->db->quote($educational['meta_educational_id'], ilDBConstants::T_INTEGER)
                );
            }
        }
    }

    protected function countNotMigratedFromEducational(): int
    {
        $res = $this->db->query(
            "SELECT COUNT(*) AS count FROM il_meta_educational WHERE
            CHAR_LENGTH(learning_resource_type) > 0 OR CHAR_LENGTH(intended_end_user_role) > 0
            OR CHAR_LENGTH(context) > 0"
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['count'];
        }
        return 0;
    }

    protected function normalizeLearningResourceType(string $name): string
    {
        $dict = [
            'Exercise' => 'exercise',
            'Simulation' => 'simulation',
            'Questionnaire' => 'questionnaire',
            'Diagram' => 'diagram',
            'Figure' => 'figure',
            'Graph' => 'graph',
            'Index' => 'index',
            'Slide' => 'slide',
            'Table' => 'table',
            'NarrativeText' => 'narrative text',
            'Exam' => 'exam',
            'Experiment' => 'experiment',
            'ProblemStatement' => 'problem statement',
            'SelfAssessment' => 'self assessment',
            'Lecture' => 'lecture'
        ];

        if (key_exists($name, $dict)) {
            return $dict[$name];
        }
        return $name;
    }

    protected function normalizeIntendedEndUserRole(string $name): string
    {
        $dict = [
            'Teacher' => 'teacher',
            'Author' => 'author',
            'Learner' => 'learner',
            'Manager' => 'manager'
        ];

        if (key_exists($name, $dict)) {
            return $dict[$name];
        }
        return $name;
    }

    protected function normalizeContext(string $name): string
    {
        $dict = [
            'School' => 'school',
            'HigherEducation' => 'higher education',
            'Training' => 'training',
            'Other' => 'other'
        ];

        if (key_exists($name, $dict)) {
            return $dict[$name];
        }
        return $name;
    }

    protected function logDetailed(string $str): void
    {
        if (!isset($this->io) || !$this->io->isVerbose()) {
            return;
        }
        $this->io->inform($str);
    }

    protected function logSuccess(string $str): void
    {
        if (!isset($this->io)) {
            return;
        }
        $this->io->success($str);
    }

    protected function startLog(): void
    {
        if (!isset($this->io)) {
            return;
        }
        $this->io->text('');
    }
}
