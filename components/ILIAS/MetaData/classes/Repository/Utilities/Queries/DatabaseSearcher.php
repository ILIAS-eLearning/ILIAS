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

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactoryInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserInterface;
use ILIAS\MetaData\Search\Filters\Placeholder;
use ILIAS\MetaData\Search\Clauses\Properties\BasicPropertiesInterface;

class DatabaseSearcher implements DatabaseSearcherInterface
{
    protected RessourceIDFactoryInterface $ressource_factory;
    protected DatabasePathsParserFactoryInterface $paths_parser_factory;
    protected \ilDBInterface $db;

    public function __construct(
        RessourceIDFactoryInterface $ressource_factory,
        DatabasePathsParserFactoryInterface $paths_parser_factory,
        \ilDBInterface $db
    ) {
        $this->ressource_factory = $ressource_factory;
        $this->paths_parser_factory = $paths_parser_factory;
        $this->db = $db;
    }

    public function search(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator {
        $paths_parser = $this->paths_parser_factory->forSearch();
        $where = $this->getClauseForQueryHaving($clause, $paths_parser);
        $quoted_table_alias = $this->quoteIdentifier($paths_parser->getTableAliasForFilters());

        $query = $paths_parser->getSelectForQuery() . ' GROUP BY ' . $quoted_table_alias . '.rbac_id, ' .
            $quoted_table_alias . '.obj_id, ' . $quoted_table_alias . '.obj_type HAVING ' . $where .
            $this->getFiltersForQueryHaving($quoted_table_alias, ...$filters) .
            ' ORDER BY rbac_id, obj_id, obj_type' . $this->getLimitAndOffsetForQuery($limit, $offset);

        foreach ($this->queryDB($query) as $row) {
            yield $this->ressource_factory->ressourceID(
                (int) $row['rbac_id'],
                (int) $row['obj_id'],
                (string) $row['obj_type']
            );
        }
    }

    protected function getFiltersForQueryHaving(
        string $quoted_table_alias,
        FilterInterface ...$filters
    ): string {
        $filter_where = [];
        foreach ($filters as $filter) {
            $filter_values = [];
            if ($val = $this->getFilterValueForCondition($quoted_table_alias, $filter->objID())) {
                $filter_values[] = $quoted_table_alias . '.rbac_id = ' . $val;
            }
            if ($val = $this->getFilterValueForCondition($quoted_table_alias, $filter->subID())) {
                $filter_values[] = $quoted_table_alias . '.obj_id = ' . $val;
            }
            if ($val = $this->getFilterValueForCondition($quoted_table_alias, $filter->type())) {
                $filter_values[] = $quoted_table_alias . '.obj_type = ' . $val;
            }
            if (!empty($filter_values)) {
                $filter_where[] = '(' . implode(' AND ', $filter_values) . ')';
            }
        }

        if (empty($filter_where)) {
            return '';
        }

        return ' AND (' . implode(' OR ', $filter_where) . ')';
    }

    protected function getFilterValueForCondition(
        string $quoted_table_alias,
        string|int|Placeholder $value
    ): string {
        if (is_int($value)) {
            return $this->quoteInteger($value);
        }
        if (is_string($value)) {
            return $this->quoteText($value);
        }

        switch ($value) {
            case Placeholder::OBJ_ID:
                return $quoted_table_alias . '.rbac_id';

            case Placeholder::SUB_ID:
                return $quoted_table_alias . '.obj_id';

            case Placeholder::TYPE:
                return $quoted_table_alias . '.obj_type';

            case Placeholder::ANY:
            default:
                return '';
        }
    }

    protected function getLimitAndOffsetForQuery(?int $limit, ?int $offset): string
    {
        $query_limit = '';
        if (!is_null($limit) || !is_null($offset)) {
            $limit = is_null($limit) ? PHP_INT_MAX : $limit;
            $query_limit = ' LIMIT ' . $this->quoteInteger($limit);
        }
        $query_offset = '';
        if (!is_null($offset)) {
            $query_offset = ' OFFSET ' . $this->quoteInteger($offset);
        }
        return $query_limit . $query_offset;
    }

    protected function getClauseForQueryHaving(
        ClauseInterface $clause,
        DatabasePathsParserInterface $paths_parser,
        int $depth = 0
    ): string {
        if ($depth > 50) {
            throw new \ilMDRepositoryException('Search clause is nested to deep.');
        }

        if (!$clause->isJoin()) {
            return $this->getBasicClauseForQueryWhere(
                $clause->basicProperties(),
                $clause->isNegated(),
                $paths_parser
            );
        }

        $join_props = $clause->joinProperties();

        $sub_clauses_for_query = [];
        foreach ($join_props->subClauses() as $sub_clause) {
            $sub_clauses_for_query[] = $this->getClauseForQueryHaving($sub_clause, $paths_parser, $depth + 1);
        }

        switch ($join_props->operator()) {
            case Operator::AND:
                $operator_for_query = 'AND';
                break;

            case Operator::OR:
                $operator_for_query = 'OR';
                break;

            default:
                throw new \ilMDRepositoryException('Invalid search operator.');
        }

        $negation = '';
        if ($clause->isNegated()) {
            $negation = 'NOT ';
        }

        return $negation . '(' . implode(' ' . $operator_for_query . ' ', $sub_clauses_for_query) . ')';
    }

    protected function getBasicClauseForQueryWhere(
        BasicPropertiesInterface $basic_props,
        bool $is_clause_negated,
        DatabasePathsParserInterface $paths_parser
    ): string {
        switch ($basic_props->mode()) {
            case Mode::EQUALS:
                $comparison = '= ' .
                    $this->quoteText($basic_props->value());
                break;

            case Mode::CONTAINS:
                $comparison = 'LIKE ' .
                    $this->quoteText('%' . $basic_props->value() . '%');
                break;

            case Mode::STARTS_WITH:
                $comparison = 'LIKE ' .
                    $this->quoteText($basic_props->value() . '%');
                break;

            case Mode::ENDS_WITH:
                $comparison = 'LIKE ' .
                    $this->quoteText('%' . $basic_props->value());
                break;

            default:
                throw new \ilMDRepositoryException('Invalid search mode.');
        }

        $mode_negation = '';
        if ($basic_props->isModeNegated()) {
            $mode_negation = 'NOT ';
        }
        $clause_negation = '';
        if ($is_clause_negated) {
            $clause_negation = 'NOT ';
        }

        $needs_join_to_base_table = $basic_props->value() === '' || $is_clause_negated;

        return $clause_negation . 'COUNT(CASE WHEN ' . $mode_negation .
            $paths_parser->addPathAndGetColumn($basic_props->path(), $needs_join_to_base_table) .
            ' ' . $comparison . ' THEN 1 END) > 0';
    }

    protected function queryDB(string $query): \Generator
    {
        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            yield $row;
        }
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
