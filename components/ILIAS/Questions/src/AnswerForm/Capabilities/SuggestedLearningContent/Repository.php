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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\ResourceStorage\Services as IRSS;

class Repository
{
    private readonly TableNameBuilder $table_names_builder;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly IRSS $irss,
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableDefinitions $table_definitions
    ) {
        $this->table_names_builder = new TableNameBuilder(
            QuestionRepository::COMPONENT_NAMESPACE,
            null
        );
    }

    public function getNew(
        Uuid $answer_form_id,
        Types $type
    ): Content {
        return new Content(
            $this->irss,
            $answer_form_id,
            $type,
            ''
        );
    }

    public function getFor(
        Uuid $answer_form_id,
    ): Content {
        $database_values = $this->buildQuery(
            $answer_form_id
        )->getRecords()->current();

        if ($database_values === null) {
            return $this->getNew(
                $answer_form_id,
                Types::None
            );
        }

        return $database_values->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->table_names_builder,
                TableTypes::SuggestedLearningContent
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): Content => new Content(
                    $this->irss,
                    $answer_form_id,
                    Types::tryFrom($vs[0]['type']),
                    $vs[0]['content']
                )
            )
        );
    }

    public function store(
        Content $content
    ): void {
        (new Manipulate(
            $this->db,
            QuestionRepository::COMPONENT_NAMESPACE
        ))->withAdditionalStatement(
            $this->persistence_factory->replace(
                $this->table_definitions->getColumns(
                    $this->table_names_builder,
                    TableTypes::SuggestedLearningContent
                ),
                $content->toStorage(
                    $this->persistence_factory
                )
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
                    $this->table_names_builder,
                    TableTypes::SuggestedLearningContent
                ),
                [
                    $this->persistence_factory->where(
                        $this->table_definitions->getIdColumn(
                            $this->table_names_builder,
                            TableTypes::SuggestedLearningContent
                        ),
                        $this->persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $answer_form_id->toString()
                        )
                    )
                ]
            )
        )->run();
    }

    private function buildQuery(
        Uuid $answer_form_id
    ): Query {
        return $this->table_definitions->completeQuery(
            new Query(
                $this->db,
                $this->refinery,
                QuestionRepository::COMPONENT_NAMESPACE,
                $this->persistence_factory->table(
                    $this->table_names_builder,
                    TableTypes::SuggestedLearningContent
                )
            ),
            $this->table_definitions->getIdColumn(
                $this->table_names_builder,
                TableTypes::SuggestedLearningContent
            )
        )->withAdditionalWhere(
            $this->persistence_factory->where(
                $this->table_definitions->getIdColumn(
                    $this->table_names_builder,
                    TableTypes::SuggestedLearningContent
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $answer_form_id->toString()
                )
            )
        );
    }
}
