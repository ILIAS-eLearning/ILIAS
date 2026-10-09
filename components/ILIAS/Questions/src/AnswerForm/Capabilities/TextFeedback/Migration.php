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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Migration as MigrationInterface;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerForm\Migration\SanitizeLegacyText;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Database\FieldDefinition;
use ILIAS\Setup\Environment;

class Migration implements MigrationInterface
{
    use SanitizeLegacyText;

    public function __construct(
        private readonly TableDefinitions $table_definitions
    ) {
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert
    ): MigrationInsert {
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
            [FieldDefinition::T_INTEGER],
            [$old_question_id]
        );

        yield from $this->fetchFeedbacksForQuestions($db, $query);
    }

    private function fetchSpecificFeedbackDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->queryF(
            'SELECT * FROM qpl_fb_specific WHERE question_fi = %s ORDER BY question_fi',
            [FieldDefinition::T_INTEGER],
            [$old_question_id]
        );

        yield from $this->fetchFeedbacksForQuestions($db, $query);
    }

    private function fetchFeedbacksForQuestions(
        \ilDBInterface $db,
        \ilDBStatement $query
    ): \Generator {
        $feedbacks_for_question = null;
        while (($row = $db->fetchAssoc($query)) !== null) {
            if ($feedbacks_for_question === null) {
                $feedbacks_for_question = [$row];
                continue;
            }

            if ($feedbacks_for_question[0]['question_fi'] === $row['question_fi']) {
                $feedbacks_for_question[] = $row;
                continue;
            }

            yield $feedbacks_for_question;
            $feedbacks_for_question = [$row];
        }

        if ($feedbacks_for_question !== null) {
            yield $feedbacks_for_question;
        }
    }

    private function buildGenericFeedbackInsert(
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert
    ): ?Insert {
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
        ?Insert $insert,
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
                    $migration_insert->getTableNameBuilder(null),
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
        $page_editor_used = $migration_insert->wasIliasPageEditorUsedForAdditionalTexts();

        $feedback_best_response = '';
        $feedback_other_response = '';
        foreach ($feedback_for_question as $feedback_row) {
            $feedback_text = $this->generateTextValueForFeedback(
                $migration_insert->getDb(),
                $page_editor_used,
                $feedback_row['feedback_id'],
                $feedback_row['feedback']
            );
            if ($feedback_row['correctness'] === '1') {
                $feedback_best_response = $feedback_text;
                continue;
            }
            $feedback_other_response = $feedback_text;
        }

        return [
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $migration_insert->getAnswerFormId()->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                ''
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $feedback_best_response,
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                ''
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $feedback_other_response
            )
        ];
    }

    private function buildSpecificFeedbackInsert(
        PersistenceFactory $persistence_factory,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert
    ): ?Insert {
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
        ?Insert $insert,
        array $feedback_for_question
    ): ?Insert {
        $values = $this->buildNewSpecificFeedbackValuesFromOld(
            $persistence_factory,
            $answer_form_migration,
            $migration_insert,
            $feedback_for_question
        );

        if ($values === []) {
            return $insert;
        }

        if ($insert === null) {
            $insert = $persistence_factory->insert(
                $this->table_definitions->getColumns(
                    $migration_insert->getTableNameBuilder(null),
                    TableTypes::FeedbackSpecific
                ),
                array_shift($values)
            );
        }

        return array_reduce(
            $values,
            fn(Insert $c, array $v): Insert
                => $c->withAdditionalValues($v),
            $insert
        );
    }

    private function buildNewSpecificFeedbackValuesFromOld(
        PersistenceFactory $persistence_factory,
        AnswerFormMigration $answer_form_migration,
        MigrationInsert $migration_insert,
        array $feedback_for_question
    ): array {
        $conditions = array_reduce(
            $feedback_for_question,
            function (
                array $c,
                array $v
            ) use (
                $answer_form_migration,
                $migration_insert
            ): array {
                $parent_id = $answer_form_migration->getNewAnswerInputIdForOld(
                    $v['question']
                );

                $conditions = $answer_form_migration->getConditionsForFeedbackFromOldValues(
                    $v['answer'],
                    $v['question']
                );

                if ($parent_id === null || $conditions === null) {
                    return $c;
                }

                if (!array_key_exists($parent_id->toString(), $c)) {
                    $c[$parent_id->toString()] = [];
                }

                /*
                 * sk, 2026-06-09: We got rid of the option to set a value and a
                 * range on numeric gaps. We now need to make sure that we do not
                 * have feedback for both. If there is feedback for both only the
                 * first one is kept. I do not see a better solution.
                 */
                if ($conditions[0] === Range::InRange->value
                    && array_filter(
                        $c[$parent_id->toString()],
                        fn(array $v): bool => $v['conditions'][0] === Range::InRange->value
                    ) !== []) {
                    return $c;
                }

                $c[$parent_id->toString()][] = [
                    'conditions' => $conditions,
                    'text' => $this->generateTextValueForFeedback(
                        $migration_insert->getDb(),
                        $migration_insert->wasIliasPageEditorUsedForAdditionalTexts(),
                        $v['feedback_id'],
                        $v['feedback']
                    )
                ];

                return $c;
            },
            []
        );

        $inserts = [];
        foreach ($conditions as $parent_id => $definitions) {
            foreach ($definitions as $definition) {
                foreach ($definition['conditions'] as $condition) {
                    $inserts[] = [
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $migration_insert->getUuid()->toString()
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $migration_insert->getAnswerFormId()->toString()
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $parent_id
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $condition
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            ''
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $this->sanitizeLegacyText(
                                $migration_insert->getDb(),
                                $definition['text'],
                                $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                            )
                        ),
                    ];
                }
            }
        }

        return $inserts;
    }

    private function generateTextValueForFeedback(
        \ilDBInterface $db,
        bool $page_editor_used,
        int $feedback_id,
        string $feedback_text
    ): string {
        if ($page_editor_used) {
            return "####{$feedback_id}####";
        }

        return $this->sanitizeLegacyText(
            $db,
            $feedback_text,
            $page_editor_used
        );
    }
}
