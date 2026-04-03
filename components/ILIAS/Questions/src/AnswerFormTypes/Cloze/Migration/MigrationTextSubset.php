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
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Definitions\ScoringIdentical;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Setup\Environment;

class MigrationTextSubset implements Migration
{
    use BasicMigrationFunctions;

    public function __construct(
        private readonly TableDefinitions $table_definitions
    ) {
    }

    #[\Override]
    public function getOldQuestionTypeIdentifier(): string
    {
        return 'assTextSubset';
    }

    #[\Override]
    public function getDefinitionClass(): string
    {
        return Definition::class;
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->table_definitions->getTableNameSpace();
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        MigrationInsert $migration_insert
    ): ?MigrationInsert {
        $answer_form_id = $migration_insert->getAnswerFormId();
        $answer_options_insert = null;
        $gaps = [];

        foreach ($this->fetchDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $db_row) {
            if ($gaps === []) {
                $gaps_insert = null;
                for ($i = 0; $i < $db_row->correctanswers; $i++) {
                    $gap_id = $migration_insert->getUuid();
                    $gaps[] = $gap_id;

                    $gaps_insert = $this->buildGapInsertStatement(
                        $this->table_definitions,
                        $migration_insert->getPersistenceFactory(),
                        $migration_insert->getTableNameBuilder(),
                        $gaps_insert,
                        $gap_id,
                        $answer_form_id,
                        $i,
                        'text',
                        null,
                        null,
                        $this->buildNewTextRatingFromOld($db_row->textgap_rating),
                        null,
                        0
                    );
                }

                $migration_insert = $migration_insert->withAdditionalInsert($gaps_insert);
            }

            foreach ($gaps as $gap_id) {
                $answer_options_insert = $this->buildAnswerOptionInsertStatement(
                    $this->table_definitions,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $answer_options_insert,
                    $migration_insert->getUuid(),
                    $gap_id,
                    $db_row->aorder,
                    $db_row->answertext,
                    $db_row->points,
                    null,
                    null
                );
            }
        }

        if (!isset($db_row)) {
            return null;
        }

        return $migration_insert
            ->withAdditionalInsert(
                $this->buildAnswerFormInsertStatement(
                    $this->table_definitions,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $answer_form_id,
                    ScoringIdentical::OnlyScoreDistinct,
                    0
                )
            )->withAdditionalInsert(
                $answer_options_insert
            )->withAdditionalText(
                $this->buildAdditionalTextFromGapsArray($gaps)
            );
    }

    #[\Override]
    public function getConditionsForFeedbackFromOldValues(
        int $answer,
        int $question
    ): null {
        return null;
    }

    private function fetchDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT * FROM qpl_qst_textsubset q' . PHP_EOL
            . 'JOIN qpl_a_textsubset a ON q.question_fi = a.question_fi' . PHP_EOL
            . "WHERE q.question_fi = {$db->quote($old_question_id)}" . PHP_EOL
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }

    private function buildAdditionalTextFromGapsArray(
        array $gaps
    ): string {
        $text_array = [];
        foreach ($gaps as $index => $gap) {
            $position = $index + 1;
            $text_array[] = "{$position}. {{GAP_{$gap->toString()}}}";
        }

        return implode(
            "\n\n",
            $text_array
        );
    }
}
