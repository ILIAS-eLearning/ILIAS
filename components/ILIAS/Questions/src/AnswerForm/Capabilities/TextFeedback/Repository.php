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

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\Table;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;

class Repository
{
    private readonly TableNameBuilder $feedback_table_names_builder;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly TextFactory $text_factory,
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableDefinitions $feedback_table_definitions
    ) {
        $this->feedback_table_names_builder = new TableNameBuilder(
            QuestionRepository::COMPONENT_NAMESPACE,
            null
        );
    }

    public function getFor(
        Uuid $answer_form_id,
        TextFeedback $feedback
    ): TextFeedback {
        $database_values = $this
            ->buildQuery()
            ->withAdditionalWhere(
                $this->persistence_factory->where(
                    $this->feedback_table_definitions->getIdColumn(
                        $this->feedback_table_names_builder,
                        TableTypes::FeedbackGeneric
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $answer_form_id->toString()
                    )
                )
            )->withGroupBy(
                $this->feedback_table_definitions->getIdColumn(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackGeneric
                )
            )->getRecords()->current();

        if ($database_values === null) {
            return $feedback;
        }

        $feedback_with_generic_values = $database_values->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->feedback_table_names_builder,
                TableTypes::FeedbackGeneric
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): TextFeedback => $feedback->withGenericFeedbackFromDatabase(
                    $this->text_factory,
                    $vs
                )
            )
        );

        return $feedback_with_generic_values->withSpecificFeedbackFromDatabase(
            $this->uuid_factory,
            $this->text_factory,
            $database_values->retrieveCurrentRecord(
                $this->persistence_factory->table(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackSpecific
                ),
                $this->refinery->identity()
            )
        );
    }

    public function store(
        Uuid $answer_form_id,
        TextFeedback $feedback
    ): void {
        $feedback->toStorage(
            $this->persistence_factory,
            $this->feedback_table_definitions,
            $this->feedback_table_names_builder,
            $answer_form_id,
            new Manipulate(
                $this->db,
                QuestionRepository::COMPONENT_NAMESPACE
            )
        )->run();
    }

    public function delete(
        Uuid $answer_form_id
    ): void {
        (new Manipulate(
            $this->db,
            QuestionRepository::COMPONENT_NAMESPACE
        ))->withAdditionalStatement(
            $this->persistence_factory->delete(
                $this->persistence_factory->table(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackGeneric
                ),
                [
                    $this->persistence_factory->where(
                        $this->feedback_table_definitions->getForeignKeyColumn(
                            $this->feedback_table_names_builder,
                            TableTypes::FeedbackGeneric
                        )
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $answer_form_id->toString()
                    )
                ]
            )
        )->withAdditionalStatement(
            $this->persistence_factory->delete(
                $this->persistence_factory->table(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackSpecific
                ),
                [
                    $this->persistence_factory->where(
                        $this->feedback_table_definitions->getForeignKeyColumn(
                            $this->feedback_table_names_builder,
                            TableTypes::FeedbackSpecific
                        )
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $answer_form_id->toString()
                    )
                ]
            )
        )->run();
    }

    public function migrateFeedbackPages(): void
    {
        $this->migrateGenericFeedbackPages();
        $this->migrateSpecificFeedbackPages();
    }

    private function buildQuery(): Query
    {
        return $this->feedback_table_definitions->completeQuery(
            new Query(
                $this->db,
                $this->refinery,
                QuestionRepository::COMPONENT_NAMESPACE,
                $this->persistence_factory->table(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackGeneric
                )
            ),
            $this->feedback_table_definitions->getIdColumn(
                $this->feedback_table_names_builder,
                TableTypes::FeedbackGeneric
            )
        );
    }

    private function migrateGenericFeedbackPages(): void
    {
        $table = $this->persistence_factory->table(
            $this->feedback_table_names_builder,
            TableTypes::FeedbackGeneric
        )->getName();

        $statement = $this->db->query(
            'SELECT answer_form_id, feedback_best_response_legacy, feedback_other_response_legacy' . PHP_EOL
                . "FROM {$table} WHERE feedback_best_response_legacy LIKE '####%####'"
                . 'OR feedback_other_response_legacy LIKE "####%####"'
        );

        if ($this->db->numRows($statement) === 0) {
            return;
        }

        $prepared = $this->db->prepare(
            "UPDATE {$table}" . PHP_EOL
                . 'SET feedback_best_response_legacy = ?,' . PHP_EOL
                . 'feedback_other_response_legacy = ?' . PHP_EOL
                . 'WHERE answer_form_id = ?',
            [
                \ilDBConstants::T_TEXT,
                \ilDBConstants::T_TEXT,
                \ilDBConstants::T_TEXT
            ]
        );

        while (($row = $this->db->fetchObject($statement)) !== null) {
            $this->db->execute(
                $prepared,
                [
                    $this->migrateFeedback(
                        '\ilAssGenFeedbackPageGUI',
                        $row->feedback_best_response_legacy
                    ),
                    $this->migrateFeedback(
                        '\ilAssGenFeedbackPageGUI',
                        $row->feedback_other_response_legacy
                    ),
                    $row->answer_form_id
                ]
            );
        }
    }

    private function migrateSpecificFeedbackPages(): void
    {
        $table = $this->persistence_factory->table(
            $this->feedback_table_names_builder,
            TableTypes::FeedbackSpecific
        )->getName();

        $statement = $this->db->query(
            'SELECT id, feedback_legacy' . PHP_EOL
                . "FROM {$table} WHERE feedback_legacy LIKE '####%####'"
        );

        if ($this->db->numRows($statement) === 0) {
            return;
        }

        $prepared = $this->db->prepare(
            "UPDATE {$table}" . PHP_EOL
                . 'SET feedback_legacy = ?' . PHP_EOL
                . 'WHERE id = ?',
            [
                \ilDBConstants::T_TEXT,
                \ilDBConstants::T_TEXT
            ]
        );

        while (($row = $this->db->fetchObject($statement)) !== null) {
            $new_feedback_text = $this->migrateFeedback(
                '\ilAssSpecFeedbackPageGUI',
                $row->feedback_legacy
            );

            if ($new_feedback_text === '') {
                $this->db->manipulateF(
                    "DELETE FROM {$table} WHERE id  = %s",
                    [\ilDBConstants::T_TEXT],
                    [$row->id]
                );
                continue;
            }

            $this->db->execute(
                $prepared,
                [
                    $new_feedback_text,
                    $row->id
                ]
            );
        }
    }

    private function migrateFeedback(
        string $page_class,
        string $text
    ): string {

        $feedback_page_id = trim($text, '#');
        if (!is_numeric($feedback_page_id)) {
            return '';
        }

        $feedback_page = (new $page_class(
            $feedback_page_id
        ));

        if ($feedback_page->getPageObject()->getXMLContent() === '<PageObject></PageObject>') {
            return '';
        }

        return $feedback_page->presentation();
    }
}
