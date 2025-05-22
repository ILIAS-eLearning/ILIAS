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
use ILIAS\MetaData\Search\Clauses\Properties\JoinProperties;
use ILIAS\MetaData\Search\Clauses\Properties\BasicProperties;

class Factory implements FactoryInterface
{
    public function getBasicClause(
        PathInterface $path,
        Mode $mode,
        string $value,
        bool $is_mode_negated = false
    ): ClauseInterface {
        if (count(iterator_to_array($path->steps())) === 0) {
            throw new \ilMDRepositoryException('Paths in search clauses must not be empty.');
        }

        return new Clause(
            false,
            false,
            null,
            new BasicProperties(
                $path,
                $mode,
                $value,
                $is_mode_negated
            )
        );
    }

    public function getJoinedClauses(
        Operator $operator,
        ClauseInterface $first_clause,
        ClauseInterface ...$further_clauses
    ): ClauseInterface {
        if (count($further_clauses) === 0) {
            return $first_clause;
        }
        return new Clause(
            false,
            true,
            new JoinProperties(
                $operator,
                $first_clause,
                ...$further_clauses
            ),
            null
        );
    }

    public function getNegatedClause(ClauseInterface $clause): ClauseInterface
    {
        return new Clause(
            !$clause->isNegated(),
            $clause->isJoin(),
            $clause->joinProperties(),
            $clause->basicProperties()
        );
    }
}
