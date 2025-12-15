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

namespace ILIAS\Questions\Question\Persistence;

class Manipulate
{
    private array $statements;

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function withAdditionalStatement(
        Insert|Update $statement
    ): self {
        $clone = clone $this;
        $clone->statements[] = $statement;
        return $clone;
    }

    public function run(): void
    {
        $atom_query = $this->db->buildAtomQuery();

        $manipulates = [];
        foreach ($this->statements as $statement) {
            $atom_query->addTableLock($statement->getTableName());
            $manipulates[] = $statement->toManipulateString($this->db);
        }
        $atom_query->addQueryCallable(
            function () use ($manipulates): void {
                foreach ($manipulates as $manipulate) {
                    $this->db->manipulate($manipulate);
                }
            }
        );
        $atom_query->run();
    }
}
