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

namespace ILIAS\MetaData\Search\Clauses;

use ILIAS\MetaData\Paths\PathInterface;

interface FactoryInterface
{
    /**
     * Basic search clause with the following semantics:
     * "Find all LOM sets that have at least one element at the
     * end of the path whose value fulfills the condition."
     * The condition is assembled from mode and value parameters,
     * e.g. "equals 'cat'" or "starts with 'a'". Negating the mode
     * leads to e.g. "does not equal 'cat'"
     *
     * Filters on the path are taken into account, with the
     * exception of index filters.
     *
     * @throws \ilMDRepositoryException if the path does not contain any steps
     */
    public function getBasicClause(
        PathInterface $path,
        Mode $mode,
        string $value,
        bool $is_mode_negated = false
    ): ClauseInterface;

    /**
     * Joins multiple clauses with an operator, leading to
     * search clauses like:
     * "Find all LOM sets that have at least one keyword with
     * value 'key' and at least one keyword with value 'different key'
     * and at least one author with value 'name'."
     *
     * When only a single clause is passed, it is returned as is.
     */
    public function getJoinedClauses(
        Operator $operator,
        ClauseInterface $first_clause,
        ClauseInterface ...$further_clauses
    ): ClauseInterface;

    /**
     * Negating a clause does **not** negate the condition on values, e.g.
     * negating "Find all LOM sets with a keyword 'key'" gives
     * "Find all LOM sets with **no** keyword 'key'", and **not**
     * "Find all LOM sets with a keyword that is not 'key'".
     *
     * Further, negation of joined clauses will also apply to the operators
     * accordingly (negating AND-joined clauses will result in negated OR-joined clauses).
     */
    public function getNegatedClause(ClauseInterface $clause): ClauseInterface;
}
