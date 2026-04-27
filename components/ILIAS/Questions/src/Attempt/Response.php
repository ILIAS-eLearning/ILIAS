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

namespace ILIAS\Questions\Attempt;

use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class Response
{
    /**
     * @var array<string, AnswerFormResponse> $answer_form_responses
     */
    private readonly array $answer_form_responses;

    /**
     * @param array<AnswerFormResponse> $answer_form_responses
     */
    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $question_id,
        private readonly Uuid $attempt_id,
        private readonly \DateTimeImmutable $create_date,
        private ?float $reached_points,
        array $answer_form_responses = []
    ) {
        $this->answer_form_responses = array_reduce(
            $answer_form_responses,
            function (array $c, AnswerFormResponse $v): array {
                $c[$v->getAnswerFormId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    public function getReachedPoints(): float
    {
        return $this->reached_points;
    }

    public function getCreateDate(): \DateTimeImmutable
    {
        return $this->create_date;
    }

    public function withAnswerFormResponse(
        AnswerFormResponse $response
    ): self {
        $clone = clone $this;
        $clone->answer_form_responses[$response->getAnswerFormId()->toString()] = $response;
        return $clone;
    }

    public function toStorage(
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder,
        Manipulate $manipulate
    ): Manipulate {
        return array_reduce(
            $this->answer_form_responses,
            fn(Manipulate $c, AnswerFormResponse $v): Manipulate
                => $v->toStorage(
                    $persistence_factory,
                    $c
                ),
            $manipulate->withAdditionalStatement(
                $persistence_factory->insert(
                    $table_definitions->getColumns(
                        $table_names_builder,
                        TableTypes::Responses
                    ),
                    $this->buildValuesArrayForStorage($persistence_factory)
                )
            )
        );
    }

    public function toDelete(
        PersistenceFactory $persistence_factory,
        TableDefinitions $table_definitions,
        TableNameBuilder $table_names_builder,
        Manipulate $manipulate
    ): Manipulate {
        return array_reduce(
            $this->answer_form_responses,
            fn(Manipulate $c, AnswerFormResponse $v): Manipulate
                => $v->toDelete(
                    $persistence_factory,
                    $c
                ),
            $manipulate->withAdditionalStatement(
                $persistence_factory->delete(
                    $persistence_factory->table(
                        $table_names_builder,
                        TableTypes::Responses
                    ),
                    [
                        $persistence_factory->where(
                            $table_definitions->getIdColumn(
                                $table_names_builder,
                                TableTypes::Responses
                            ),
                            $persistence_factory->value(
                                FieldDefinition::T_TEXT,
                                $this->id->toString()
                            )
                        )
                    ]
                )
            )
        );
    }

    private function buildValuesArrayForStorage(
        PersistenceFactory $persistence_factory
    ): array {
        return [
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->attempt_id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_TEXT,
                $this->question_id->toString()
            ),
            $persistence_factory->value(
                FieldDefinition::T_FLOAT,
                $this->reached_points
            ),
            $persistence_factory->value(
                FieldDefinition::T_INTEGER,
                $this->create_date->getTimestamp()
            )
        ];
    }
}
