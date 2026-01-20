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
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Questions\Persistence\TableNameSpace;

class MigrationCloze implements Migration
{
    use BasicMigrationFunctions;

    public function __construct(
        private readonly Persistence $persistence,
        private readonly \EvalMath $math
    ) {
    }

    #[\Override]
    public function getOldQuestionIdentifier(): string
    {
        return 'assClozeTest';
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
        $answer_input_mapping = [];
        foreach ($this->fetchDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $db_row) {
            $answer_form_id = $migration_insert->getAnswerFormId();
            if (!isset($answer_input_mapping[$db_row->gap_id])) {
                $answer_input_mapping[$db_row->gap_id] = $migration_insert->getUuid();
                $migration_insert = $migration_insert->withAdditionalInsert(
                    $this->buildGapInsertStatement(
                        $this->persistence,
                        $migration_insert->getTableNameBuilder(),
                        $answer_input_mapping[$db_row->gap_id],
                        $answer_form_id,
                        $db_row->gap_id,
                        $this->buildNewGapTypeIdentifierFromOld((int) $db_row->cloze_type),
                        null,
                        null,
                        $this->buildNewTextRatingFromOld($db_row->textgap_rating),
                        null,
                        $db_row->shuffle === '1' ? 1 : 0
                    )
                );
            }

            $migration_insert = $migration_insert->withAdditionalInsert(
                $this->buildAnswerOptionInsertStatement(
                    $this->persistence,
                    $migration_insert->getTableNameBuilder(),
                    $migration_insert->getUuid(),
                    $answer_input_mapping[$db_row->gap_id],
                    $db_row->aorder,
                    $db_row->answertext,
                    $db_row->points,
                    $this->limitToFloat($this->math, $db_row->lowerlimit),
                    $this->limitToFloat($this->math, $db_row->upperlimit)
                )
            );
        }

        return $migration_insert
            ->withAdditionalInsert(
                $this->buildAnswerFormInsertStatement(
                    $this->persistence,
                    $migration_insert->getTableNameBuilder(),
                    $answer_form_id,
                    $this->buildScoringIdenticalFromOld((int) $db_row->identical_scoring),
                    $db_row->combinations_enabled
                )
            )->withAdditionalTextLegacy(
                $this->replaceGapsAndSantizeLegacyClozeText(
                    '\[gap\].+?\[\/gap\]',
                    $db_row->cloze_text,
                    $answer_input_mapping
                )
            );
    }

    private function fetchDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT *, EXISTS (' . PHP_EOL
                . 'SELECT gap_fi FROM qpl_a_cloze_combi_res' . PHP_EOL
                . 'WHERE question_fi = a.question_fi' . PHP_EOL
            . ') combinations_enabled' . PHP_EOL
            . 'FROM qpl_qst_cloze q' . PHP_EOL
            . 'JOIN qpl_a_cloze a ON q.question_fi = a.question_fi' . PHP_EOL
            . "WHERE q.question_fi = {$db->quote($old_question_id)}" . PHP_EOL
            . 'ORDER BY a.gap_id, a.aorder'
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }

    private function buildNewGapTypeIdentifierFromOld(
        int $old_gap_type
    ): string {
        return match($old_gap_type) {
            \assClozeGap::TYPE_TEXT => 'text',
            \assClozeGap::TYPE_SELECT => 'select',
            \assClozeGap::TYPE_NUMERIC => 'numeric'
        };
    }

    private function buildNewTextRatingFromOld(
        string $old_text_rating
    ): TextMatchingOptions {
        return match($old_text_rating) {
            \assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE => TextMatchingOptions::CaseInsensitive,
            \assClozeGap::TEXTGAP_RATING_CASESENSITIVE => TextMatchingOptions::CaseSensitive,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1 => TextMatchingOptions::Levenstein1,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2 => TextMatchingOptions::Levenstein2,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3 => TextMatchingOptions::Levenstein3,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4 => TextMatchingOptions::Levenstein4,
            \assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5 => TextMatchingOptions::Levenstein5
        };
    }
}
