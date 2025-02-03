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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Paths;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\StructureNavigatorInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface as LOMVocabInitiator;
use ILIAS\MetaData\Paths\Filters\FilterInterface as PathFilter;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Repository\Utilities\Queries\TableNamesHandler;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;

class DatabasePathsParser implements DatabasePathsParserInterface
{
    use TableNamesHandler;

    protected const JOIN_TABLE = 'join_table';
    protected const JOIN_CONDITION = 'join_condition';
    protected const COLUMN_NAME = 'column_name';

    /**
     * @var string[]
     */
    protected array $path_joins_by_path = [];

    /**
     * @var array[] sub arrays contain strings
     */
    protected array $additional_conditions_by_path = [];

    protected int $path_number = 1;

    /**
     * @var string[]
     */
    protected array $columns_by_path = [];

    protected bool $force_join_to_base_table = false;

    /**
     * Just for quoting.
     */
    protected \ilDBInterface $db;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        \ilDBInterface $db,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
    ) {
        $this->db = $db;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
    }

    /**
     * Make sure that you add paths before calling this.
     */
    public function getSelectForQuery(): string
    {
        $from_expression = '';
        $base_table = '';
        if (empty($this->path_joins_by_path)) {
            throw new \ilMDRepositoryException('No tables found for search.');
        } elseif (
            count($this->path_joins_by_path) === 1 &&
            !$this->force_join_to_base_table
        ) {
            $base_table = 'p1t1';
            $from_expression = array_values($this->path_joins_by_path)[0];
            $path = array_keys($this->path_joins_by_path)[0];
            if (isset($this->additional_conditions_by_path[$path])) {
                $from_expression .= ' WHERE ' .
                    implode(' AND ', $this->additional_conditions_by_path[$path]);
            }
        } else {
            $base_table = 'base';
            $from_expression = 'il_meta_general AS base';
            $path_number = 1;
            foreach ($this->path_joins_by_path as $path => $join) {
                $condition = $this->getBaseJoinConditionsForTable(
                    'base',
                    'p' . $path_number . 't1',
                );
                if (isset($this->additional_conditions_by_path[$path])) {
                    $condition .= ' AND ' .
                        implode(' AND ', $this->additional_conditions_by_path[$path]);
                }
                $from_expression .= ' LEFT JOIN (' . $join . ') ON ' . $condition;
                $path_number++;
            }
        }

        return 'SELECT ' . $this->quoteIdentifier($base_table) . '.rbac_id, ' .
            $this->quoteIdentifier($base_table) . '.obj_id, ' .
            $this->quoteIdentifier($base_table) . '.obj_type FROM ' . $from_expression;
    }

    public function addPathAndGetColumn(
        PathInterface $path,
        bool $force_join_to_base_table
    ): string {
        if (!$this->force_join_to_base_table) {
            $this->force_join_to_base_table = $force_join_to_base_table;
        }

        $path_string = $path->toString();
        if (isset($this->columns_by_path[$path_string])) {
            return $this->columns_by_path[$path_string];
        }

        $data_column_name = '';

        $tables = [];
        $conditions = [];
        foreach ($this->collectJoinInfos($path, $this->path_number) as $type => $info) {
            if ($type === self::JOIN_TABLE && !empty($info)) {
                $tables[] = $info;
            }
            if ($type === self::JOIN_CONDITION && !empty($info)) {
                $conditions[] = $info;
            }
            if ($type === self::COLUMN_NAME && !empty($info)) {
                $data_column_name = $info;
            }
        }

        if (count($tables) === 1 && !empty($conditions)) {
            $this->path_joins_by_path[$path_string] = $tables[0];
            /**
             * If there is just one table on the path, additional conditions
             * e.g. from filters can't be treated as a join condition on the
             * path, so it has to be passed one layer up.
             */
            $this->additional_conditions_by_path[$path_string] = $conditions;
            $this->path_number++;
        } elseif (!empty($tables)) {
            $join = implode(' JOIN ', $tables);
            if (!empty($conditions)) {
                $join .= ' ON ' . implode(' AND ', $conditions);
            }
            $this->path_joins_by_path[$path_string] = $join;
            $this->path_number++;
        }

        return $this->columns_by_path[$path_string] = $data_column_name;
    }

    public function getTableAliasForFilters(): string
    {
        if (empty($this->path_joins_by_path)) {
            throw new \ilMDRepositoryException('No tables found for search.');
        } elseif (
            count($this->path_joins_by_path) === 1 &&
            !$this->force_join_to_base_table
        ) {
            return 'p1t1';
        }
        return 'base';
    }

    /**
     * @return string[], key is either self::JOIN_TABLE, self::JOIN_CONDITION or self::COLUMN_NAME
     */
    protected function collectJoinInfos(
        PathInterface $path,
        int $path_number
    ): \Generator {
        $navigator = $this->getNavigatorForPath($path);
        $table_aliases = [];
        $current_tag = null;
        $current_table = '';
        $table_number = 1;

        $depth = 0;
        while ($navigator->hasNextStep()) {
            if ($depth > 20) {
                throw new \ilMDStructureException('LOM Structure is nested to deep.');
            }

            $navigator = $navigator->nextStep();
            $current_tag = $this->getTagForCurrentStepOfNavigator($navigator);

            if ($current_tag?->table() && $current_table !== $current_tag?->table()) {
                $parent_table = $current_table;
                $current_table = $current_tag->table();
                $this->checkTable($current_table);

                /**
                 * If the step goes back to a previous table, reuse the same
                 * alias, but if it goes down again to the same table, use a new
                 * alias (since path filter might mean you're on different
                 * branches now).
                 */
                if ($navigator->currentStep()->name() === StepToken::SUPER) {
                    $alias = $table_aliases[$current_table];
                } else {
                    $alias = 'p' . $path_number . 't' . $table_number;
                    $table_aliases[$current_table] = $alias;
                    $table_number++;

                    yield self::JOIN_TABLE => $this->quoteIdentifier($this->table($current_table)) .
                        ' AS ' . $this->quoteIdentifier($alias);
                }

                if (!$current_tag->hasParent()) {
                    yield self::JOIN_CONDITION => $this->getBaseJoinConditionsForTable(
                        'p' . $path_number . 't1',
                        $alias
                    );
                } else {
                    yield self::JOIN_CONDITION => $this->getBaseJoinConditionsForTable(
                        'p' . $path_number . 't1',
                        $alias,
                        $table_aliases[$parent_table],
                        $parent_table,
                        $current_tag->parent()
                    );
                }
            }

            foreach ($navigator->currentStep()->filters() as $filter) {
                yield self::JOIN_CONDITION => $res = $this->getJoinConditionFromPathFilter(
                    $table_aliases[$current_table],
                    $current_table,
                    $current_tag?->hasData() ? $current_tag->dataField() : '',
                    $filter
                );
            }

            $depth++;
        }

        yield self::COLUMN_NAME => $this->getDataColumn(
            $this->quoteIdentifier($table_aliases[$current_table]),
            $current_tag?->hasData() ? $current_tag->dataField() : ''
        );
    }

    protected function getBaseJoinConditionsForTable(
        string $first_table_alias,
        string $table_alias,
        ?string $parent_table_alias = null,
        ?string $parent_table = null,
        ?string $parent_type = null
    ): string {
        $table_alias = $this->quoteIdentifier($table_alias);
        $first_table_alias = $this->quoteIdentifier($first_table_alias);
        $conditions = [];

        if ($table_alias !== $first_table_alias) {
            $conditions[] = $first_table_alias . '.rbac_id = ' . $table_alias . '.rbac_id';
            $conditions[] = $first_table_alias . '.obj_id = ' . $table_alias . '.obj_id';
            $conditions[] = $first_table_alias . '.obj_type = ' . $table_alias . '.obj_type';
        }

        if (!is_null($parent_table_alias) && !is_null($parent_table)) {
            $parent_id_column = $parent_table_alias . '.' .
                $this->quoteIdentifier($this->IDName($parent_table));
            $conditions[] = $parent_id_column . ' = ' . $table_alias . '.parent_id';
        }
        if (!is_null($parent_type)) {
            $conditions[] = $this->quoteText($parent_type) .
                ' = ' . $table_alias . '.parent_type';
        }

        return implode(' AND ', $conditions);
    }

    protected function getJoinConditionFromPathFilter(
        string $table_alias,
        string $table,
        string $data_field,
        PathFilter $filter
    ): string {
        $table_alias = $this->quoteIdentifier($table_alias);
        $quoted_values = [];
        foreach ($filter->values() as $value) {
            $quoted_values[] = $filter->type() === FilterType::DATA ?
                $this->quoteText($value) :
                $this->quoteInteger((int) $value);
        }

        if (empty($quoted_values)) {
            return '';
        }

        switch ($filter->type()) {
            case FilterType::NULL:
                return '';

            case FilterType::MDID:
                $column = $table_alias . '.' . $this->quoteIdentifier($this->IDName($table));
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            case FilterType::INDEX:
                // not supported
                return '';

            case FilterType::DATA:
                $column = $this->getDataColumn($table_alias, $data_field);
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            default:
                throw new \ilMDRepositoryException('Unknown filter type: ' . $filter->type()->value);
        }
    }

    /**
     * Direct_data is only needed to make vocab sources work until
     * controlled vocabularies are implemented.
     */
    protected function getDataColumn(
        string $quoted_table_alias,
        string $data_field
    ): string {
        $column = $this->quoteText('');
        if ($data_field !== '') {
            $column = 'COALESCE(' . $quoted_table_alias . '.' . $this->quoteIdentifier($data_field) . ", '')";
        }
        return $column;
    }

    protected function getNavigatorForPath(PathInterface $path): StructureNavigatorInterface
    {
        return $this->navigator_factory->structureNavigator(
            $path,
            $this->structure->getRoot()
        );
    }

    protected function getTagForCurrentStepOfNavigator(StructureNavigatorInterface $navigator): ?TagInterface
    {
        return $this->dictionary->tagForElement($navigator->element());
    }

    protected function getDataTypeForCurrentStepOfNavigator(StructureNavigatorInterface $navigator): Type
    {
        return $navigator->element()->getDefinition()->dataType();
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return $this->db->quoteIdentifier($identifier);
    }

    protected function quoteText(string $text): string
    {
        return $this->db->quote($text, \ilDBConstants::T_TEXT);
    }

    protected function quoteInteger(int $integer): string
    {
        return $this->db->quote($integer, \ilDBConstants::T_INTEGER);
    }
}
