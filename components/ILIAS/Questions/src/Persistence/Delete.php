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
 * ******************************************************************* */
declare(strict_types=1);

namespace ILIAS\Questions\Persistence;

class Delete
{
    /**
     * @param array<\ILIAS\Questions\Persistence\Where> $where
     */
    public function __construct(
        private readonly array $where
    ) {
        if ($where === []) {
            throw new \InvalidArgumentException(
                "There MUST be at least one Where statement."
            );
        }

        $table_name = $where[0]->getTableName();
        foreach ($where as $w) {
            if ($w->getTableName() !== $table_name) {
                throw new \InvalidArgumentException(
                    "All Where statements MUST belong to the same Table."
                );
            }
        }
    }

    public function getTableToLock(): string
    {
        return $this->where[0]->getTableName();
    }

    public function toManipulateString(
        \ilDBInterface $db
    ): string {
        return "DELETE FROM {$this->getTableToLock()}" . PHP_EOL
            . $this->buildWhereString($db);
    }

    private function buildWhereString(
        \ilDBInterface $db
    ): string {
        $values = [];
        return sprintf(
            array_reduce(
                $this->where,
                function (?string $c, Where $v) use ($db, &$values): string {
                    $quoted_value = $v->getRight()->getQuotedValue($db);
                    if (is_array($quoted_value)) {
                        $values = [
                            ...$values,
                            ...array_values($quoted_value)
                        ];
                    } else {
                        $values[] = $quoted_value;
                    }

                    if ($c === null) {
                        return "WHERE {$v->toSql()}" . PHP_EOL;
                    }

                    return "{$c}{$v->getLogicalOperator()->value} {$v->toSql()}" . PHP_EOL;
                }
            ) ?? '',
            ...$values
        );
    }
}
