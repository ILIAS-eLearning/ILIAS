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
        private readonly Table $table,
        private readonly array $where
    ) {
    }

    public function getTableToLock(): string
    {
        return $this->table->getName();
    }

    public function toManipulateString(
        \ilDBInterface $db
    ): string {
        return "DELETE FROM {$this->table->getName()}" . PHP_EOL
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
                        $values = array_merge(
                            $values,
                            array_values($quoted_value)
                        );
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
