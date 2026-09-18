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

namespace ILIAS\ObjectGroup\Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilObjectGroupRebrandMigration implements Migration
{
    private const string OLD_TYPE = 'itgr';
    private const string NEW_TYPE = 'obgr';

    private const array SETTINGS_KEY_MAP = [
        'obj_add_new_pos_itgr' => 'obj_add_new_pos_obgr',
        'obj_add_new_pos_grp_itgr' => 'obj_add_new_pos_grp_obgr',
        'obj_dis_creation_itgr' => 'obj_dis_creation_obgr',
    ];

    /**
     * @var array<string, array{pk: list<string>, columns: list<string>}>
     */
    private const array LEGACY_TARGETS = [
        'object_data_del' => [
            'pk' => ['obj_id'],
            'columns' => ['type'],
        ],
        'il_news_item' => [
            'pk' => ['id'],
            'columns' => ['context_obj_type', 'context_sub_obj_type'],
        ],
        'adv_md_record_objs' => [
            'pk' => ['record_id', 'obj_type', 'sub_type'],
            'columns' => ['obj_type'],
        ],
        'adv_md_substitutions' => [
            'pk' => ['obj_type'],
            'columns' => ['obj_type'],
        ],
        'didactic_tpl_sa' => [
            'pk' => ['id', 'obj_type'],
            'columns' => ['obj_type'],
        ],
        'il_cert_template' => [
            'pk' => ['id'],
            'columns' => ['obj_type'],
        ],
        'il_custom_block' => [
            'pk' => ['id'],
            'columns' => ['context_obj_type', 'context_sub_obj_type'],
        ],
        'obj_stat' => [
            'pk' => ['obj_id', 'yyyy', 'mm', 'dd', 'hh'],
            'columns' => ['obj_type'],
        ],
        'obj_stat_log' => [
            'pk' => ['log_id'],
            'columns' => ['obj_type'],
        ],
        'search_command_queue' => [
            'pk' => ['obj_id', 'obj_type', 'sub_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_annotation' => [
            'pk' => ['meta_annotation_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_classification' => [
            'pk' => ['meta_classification_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_context' => [
            'pk' => ['meta_context_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_contribute' => [
            'pk' => ['meta_contribute_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_coverage' => [
            'pk' => ['meta_coverage_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_description' => [
            'pk' => ['meta_description_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_educational' => [
            'pk' => ['meta_educational_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_end_usr_role' => [
            'pk' => ['meta_end_usr_role_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_entity' => [
            'pk' => ['meta_entity_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_format' => [
            'pk' => ['meta_format_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_general' => [
            'pk' => ['meta_general_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_identifier' => [
            'pk' => ['meta_identifier_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_identifier_' => [
            'pk' => ['meta_identifier__id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_keyword' => [
            'pk' => ['meta_keyword_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_language' => [
            'pk' => ['meta_language_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_lifecycle' => [
            'pk' => ['meta_lifecycle_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_location' => [
            'pk' => ['meta_location_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_lr_type' => [
            'pk' => ['meta_lr_type_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_meta_data' => [
            'pk' => ['meta_meta_data_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_meta_schema' => [
            'pk' => ['meta_meta_schema_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_or_composite' => [
            'pk' => ['meta_or_composite_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_relation' => [
            'pk' => ['meta_relation_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_requirement' => [
            'pk' => ['meta_requirement_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_rights' => [
            'pk' => ['meta_rights_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_tar' => [
            'pk' => ['meta_tar_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_taxon' => [
            'pk' => ['meta_taxon_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_taxon_path' => [
            'pk' => ['meta_taxon_path_id'],
            'columns' => ['obj_type'],
        ],
        'il_meta_technical' => [
            'pk' => ['meta_technical_id'],
            'columns' => ['obj_type'],
        ],
    ];

    private const array LEGACY_TEXT_COLUMNS = [
        'obj_type',
        'type',
        'sub_type',
        'context_obj_type',
        'context_sub_obj_type',
    ];

    protected ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Rebrand Item Group (itgr) to Object Group (obgr) in stored data.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdateStepsExecutedObjective(new ilObjectGroupDBUpdateSteps()),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        match (true) {
            $this->remainingObjectData() > 0 => $this->stepObjectData(),
            $this->remainingRbac() > 0 => $this->stepRbac(),
            $this->remainingContainerSorting() > 0 => $this->stepContainerSorting(),
            $this->remainingSettings() > 0 => $this->stepSettings(),
            $this->remainingDesktopItem() > 0 => $this->stepDesktopItem(),
            $this->remainingLegacy() > 0 => $this->stepLegacy(),
            default => null,
        };
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!isset($this->db)) {
            return 0;
        }

        return
            $this->remainingObjectData()
            + $this->remainingRbac()
            + $this->remainingContainerSorting()
            + $this->remainingSettings()
            + $this->remainingDesktopItem()
            + $this->remainingLegacy();
    }

    private function remainingObjectData(): int
    {
        $result = $this->db->queryF(
            'SELECT COUNT(obj_id) AS cnt FROM object_data WHERE type = %s',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $count = (int) ($this->db->fetchObject($result)?->cnt ?? 0);

        $typ_result = $this->db->queryF(
            'SELECT COUNT(obj_id) AS cnt FROM object_data WHERE type = %s AND title = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['typ', self::OLD_TYPE]
        );
        $typ_count = (int) ($this->db->fetchObject($typ_result)?->cnt ?? 0);

        return $count + $typ_count;
    }

    private function stepObjectData(): void
    {
        $result = $this->db->queryF(
            'SELECT obj_id FROM object_data WHERE type = %s LIMIT 1',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $row = $this->db->fetchObject($result);
        if ($row !== null) {
            $this->db->update(
                'object_data',
                ['type' => [ilDBConstants::T_TEXT, self::NEW_TYPE]],
                ['obj_id' => [ilDBConstants::T_INTEGER, (int) $row->obj_id]]
            );
            return;
        }

        $typ_result = $this->db->queryF(
            'SELECT obj_id FROM object_data WHERE type = %s AND title = %s LIMIT 1',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['typ', self::OLD_TYPE]
        );
        $typ_row = $this->db->fetchObject($typ_result);
        if ($typ_row === null) {
            return;
        }

        $this->db->update(
            'object_data',
            [
                'title' => [ilDBConstants::T_TEXT, self::NEW_TYPE],
                'description' => [ilDBConstants::T_TEXT, 'Object Group'],
            ],
            ['obj_id' => [ilDBConstants::T_INTEGER, (int) $typ_row->obj_id]]
        );
    }

    private function remainingRbac(): int
    {
        $ops_result = $this->db->queryF(
            'SELECT COUNT(ops_id) AS cnt FROM rbac_operations WHERE operation = %s',
            [ilDBConstants::T_TEXT],
            [$this->rbacCreateOperationOld()]
        );
        $ops_count = (int) ($this->db->fetchObject($ops_result)?->cnt ?? 0);

        $template_result = $this->db->queryF(
            'SELECT COUNT(*) AS cnt FROM rbac_templates WHERE type = %s',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $template_count = (int) ($this->db->fetchObject($template_result)?->cnt ?? 0);

        return $ops_count + $template_count;
    }

    private function stepRbac(): void
    {
        $result = $this->db->queryF(
            'SELECT ops_id FROM rbac_operations WHERE operation = %s LIMIT 1',
            [ilDBConstants::T_TEXT],
            [$this->rbacCreateOperationOld()]
        );
        $row = $this->db->fetchObject($result);
        if ($row !== null) {
            $this->db->update(
                'rbac_operations',
                [
                    'operation' => [ilDBConstants::T_TEXT, $this->rbacCreateOperationNew()],
                    'description' => [ilDBConstants::T_TEXT, 'Create Object Group'],
                ],
                ['ops_id' => [ilDBConstants::T_INTEGER, (int) $row->ops_id]]
            );
            return;
        }

        $template_result = $this->db->queryF(
            'SELECT rol_id, ops_id, parent FROM rbac_templates WHERE type = %s LIMIT 1',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $template_row = $this->db->fetchObject($template_result);
        if ($template_row === null) {
            return;
        }

        $rol_id = (int) $template_row->rol_id;
        $ops_id = (int) $template_row->ops_id;
        $parent = (int) $template_row->parent;

        if ($this->rbacTemplateRowExists($rol_id, $ops_id, $parent, self::NEW_TYPE)) {
            $this->db->manipulateF(
                'DELETE FROM rbac_templates WHERE rol_id = %s AND ops_id = %s AND parent = %s AND type = %s',
                [
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_TEXT,
                ],
                [$rol_id, $ops_id, $parent, self::OLD_TYPE]
            );
            return;
        }

        $this->db->update(
            'rbac_templates',
            ['type' => [ilDBConstants::T_TEXT, self::NEW_TYPE]],
            [
                'rol_id' => [ilDBConstants::T_INTEGER, $rol_id],
                'ops_id' => [ilDBConstants::T_INTEGER, $ops_id],
                'parent' => [ilDBConstants::T_INTEGER, $parent],
                'type' => [ilDBConstants::T_TEXT, self::OLD_TYPE],
            ]
        );
    }

    private function rbacTemplateRowExists(int $rol_id, int $ops_id, int $parent, string $type): bool
    {
        $result = $this->db->queryF(
            'SELECT 1 FROM rbac_templates WHERE rol_id = %s AND ops_id = %s AND parent = %s AND type = %s LIMIT 1',
            [
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_TEXT,
            ],
            [$rol_id, $ops_id, $parent, $type]
        );

        return $this->db->fetchAssoc($result) !== null;
    }

    private function remainingContainerSorting(): int
    {
        $result = $this->db->queryF(
            'SELECT COUNT(*) AS cnt FROM container_sorting WHERE parent_type = %s',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );

        return (int) ($this->db->fetchObject($result)?->cnt ?? 0);
    }

    private function stepContainerSorting(): void
    {
        $result = $this->db->queryF(
            'SELECT parent_id, parent_type, child_id FROM container_sorting WHERE parent_type = %s LIMIT 1',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return;
        }

        $this->db->update(
            'container_sorting',
            ['parent_type' => [ilDBConstants::T_TEXT, self::NEW_TYPE]],
            [
                'parent_id' => [ilDBConstants::T_INTEGER, (int) $row['parent_id']],
                'parent_type' => [ilDBConstants::T_TEXT, self::OLD_TYPE],
                'child_id' => [ilDBConstants::T_INTEGER, (int) $row['child_id']],
            ]
        );
    }

    private function remainingSettings(): int
    {
        $count = 0;
        foreach (self::SETTINGS_KEY_MAP as $old_key => $_) {
            $result = $this->db->queryF(
                'SELECT COUNT(*) AS cnt FROM settings WHERE keyword = %s',
                [ilDBConstants::T_TEXT],
                [$old_key]
            );
            $count += (int) ($this->db->fetchObject($result)?->cnt ?? 0);
        }

        return $count;
    }

    private function stepSettings(): void
    {
        foreach (self::SETTINGS_KEY_MAP as $old_key => $new_key) {
            $result = $this->db->queryF(
                'SELECT module, value FROM settings WHERE keyword = %s LIMIT 1',
                [ilDBConstants::T_TEXT],
                [$old_key]
            );
            $row = $this->db->fetchAssoc($result);
            if ($row === null) {
                continue;
            }

            if ($this->settingsKeywordExists($new_key, $row['module'])) {
                $this->db->manipulateF(
                    'DELETE FROM settings WHERE module = %s AND keyword = %s',
                    [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                    [$row['module'], $old_key]
                );
            } else {
                $this->db->manipulateF(
                    'UPDATE settings SET keyword = %s WHERE module = %s AND keyword = %s',
                    [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                    [$new_key, $row['module'], $old_key]
                );
            }
            return;
        }
    }

    private function settingsKeywordExists(string $keyword, string $module): bool
    {
        $result = $this->db->queryF(
            'SELECT 1 FROM settings WHERE module = %s AND keyword = %s LIMIT 1',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$module, $keyword]
        );

        return $this->db->fetchAssoc($result) !== null;
    }

    private function remainingDesktopItem(): int
    {
        $result = $this->db->queryF(
            'SELECT COUNT(*) AS cnt FROM desktop_item WHERE type = %s',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );

        return (int) ($this->db->fetchObject($result)?->cnt ?? 0);
    }

    private function stepDesktopItem(): void
    {
        $result = $this->db->queryF(
            'SELECT item_id, user_id FROM desktop_item WHERE type = %s LIMIT 1',
            [ilDBConstants::T_TEXT],
            [self::OLD_TYPE]
        );
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return;
        }

        $this->db->update(
            'desktop_item',
            ['type' => [ilDBConstants::T_TEXT, self::NEW_TYPE]],
            [
                'item_id' => [ilDBConstants::T_INTEGER, (int) $row['item_id']],
                'user_id' => [ilDBConstants::T_INTEGER, (int) $row['user_id']],
            ]
        );
    }

    private function remainingLegacy(): int
    {
        $count = 0;
        foreach (self::LEGACY_TARGETS as $table => $spec) {
            if (!$this->legacyTableReady($table, $spec['columns'])) {
                continue;
            }

            $conditions = implode(
                ' OR ',
                array_map(
                    fn(string $column): string => "{$column} = {$this->db->quote(self::OLD_TYPE, ilDBConstants::T_TEXT)}",
                    $spec['columns']
                )
            );

            $result = $this->db->query("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$conditions}");
            $count += (int) ($this->db->fetchObject($result)?->cnt ?? 0);
        }

        return $count;
    }

    private function stepLegacy(): void
    {
        foreach (self::LEGACY_TARGETS as $table => $spec) {
            if (!$this->legacyTableReady($table, $spec['columns'])) {
                continue;
            }

            $row = $this->fetchLegacyOne($table, $spec);
            if ($row === null) {
                continue;
            }

            if ($this->legacyTypeIsInPrimaryKey($spec) && $this->legacyDestinationExists($table, $spec, $row)) {
                $this->db->manipulate(
                    "DELETE FROM {$table} WHERE {$this->legacySourceWhere($spec, $row)}"
                );
                return;
            }

            $assignments = [];
            foreach ($spec['columns'] as $column) {
                if ((string) $row[$column] !== self::OLD_TYPE) {
                    continue;
                }
                $assignments[] = "{$column} = {$this->db->quote(self::NEW_TYPE, ilDBConstants::T_TEXT)}";
            }
            if ($assignments === []) {
                return;
            }

            $this->db->manipulate(
                "UPDATE {$table} SET " . implode(', ', $assignments) . " WHERE {$this->legacySourceWhere($spec, $row)}"
            );
            return;
        }
    }

    /**
     * @param list<string> $columns
     */
    private function legacyTableReady(string $table, array $columns): bool
    {
        if (!$this->db->tableExists($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if ($this->db->tableColumnExists($table, $column)) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @param array{pk: list<string>, columns: list<string>} $spec
     * @return array<string, mixed>|null
     */
    private function fetchLegacyOne(string $table, array $spec): ?array
    {
        $select = implode(
            ', ',
            array_values(
                array_unique(
                    array_merge(
                        $spec['pk'],
                        $spec['columns']
                    )
                )
            )
        );
        $conditions = implode(
            ' OR ',
            array_map(
                fn(string $column): string => "{$column} = {$this->db->quote(self::OLD_TYPE, ilDBConstants::T_TEXT)}",
                $spec['columns']
            )
        );

        $result = $this->db->query("SELECT {$select} FROM {$table} WHERE {$conditions} LIMIT 1");
        $row = $this->db->fetchAssoc($result);

        return $row === null ? null : $row;
    }

    /**
     * @param array{pk: list<string>, columns: list<string>} $spec
     */
    private function legacyTypeIsInPrimaryKey(array $spec): bool
    {
        return array_intersect($spec['pk'], $spec['columns']) !== [];
    }

    /**
     * @param array{pk: list<string>, columns: list<string>} $spec
     * @param array<string, mixed> $row
     */
    private function legacyDestinationExists(string $table, array $spec, array $row): bool
    {
        $where = [];
        foreach ($spec['pk'] as $column) {
            $value = $row[$column];
            if (in_array($column, $spec['columns'], true) && (string) $value === self::OLD_TYPE) {
                $value = self::NEW_TYPE;
            }
            $where[] = "{$column} = {$this->legacyQuoteColumn($column, $value)}";
        }

        $result = $this->db->query("SELECT 1 FROM {$table} WHERE " . implode(' AND ', $where) . ' LIMIT 1');

        return $this->db->fetchAssoc($result) !== null;
    }

    /**
     * @param array{pk: list<string>, columns: list<string>} $spec
     * @param array<string, mixed> $row
     */
    private function legacySourceWhere(array $spec, array $row): string
    {
        return implode(
            ' AND ',
            array_map(
                fn(string $column): string => "{$column} = {$this->legacyQuoteColumn($column, $row[$column])}",
                $spec['pk']
            )
        );
    }

    private function legacyQuoteColumn(string $column, mixed $value): string
    {
        return in_array($column, self::LEGACY_TEXT_COLUMNS, true)
            ? $this->db->quote((string) $value, ilDBConstants::T_TEXT)
            : $this->db->quote((int) $value, ilDBConstants::T_INTEGER);
    }

    private function rbacCreateOperationOld(): string
    {
        return 'create_' . self::OLD_TYPE;
    }

    private function rbacCreateOperationNew(): string
    {
        return 'create_' . self::NEW_TYPE;
    }
}
