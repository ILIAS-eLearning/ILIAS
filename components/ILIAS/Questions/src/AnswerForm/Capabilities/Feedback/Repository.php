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

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Data\Order as DataOrder;
use ILIAS\Data\Range as DataRange;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
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
        Feedback $feedback
    ): Feedback {
        $database_values = $this
            ->buildQuery()
            ->withAdditionalWhere(
                $this->persistence_factory->where(
                    $this->feedback_table_definitions->getIdColumn(
                        $this->feedback_table_names_builder,
                        TableTypes::FeedbackGeneric
                    ),
                    $this->persistence_factory->value(
                        \ilDBConstants::T_TEXT,
                        $answer_form_id->toString()
                    )
                )
            )->loadNextRecord(
                $this->feedback_table_definitions->getIdColumn(
                    $this->feedback_table_names_builder,
                    TableTypes::FeedbackGeneric
                )
            )->current();

        if ($database_values === null) {
            return $feedback;
        }

        $feedback_with_generic_values = $database_values->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->feedback_table_names_builder,
                TableTypes::FeedbackGeneric
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): Feedback => $feedback->withGenericFeedbackFromDatabase(
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
        Feedback $feedback
    ): void {
        $feedback->toStorage(
            $this->persistence_factory,
            $this->feedback_table_definitions,
            $this->feedback_table_names_builder,
            $answer_form_id,
            new Manipulate(
                $this->db,
                ManipulationType::Replace,
                QuestionRepository::COMPONENT_NAMESPACE
            )
        )->run();
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
}
