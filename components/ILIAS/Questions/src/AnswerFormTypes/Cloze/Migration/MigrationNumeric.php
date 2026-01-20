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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Migration;

use ILIAS\Questions\AnswerForm\Migration\Migration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\Persistence\TableNameSpace;

class MigrationNumeric implements Migration
{
    use BasicMigrationFunctions;

    public function __construct(
        private readonly Persistence $persistence,
        private readonly \EvalMath $math
    ) {
        $this->math->suppress_errors = true;
    }

    #[\Override]
    public function getOldQuestionIdentifier(): string
    {
        return 'assNumeric';
    }

    #[\Override]
    public function getDefinitionClass(): string
    {
        return Definition::class;
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->persistence->getTableNameSpace();
    }

    #[\Override]
    public function buildInsertStatement(
        MigrationInsert $migration_insert
    ): MigrationInsert {
        $db_row = $this->fetchDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        )->current();

        $answer_form_id = $migration_insert->getAnswerFormId();
        $gap_id = $migration_insert->getUuid();
        return $migration_insert->withAdditionalInsert(
            $this->buildGapInsertStatement(
                $this->persistence,
                $migration_insert->getTableNameBuilder(),
                $gap_id,
                $answer_form_id,
                0,
                'numeric',
                null,
                0.0001,
                null,
                null,
                null
            )
        )->withAdditionalInsert(
            $this->buildAnswerOptionInsertStatement(
                $this->persistence,
                $migration_insert->getTableNameBuilder(),
                $migration_insert->getUuid(),
                $gap_id,
                0,
                '',
                $db_row->points,
                $this->limitToFloat($this->math, $db_row->lowerlimit),
                $this->limitToFloat($this->math, $db_row->upperlimit)
            )
        )->withAdditionalInsert(
            $this->buildAnswerFormInsertStatement(
                $this->persistence,
                $migration_insert->getTableNameBuilder(),
                $answer_form_id,
                ScoringIdentical::ScoreAll,
                0
            )
        )->withAdditionalTextLegacy(
            '{{' . Gap::GAP_PLACEHOLDER_NAME . '_' . array_shift($gap_id->toString()) . '}}'
        );
    }

    private function fetchDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT points, lowerlimit, upperlimit FROM qpl_num_range' . PHP_EOL
            . "WHERE q.question_fi = {$db->quote($old_question_id)}"
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }
}
