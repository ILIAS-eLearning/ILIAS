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

namespace ILIAS\Questions\Persistence;

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Persistence;

class Manipulate
{
    private array $statements = [];

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly ManipulationType $type
    ) {
    }

    public function getManipulationType(): ManipulationType
    {
        return $this->type;
    }

    public function getPersistenceForDefinitionClass(
        string $definition_class
    ): Persistence {
        return $this->answer_form_factory
            ->getDefinitionForClass($definition_class)
            ->getPersistence();
    }

    public function getTableNameBuilder(
        string $definition_class
    ): TableNameBuilder {
        return new TableNameBuilder(
            $this->answer_form_factory
                ->getDefinitionForClass($definition_class)
                ->getPersistence()
                ->getPublicNameSpace()
        );
    }

    public function withAdditionalStatement(
        Insert|Update|Replace|Delete $statement
    ): self {
        $clone = clone $this;
        $clone->statements[] = $statement;
        return $clone;
    }

    public function run(): void
    {
        if ($this->statements === []) {
            return;
        }

        $atom_query = $this->db->buildAtomQuery();

        $manipulates = [];
        $locked_tables = [];
        foreach ($this->statements as $statement) {
            $table_to_lock = $statement->getTableToLock();
            if (!in_array($table_to_lock, $locked_tables)) {
                $atom_query->addTableLock($table_to_lock);
                $locked_tables[] = $table_to_lock;
            }
            $manipulates[] = $statement->toManipulateString($this->db);
        }
        $atom_query->addQueryCallable(
            function (\ilDBInterface $db) use ($manipulates): void {
                foreach ($manipulates as $manipulate) {
                    $db->manipulate($manipulate);
                }
            }
        );
        $atom_query->run();
    }
}
