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

namespace ILIAS\MetaData\Search\Clauses\Properties;

use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;

class JoinProperties implements JoinPropertiesInterface
{
    /**
     * @var ClauseInterface[]
     */
    protected array $sub_clauses;
    protected Operator $operator;

    public function __construct(
        Operator $operator,
        ClauseInterface $first_clause,
        ClauseInterface $second_clause,
        ClauseInterface ...$further_clauses
    ) {
        $this->operator = $operator;
        $this->sub_clauses = [
            $first_clause,
            $second_clause,
            ...$further_clauses
        ];
    }

    public function operator(): Operator
    {
        return $this->operator;
    }

    /**
     * @return ClauseInterface[]
     */
    public function subClauses(): \Generator
    {
        yield from $this->sub_clauses;
    }
}
