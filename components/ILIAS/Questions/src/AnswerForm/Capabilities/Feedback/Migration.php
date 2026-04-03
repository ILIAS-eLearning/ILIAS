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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\AnswerForm\Capabilities\Migration as MigrationInterface;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerForm\Migration\SanitizeLegacyText;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Setup\Environment;

class Migration implements MigrationInterface
{
    use SanitizeLegacyText;

    public function __construct(
        private readonly TableDefinitions $table_definitions
    ) {
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->table_definitions->getTableNameSpace();
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert
    ): ?MigrationInsert {
        $generic_feedback_insert = $this->buildGenericFeedbackInsert(
            $migration_insert->getPersistenceFactory(),
            $migration_insert
        );
        if ($generic_feedback_insert !== null) {
            $migration_insert = $migration_insert
                ->withAdditionalInsert(
                    $generic_feedback_insert
                );
        }

        $specific_feedback_insert = $this->buildSpecificFeedbackInsert(
            $migration_insert->getPersistenceFactory(),
            $answer_form_migration,
            $migration_insert
        );
        if ($specific_feedback_insert !== null) {
            $migration_insert = $migration_insert
                ->withAdditionalInsert(
                    $specific_feedback_insert
                );
        }

        return $migration_insert;
    }

    private function fetchGenericFeedbackDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->queryF(
            'SELECT * FROM qpl_fb_generic WHERE question_fi = %s ORDER BY question_fi',
            [\ilDBConstants::T_INTEGER],
            [$old_question_id]
        );

        yield from $this->fetchFeedbacksForQuestions($query);
    }

    private function fetchSpecificFeedbackDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->queryF(
            'SELECT * FROM qpl_fb_specific WHERE question_fi = %s ORDER BY question_fi',
            [\ilDBConstants::T_INTEGER],
            [$old_question_id]
        );

        yield from $this->fetchFeedbacksForQuestions($query);
    }

    private function fetchFeedbacksForQuestions(
        \ilDBStatement $query
    ): \Generator {
        $feedbacks_for_question = null;
        while (($row = $db->fetchObject($query)) !== null) {
            if ($feedbacks_for_question === null) {
                $feedbacks_for_question = [$row];
                continue;
            }

            if ($feedbacks_for_question['question_fi'] === $row['question_fi']) {
                $feedbacks_for_question[] = $row;
                continue;
            }

            yield $feedbacks_for_question;
            $feedbacks_for_question = [$row];
        }

        yield $feedbacks_for_question;
    }

    private function buildGenericFeedbackInsert(
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert
    ): Insert {
        $insert = null;
        foreach ($this->fetchGenericFeedbackDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $feedback_for_question) {
            $insert = $this->addGenericFeedbackToInsert(
                $persistence_factory,
                $migration_insert,
                $insert,
                $feedback_for_question
            );
        }

        return $insert;
    }

    private function addGenericFeedbackToInsert(
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert,
        Insert $insert,
        array $feedback_for_question
    ): Insert {
        $values = $this->buildNewGenericFeedbackValuesFromOld(
            $persistence_factory,
            $migration_insert,
            $feedback_for_question
        );

        if ($insert === null) {
            return $persistence_factory->insert(
                $this->table_definitions->getColumns(
                    $migration_insert->getTableNameBuilder(),
                    TableTypes::FeedbackGeneric
                ),
                $values
            );
        }

        return $insert->withAdditionalValues($values);
    }

    private function buildNewGenericFeedbackValuesFromOld(
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert,
        array $feedback_for_question
    ): array {
        $feedback_best_response = '';
        $feedback_other_response = '';
        foreach ($feedback_for_question as $feedback_row) {
            if ($feedback_row['correctness'] === '1') {
                $feedback_best_response = $feedback_row['feedback'];
                continue;
            }
            $feedback_other_response = $feedback_row['feedback'];
        }

        return [
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $migration_insert->getAnswerFormId()->toString()
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                ''
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->sanitizeLegacyText(
                    $migration_insert->getDb(),
                    $feedback_best_response,
                    $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                )
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                ''
            ),
            $persistence_factory->value(
                \ilDBConstants::T_TEXT,
                $this->sanitizeLegacyText(
                    $migration_insert->getDb(),
                    $feedback_other_response,
                    $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                )
            ),
        ];
    }

    private function buildSpecificFeedbackInsert(
        PersistenceFactory $persistence_factory,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert
    ): Insert {
        $insert = null;
        foreach ($this->fetchSpecificFeedbackDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $feedback_for_question) {
            $insert = $this->addSpecificFeedbackToInsert(
                $persistence_factory,
                $answer_form_migration,
                $migration_insert,
                $insert,
                $feedback_for_question
            );
        }

        return $insert;
    }

    private function addSpecificFeedbackToInsert(
        PersistenceFactory $persistence_factory,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert,
        Insert $insert,
        array $feedback_for_question
    ): Insert {
        $values = $this->buildNewSpecificFeedbackValuesFromOld(
            $persistence_factory,
            $answer_form_migration,
            $migration_insert,
            $feedback_for_question
        );

        if ($values === null) {
            return $insert;
        }

        if ($insert === null) {
            $insert = $persistence_factory->insert(
                $this->table_definitions->getColumns(
                    $migration_insert->getTableNameBuilder(),
                    TableTypes::FeedbackSpecific
                ),
                array_shift($values)
            );
        }

        return array_reduce(
            $values,
            fn(MigrationInsert $c, array $v): MigrationInsert
                => $c->withAdditionalValues($v),
            $insert
        );
    }

    private function buildNewSpecificFeedbackValuesFromOld(
        PersistenceFactory $persistence_factory,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert,
        array $feedback_for_question
    ): ?array {
        $parent_id = $answer_form_migration->getNewAnswerInputIdForOld(
            $feedback_for_question
        );
        $conditions = $answer_form_migration->getConditionForFeedbackFromOldValues(
            $feedback_for_question['answer'],
            $feedback_for_question['question']
        ) ?? '';

        if ($parent_id === null
            || $conditions === null) {
            return null;
        }

        return array_map(
            fn(string $v): array => [
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $migration_insert->getUuid()->toString()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $migration_insert->getAnswerFormId()->toString()
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $parent_id
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $v
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    ''
                ),
                $persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $this->sanitizeLegacyText(
                        $migration_insert->getDb(),
                        $feedback_for_question['feedback'],
                        $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                    )
                ),
            ],
            $conditions
        );
    }

    private function buildRangeValue(
        bool $is_numeric,
        string $value
    ): ?string {
        if ($is_numeric === null) {
            return null;
        }

        if ($value === 'out_of_bounds') {
            return Range::OutOfRange->value;
        }

        return Range::InRange->value;
    }
}
