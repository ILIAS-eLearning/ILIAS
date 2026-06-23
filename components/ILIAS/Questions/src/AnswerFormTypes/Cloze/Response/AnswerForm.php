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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Response;

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormSpecificTableTypes;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\TableDefinitions;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;

class AnswerForm implements Response
{
    /**
     * @var array<string, AnswerInput> $answer_input_responses
     */
    private readonly array $answer_input_responses;

    /**
     * @param array<AnswerInput> $answer_input_responses
     */
    public function __construct(
        private readonly TableDefinitions $table_definitions,
        private readonly Uuid $response_id,
        private readonly Uuid $answer_form_id,
        array $answer_input_responses
    ) {
        $this->answer_input_responses = array_reduce(
            $answer_input_responses,
            function (array $c, AnswerInput $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v;
                return $c;
            },
            []
        );
    }

    #[\Override]
    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    #[\Override]
    public function isBest(): bool
    {
        foreach ($this->answer_input_responses as $response) {
            if (!$response->isBest()) {
                return false;
            }
        }


        return true;
    }

    #[\Override]
    public function toPreviewStorage(): array
    {
        return array_map(
            fn(AnswerInput $v): array => $v->toPreviewStorage(),
            $this->answer_input_responses
        );
    }

    #[\Override]
    public function toStorage(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->withAdditionalStatement(
            array_reduce(
                $this->answer_input_responses,
                fn(?Insert $c, AnswerInput $v): Insert => $v->toStorage(
                    $this->table_definitions,
                    $manipulate->getTableNameBuilder(
                        $this->table_definitions->getTableSubNameSpace()
                    ),
                    $persistence_factory,
                    $c,
                    $this->response_id
                )
            )
        );
    }

    #[\Override]
    public function toDelete(
        PersistenceFactory $persistence_factory,
        Manipulate $manipulate
    ): Manipulate {
        return $manipulate->withAdditionalStatement(
            $persistence_factory->delete(
                $persistence_factory->table(
                    $manipulate->getTableNameBuilder(
                        $this->table_definitions->getTableSubNameSpace()
                    ),
                    AnswerFormSpecificTableTypes::Responses
                ),
                [
                    $persistence_factory->where(
                        $this->table_definitions->getIdColumn(
                            $manipulate->getTableNameBuilder(
                                $this->table_definitions->getTableSubNameSpace()
                            ),
                            AnswerFormSpecificTableTypes::Responses
                        ),
                        $persistence_factory->value(
                            FieldDefinition::T_TEXT,
                            $this->response_id->toString()
                        )
                    )
                ]
            )
        );
    }

    #[\Override]
    public function toClientSideRepresentation(): array
    {
        return array_reduce(
            $this->answer_input_responses,
            function (array $c, AnswerInput $v): array {
                $c[$v->getAnswerInputId()->toString()] = $v->toClientSideRepresentation();
                return $c;
            },
            []
        );
    }

    public function getResponseForInput(
        Uuid $answer_input_id
    ): Uuid|string|null {
        if (isset($this->answer_input_responses[$answer_input_id->toString()])) {
            return $this->answer_input_responses[$answer_input_id->toString()]->getResponse();
        }

        return null;
    }

    public function calculateAwardedPoints(
        Properties $answer_form_properties
    ): float {
        return array_reduce(
            $this->answer_input_responses,
            function (?float $c, AnswerInput $v) use ($answer_form_properties): ?float {
                $gap = $answer_form_properties
                    ->getGaps()
                    ->getGapById(
                        $v->getAnswerInputId()
                    );

                if ($gap === null) {
                    return 0.0;
                }

                $awarded_points = $gap->getType()->calculateAwardedPointsForResponse(
                    $gap,
                    $v->getResponse()
                );

                if ($c === null) {
                    return $awarded_points;
                }

                return $c + $awarded_points;
            }
        );
    }
}
