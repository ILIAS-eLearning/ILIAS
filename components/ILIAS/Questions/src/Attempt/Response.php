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
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class Response
{
    public const string KEY_POINTS = 'points';
    public const string KEY_RESPONSES = 'responses';

    /**
     * @var array<string, AnswerFormResponse> $answer_form_responses
     */
    private array $answer_form_responses;

    /**
     * @param array<AnswerFormResponse> $answer_form_responses
     */
    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $question_id,
        private readonly Uuid $attempt_id,
        private readonly \DateTimeImmutable $create_date,
        private ?float $awarded_points = null,
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

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getQuestionId(): Uuid
    {
        return $this->question_id;
    }

    public function getAwardedPoints(): ?float
    {
        return $this->awarded_points;
    }

    public function withAwardedPoints(
        float $awarded_points
    ): self {
        $clone = clone $this;
        $clone->awarded_points = $awarded_points;
        return $clone;
    }

    public function getCreateDate(): \DateTimeImmutable
    {
        return $this->create_date;
    }

    public function getAnswerFormResponse(
        Uuid $answer_form_id
    ): ?AnswerFormResponse {
        return $this->answer_form_responses[$answer_form_id->toString()] ?? null;
    }

    public function withAnswerFormResponse(
        AnswerFormResponse $response
    ): self {
        $clone = clone $this;
        $clone->answer_form_responses[$response->getAnswerFormId()->toString()] = $response;
        return $clone;
    }

    public function toPreviewStorage(): array
    {
        return [
            self::KEY_POINTS => $this->awarded_points,
            self::KEY_RESPONSES => array_map(
                fn(AnswerFormResponse $v): array => $v->toPreviewStorage(),
                $this->answer_form_responses
            )
        ];
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
                $this->awarded_points
            ),
            $persistence_factory->value(
                FieldDefinition::T_INTEGER,
                $this->create_date->getTimestamp()
            )
        ];
    }
}
